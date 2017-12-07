<?php

/**
 * DM_Document_Details_Metabox class.
 */
class DM_Document_Details_Metabox {

    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        if ( is_admin() ) :
            add_action( 'load-post.php', array( $this, 'init_metabox' ) );
            add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
        endif;
    }

    /**
     * Initalize metabox function.
     *
     * @access public
     * @return void
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
    }

    /**
     * Add metabox function.
     *
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'dm-document-details',
            __( 'Document Details', 'document-manager' ),
            array( $this, 'render_metabox' ),
            'document',
            'top',
            'default'
        );

    }

    /**
     * Render metabox function.
     *
     * @access public
     * @param mixed $post post object.
     * @return void
     */
    public function render_metabox( $post ) {
        $html = '';

        $html .= wp_nonce_field( 'update_document_details', 'dm_meta_box', true, false );

        $html     .= '<div class="dm-meta-box-row">';
            $html .= '<label for="dm-metabox-file">Select File to Upload:</label>';
            $html .= '<input type="file" id="dm-metabox-file" name="dmmetabox[file]" value="" />';
            $html .= '<a href="" id="dm-metabox-file-upload" class="button button-secondary">Upload</a>';
        $html     .= '</div>';

        $html     .= '<div class="dm-meta-box-row">';
            $html .= '<label for="dm-metabox-description">Description</label>';
            $html .= '<textarea name="dmmetabox[description]" id="dm-metabox-description" class="" placeholder="Document description">' . get_post_meta( $post->ID, '_dm_document_description', true ) . '</textarea>';
        $html     .= '</div>';

        $html .= '<input type="hidden" name="dmmetabox[post_id]" id="dm-metabox-post-id" value="' . $post->ID . '" />';

        echo esc_html( $html );
    }

    /**
     * Save metabox function.
     *
     * @access public
     * @param mixed $post_id post id.
     * @param mixed $post post object.
     * @return void
     */
    public function save_metabox( $post_id, $post ) {
        // Check if nonce is set.
        if ( ! isset( $_POST['dm_meta_box'] ) || ! wp_verify_nonce( wp_unslash( $_POST['dm_meta_box'] ), 'update_document_details' ) ) {
            return;
        }

        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        update_post_meta( $post_id, '_dm_document_description', wp_unslash( $_POST['dmmetabox']['description'] ) );
    }
}

new Document_Manager_Document_Details_Meta_Box();

