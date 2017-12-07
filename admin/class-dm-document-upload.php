<?php
/**
 * Document Upload
 *
 * @package Document Manager
 */
 
/**
 * DM_Document_Upload class.
 */
class DM_Document_Upload {

    /**
     * Construct class.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_dm_metabox_upload_file', array( $this, 'ajax_upload_file' ) );

        add_filter( 'wp_handle_upload_prefilter', array( $this, 'modify_uploaded_file_names' ), 1, 1 );
    }

    /**
     * AJAX upoad a file.
     *
     * @access public
     * @return void
     */
    public function ajax_upload_file() {
        $file_errors = array(
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_files in server settings',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE from html form',
            3 => 'The uploaded file uploaded only partially',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stoped file to upload',
        );
        $data        = array_merge( $_POST, $_FILES ); // Input var okay.
        $response    = array();

        if ( $this->user_can_save( 'dm-upload-file', $data['security'] ) ) :
            add_filter( 'upload_dir', array( $this, 'change_upload_dir' ) );

            $attachment_id = $this->handle_upload( 'file', $data['post_id'] );

            remove_filter( 'upload_dir', array( $this, 'change_upload_dir' ) );

            if ( is_wp_error( $attachment_id ) ) :
                $response['response'] = 'ERROR';
                $response['error']    = $file_errors[ $data['file']['error'] ];
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
            $response['response'] = 'ERROR';
            $response['error']    = 'You cannot upload a file.';
        endif;

        echo wp_json_encode( $response );

        wp_die();
    }

    /**
     * Checks if user can save file.
     *
     * @access protected
     * @param mixed $nonce_name string.
     * @param mixed $nonce string.
     * @return boolean
     */
    protected function user_can_save( $nonce_name, $nonce ) {
        $is_valid_nonce = ( isset( $nonce ) && wp_verify_nonce( $nonce, $nonce_name ) );

        return $is_valid_nonce;
    }

    /**
     * Handles the upload of the file to the media libaray.
     *
     * @access private
     * @param mixed $file_id string.
     * @param mixed $post_id post id.
     * @return integer
     */
    private function handle_upload( $file_id, $post_id ) {
        $time = current_time( 'mysql' );
        $post = get_post( $post_id );

        if ( $post ) {
            // The post date doesn't usually matter for pages, so don't backdate this upload.
            if ( 'page' !== $post->post_type && substr( $post->post_date, 0, 4 ) > 0 ) {
                $time = $post->post_date;
            }
        }

        $files = wp_unslash( $_FILES[ $file_id ] ); // Input var okay.

        $file = wp_handle_upload( $files, array( 'test_form' => false ), $time );

        if ( isset( $file['error'] ) ) {
            return new WP_Error( 'upload_error', $file['error'] );
        }

        $name = $files['name'];
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
                    $content .= sprintf( __( '"%1$s" from %2$s by %3$s.', 'document-manager' ), $title, $meta['album'], $meta['artist'] );
                } elseif ( ! empty( $meta['album'] ) ) {
                    /* translators: 1: audio track title, 2: album title */
                    $content .= sprintf( __( '"%1$s" from %2$s.', 'document-manager' ), $title, $meta['album'] );
                } elseif ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio track title, 2: artist name */
                    $content .= sprintf( __( '"%1$s" by %2$s.', 'document-manager' ), $title, $meta['artist'] );
                } else {
                    /* translators: 1: audio track title */
                    $content .= sprintf( __( '"%s".', 'document-manager' ), $title );
                }
            } elseif ( ! empty( $meta['album'] ) ) {

                if ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio album title, 2: artist name */
                    $content .= sprintf( __( '%1$s by %2$s.', 'document-manager' ), $meta['album'], $meta['artist'] );
                } else {
                    $content .= $meta['album'] . '.';
                }
            } elseif ( ! empty( $meta['artist'] ) ) {

                $content .= $meta['artist'] . '.';

            }

            if ( ! empty( $meta['year'] ) ) {
                /* translators: Audio file track information. 1: Year of audio track release */
                $content .= ' ' . sprintf( __( 'Released: %d.', 'document-manager' ), $meta['year'] );
            }

            if ( ! empty( $meta['track_number'] ) ) {
                $track_number = explode( '/', $meta['track_number'] );
                if ( isset( $track_number[1] ) ) {
                    /* translators: Audio file track information. 1: Audio track number, 2: Total audio tracks */
                    $content .= ' ' . sprintf( __( 'Track %1$s of %2$s.', 'document-manager' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
                } else {
                    /* translators: Audio file track information. 1: Audio track number */
                    $content .= ' ' . sprintf( __( 'Track %1$s.', 'document-manager' ), number_format_i18n( $track_number[0] ) );
                }
            }

            if ( ! empty( $meta['genre'] ) ) {
                /* translators: Audio file genre information. 1: Audio genre name */
                $content .= ' ' . sprintf( __( 'Genre: %s.', 'document-manager' ), $meta['genre'] );
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

        // Save the data.
        $id = wp_insert_attachment( $attachment, $file, $post_id, true );

        if ( ! is_wp_error( $id ) ) :
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
        endif;

        return $id;
    }

    /**
     * Changes the file name based on version.
     *
     * @access public
     * @param mixed $file array.
     * @return url
     */
    public function modify_uploaded_file_names( $file ) {
        // Get the parent post ID, if there is one.
        if ( isset( $_GET['post_id'] ) ) {
            $post_id = intval( $_GET['post_id'] ); // Input var okay.
        } elseif ( isset( $_POST['post_id'] ) ) {
            $post_id = intval( $_POST['post_id'] ); // Input var okay.
        }

        if ( 'document' !== get_post_type( $post_id ) ) {
            return $file;
        }

        $current_version = dm_get_file_version( $post_id );
        $new_version     = (int) $current_version + 1;

        $filename     = pathinfo( $file['name'], PATHINFO_FILENAME );
        $filename_ext = pathinfo( $file['name'], PATHINFO_EXTENSION );

        $file['name'] = md5( $name ) . "-version-$new_version.$filename_ext";

        // update version.
        update_post_meta( $post_id, '_dm_document_version', $new_version );

        return $file;
    }

    /**
     * Changes upload dir to custom one.
     *
     * @access protected
     * @param mixed $dirs array.
     * @return array
     */
    protected function change_upload_dir( $dirs ) {
        $dirs['subdir'] = '';
        $dirs['path']   = DocumentManager()->settings['uploads']['basedir'];
        $dirs['url']    = DocumentManager()->settings['uploads']['baseurl'];

        return $dirs;
    }

    /**
     * Adds meta details to our file.
     *
     * @access protected
     * @param int $file_id (default: 0).
     * @param int $post_id (default: 0).
     * @return void
     */
    protected function add_file_meta( $file_id = 0, $post_id = 0 ) {
        add_post_meta( $file_id, '_dm_document_timestamp', current_time( 'mysql' ) ); // add timestamp.
        add_post_meta( $file_id, '_dm_document_version_number', dm_get_file_version( $post_id ) ); // add version.
    }

}

new Document_Manager_Document_Upload();
