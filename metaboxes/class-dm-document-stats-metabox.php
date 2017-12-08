<?php
/**
 * Document Stats Metabox
 *
 * @package Document Manager
 */

/**
 * DM_Document_Stats_Metabox class.
 */
class DM_Document_Stats_Metabox {

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
    }

    /**
     * Add metabox function.
     *
     * @access public
     * @return void
     */
    public function add_metabox() {
        add_meta_box(
            'dm-document-stats',
            __( 'Document Statistics', 'document-manager' ),
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

        $html     .= '<div class="dm-meta-box-row">';

        $html     .= '</div>';

        $html     .= '<div class="dm-meta-box-row">';

        $html     .= '</div>';

        echo $html;
    }

}

new DM_Document_Stats_Metabox();

