<?php
/**
 * Document Versions Metabox
 *
 * @package Document Manager
 */

/**
 * DM_Document_Versions_Metabox class.
 */
class DM_Document_Versions_Metabox {

    /**
     * Construct
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
     * Initialize metabox.
     *
     * @access public
     * @return void
     */
    public function init_metabox() {
        add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 ); // may not need since this is auto generated.
    }

    /**
     * Add metabox.
     *
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'dm-document-versions',
            __( 'Versions', 'document-manager' ),
            array( $this, 'render_metabox' ),
            'document',
            'top',
            'default'
        );

    }

    /**
     * Render metabox.
     *
     * @access public
     * @param mixed $post post object.
     * @return void
     */
    public function render_metabox( $post ) {
        $html  = '';
        $files = get_children(
            array(
                'post_parent'      => $post->ID,
                'post_type'        => 'attachment',
                'posts_per_page'   => -1,
                'orderby'          => 'menu_order',
                'order'            => 'ASC',
                'suppress_filters' => false,
            )
        );

        $html .= '<div class="dm-meta-box-file-list">';

            $html     .= '<div id="file-header" class="dm-meta-box-file header">';
                $html .= '<div class="file-name">Name</div>';
                $html .= '<div class="file-type">Type</div>';
                $html .= '<div class="file-size">Size</div>';
                $html .= '<div class="file-version">Version</div>';
                $html .= '<div class="file-action"></div>';
            $html     .= '</div>';

        foreach ( $files as $file ) :
            $html     .= '<div id="file-' . $file->ID . '" class="dm-meta-box-file">';
                $html .= '<div class="file-name">' . $file->post_title . ' (' . dm_get_file_timestamp( $file->ID ) . ')</div>';
                $html .= '<div class="file-type">' . dm_get_file_icon( $file->post_mime_type ) . '</div>';
                $html .= '<div class="file-size">' . dm_get_file_size( $file->ID ) . '</div>';
                $html .= '<div class="file-version">' . dm_get_file_version_number( $file->ID ) . '</div>';
                $html .= '<div class="file-action"><a href="#" class="make-current">Make Current</a></div>';
            $html     .= '</div>';
        endforeach;

        $html .= '</div>';

        $html .= '<input type="hidden" name="dmmetabox[post_id]" id="dm-metabox-post-id" value="' . $post->ID . '" />';

        echo esc_html( $html );
    }

    /**
     * Save metabox data.
     *
     * @access public
     * @param mixed $post_id post id.
     * @param mixed $post post object.
     * @return void
     */
    public function save_metabox( $post_id, $post ) {
        // Check if nonce is set.
        if ( ! isset( $_POST['dm_meta_box'] ) || ! wp_verify_nonce( wp_unslash( $_POST['dm_meta_box'] ), update_document_details ) ) {
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
    }
}

new Document_Manager_Document_Versions_Meta_Box();

