<?php
/**
 * DM_Document_URL File Doc Comment
 *
 * @package Document Manager
 */

/**
 * DM_Document_URL class.
 */
class DM_Document_URL {

    /**
     * Construct class.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Init function.
     *
     * @access public
     * @return void
     */
    public function init() {
        add_action( 'single_template', array( $this, 'single_document_template_redirect' ) );
    }

    /**
     * Redirects template if a document.
     *
     * @access public
     * @return template
     */
    public function single_document_template_redirect() {
        global $post;

        if ( 'document' !== $post->post_type ) {
            return $single_template;
        }

        $template      = '';
        $template_name = 'document';

        if ( file_exists( get_stylesheet_directory() . '/document-manager/' . $template_name . '.php' ) ) :
            $template = get_stylesheet_directory() . '/document-manager/' . $template_name . '.php';
        elseif ( file_exists( get_template_directory() . '/document-manager/' . $template_name . '.php' ) ) :
            $template = get_template_directory() . '/document-manager/' . $template_name . '.php';
        else :
            $template = DM_PATH . 'templates/' . $template_name . '.php';
        endif;

        if ( ! empty( $template ) ) {
            return $template;
        }

        return $single_template;
    }

}
