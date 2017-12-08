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
        $html  = '';
        $stats = dm_get_document_stats( $post->ID );

        $html .= '<div class="dm-document-stats-metabox">';

        if ( empty( $stats ) ) :

            $html .= 'No downloads yet.';

            else :
                $html     .= '<div class="dm-meta-box-row header">';
                    $html .= '<div class="doc-name">Name</div>';
                    $html .= '<div class="user-name">Username</div>';
                    $html .= '<div class="user-ip">IP Address</div>';
                $html     .= '</div>';

                foreach ( $stats as $row ) :
                    $html     .= '<div class="dm-meta-box-row">';
                        $html .= '<div class="doc-name">' . get_the_title( $row->doc_id ) . '</div>';
                        $html .= '<div class="user-name">' . $row->username . '</div>';
                        $html .= '<div class="user-ip">' . $row->user_ip . '</div>';
                    $html     .= '</div>';
                endforeach;

            endif;

            $html .= '</div>';

            echo $html;
    }

}

new DM_Document_Stats_Metabox();

