<?php

class Document_Manager_Document_Upload {

    public function __construct() {
        add_action( 'wp_ajax_dm_metabox_upload_file', array( $this, 'ajax_upload_file' ) );

        add_filter( 'wp_handle_upload_prefilter', array( $this, 'modify_uploaded_file_names' ), 1, 1 );
    }

    public function ajax_upload_file() {
        $fileErrors = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_files in server settings',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE from html form',
            3 => 'The uploaded file uploaded only partially',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stoped file to upload',
        );
        $data       = array_merge( $_POST, $_FILES );
        $response   = array();

        if ( $this->user_can_save( 'dm-upload-file', $data['security'] ) ) :
            add_filter( 'upload_dir', array( $this, 'change_upload_dir' ) );

            // $attachment_id=media_handle_upload('file', $data['post_id']);
            $attachment_id = $this->handle_upload( 'file', $data['post_id'] );

            remove_filter( 'upload_dir', array( $this, 'change_upload_dir' ) );

            if ( is_wp_error( $attachment_id ) ) :
                $response['response'] = 'ERROR';
                $response['error']    = $fileErrors[ $data['file']['error'] ];
            else :
                $fullsize_path        = get_attached_file( $attachment_id );
                $pathinfo             = pathinfo( $fullsize_path );
                $url                  = wp_get_attachment_url( $attachment_id );
                $response['response'] = 'SUCCESS';
                $response['filename'] = $pathinfo['filename'];
                $response['url']      = $url;
                $response['type']     = $pathinfo['extension'];
                $this->add_file_meta( $attachment_id, $data['post_id'] );
            endif;
        else :
            // todo
        endif;

        echo json_encode( $response );

        wp_die();
    }

    protected function user_can_save( $nonce_name, $nonce ) {
        $is_valid_nonce = ( isset( $nonce ) && wp_verify_nonce( $nonce, $nonce_name ) );

        return $is_valid_nonce;
    }

    private function handle_upload( $file_id, $post_id ) {
        $time = current_time( 'mysql' );

        if ( $post = get_post( $post_id ) ) {
            // The post date doesn't usually matter for pages, so don't backdate this upload.
            if ( 'page' !== $post->post_type && substr( $post->post_date, 0, 4 ) > 0 ) {
                $time = $post->post_date;
            }
        }

        $file = wp_handle_upload( $_FILES[ $file_id ], array( 'test_form' => false ), $time );

        if ( isset( $file['error'] ) ) {
            return new WP_Error( 'upload_error', $file['error'] );
        }

        $name = $_FILES[ $file_id ]['name'];
        $ext  = pathinfo( $name, PATHINFO_EXTENSION );
        $name = wp_basename( $name, ".$ext" );

        $url     = $file['url'];
        $type    = $file['type'];
        $file    = $file['file'];
        $title   = sanitize_text_field( $name );
        $content = '';
        $excerpt = '';

        if ( preg_match( '#^audio#', $type ) ) {
            $meta = wp_read_audio_metadata( $file );

            if ( ! empty( $meta['title'] ) ) {
                $title = $meta['title'];
            }

            if ( ! empty( $title ) ) {

                if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio track title, 2: album title, 3: artist name */
                    $content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta['album'], $meta['artist'] );
                } elseif ( ! empty( $meta['album'] ) ) {
                    /* translators: 1: audio track title, 2: album title */
                    $content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta['album'] );
                } elseif ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio track title, 2: artist name */
                    $content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta['artist'] );
                } else {
                    /* translators: 1: audio track title */
                    $content .= sprintf( __( '"%s".' ), $title );
                }
            } elseif ( ! empty( $meta['album'] ) ) {

                if ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio album title, 2: artist name */
                    $content .= sprintf( __( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
                } else {
                    $content .= $meta['album'] . '.';
                }
            } elseif ( ! empty( $meta['artist'] ) ) {

                $content .= $meta['artist'] . '.';

            }

            if ( ! empty( $meta['year'] ) ) {
                /* translators: Audio file track information. 1: Year of audio track release */
                $content .= ' ' . sprintf( __( 'Released: %d.' ), $meta['year'] );
            }

            if ( ! empty( $meta['track_number'] ) ) {
                $track_number = explode( '/', $meta['track_number'] );
                if ( isset( $track_number[1] ) ) {
                    /* translators: Audio file track information. 1: Audio track number, 2: Total audio tracks */
                    $content .= ' ' . sprintf( __( 'Track %1$s of %2$s.' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
                } else {
                    /* translators: Audio file track information. 1: Audio track number */
                    $content .= ' ' . sprintf( __( 'Track %1$s.' ), number_format_i18n( $track_number[0] ) );
                }
            }

            if ( ! empty( $meta['genre'] ) ) {
                /* translators: Audio file genre information. 1: Audio genre name */
                $content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta['genre'] );
            }

            // Use image exif/iptc data for title and caption defaults if possible.
        } elseif ( 0 === strpos( $type, 'image/' ) && $image_meta = @wp_read_image_metadata( $file ) ) {
            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
                $title = $image_meta['title'];
            }

            if ( trim( $image_meta['caption'] ) ) {
                $excerpt = $image_meta['caption'];
            }
        }

        $attachment = array(
            'post_mime_type' => $type,
            'guid'           => $url,
            'post_parent'    => $post_id,
            'post_title'     => $title,
            'post_content'   => $content,
            'post_excerpt'   => $excerpt,
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset( $attachment['ID'] );

        // Save the data
        $id = wp_insert_attachment( $attachment, $file, $post_id, true );

        if ( ! is_wp_error( $id ) ) :
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
        endif;

        return $id;
    }

    public function modify_uploaded_file_names( $file ) {
        // Get the parent post ID, if there is one
        if ( isset( $_GET['post_id'] ) ) {
            $post_id = $_GET['post_id'];
        } elseif ( isset( $_POST['post_id'] ) ) {
            $post_id = $_POST['post_id'];
        }

        if ( get_post_type( $post_id ) != 'document' ) {
            return $file;
        }

        $current_version = dm_get_file_version( $post_id );
        $new_version     = (int) $current_version + 1;

        $filename     = pathinfo( $file['name'], PATHINFO_FILENAME );
        $filename_ext = pathinfo( $file['name'], PATHINFO_EXTENSION );

        $file['name'] = md5( $name ) . "-version-$new_version.$filename_ext";

        // update version //
        update_post_meta( $post_id, '_dm_document_version', $new_version );

        return $file;
    }

    protected function change_upload_dir( $dirs ) {
        $dirs['subdir'] = '';
        $dirs['path']   = DocumentManager()->settings['uploads']['basedir'];
        $dirs['url']    = DocumentManager()->settings['uploads']['baseurl'];

        return $dirs;
    }

    protected function add_file_meta( $file_id = 0, $post_id = 0 ) {
        add_post_meta( $file_id, '_dm_document_timestamp', current_time( 'mysql' ) ); // add timestamp
        add_post_meta( $file_id, '_dm_document_version_number', dm_get_file_version( $post_id ) ); // add version
    }

}

new Document_Manager_Document_Upload();
