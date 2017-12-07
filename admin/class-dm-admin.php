<?php
/**
 * Document Admin
 *
 * @package Document Manager
 */

/**
 * DM_Admin class.
 */
class DM_Admin {

    /**
     * Class construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts_styles' ) );
        add_action( 'init', array( $this, 'update_settings' ), 0 );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'wp_ajax_dm_reload_metabox', array( $this, 'ajax_reload_metabox' ) );
    }

    /**
     * Includes function.
     *
     * @access public
     * @return void
     */
    public function includes() {
        include_once dirname( __FILE__ ) . '/class-dm-bulk-import.php';
        include_once dirname( __FILE__ ) . '/class-dm-document-upload.php';
    }

    /**
     * Include scripts and styles.
     *
     * @access public
     * @return void
     */
    public function scripts_styles() {
        wp_register_script( 'dm-metaboxes-script', DM_URL . 'admin/js/metaboxes.js', array( 'jquery' ), DM_VERSION, true );

        wp_localize_script(
            'dm-metaboxes-script', 'dmMetaboxOptions', array(
                'nonce' => wp_create_nonce( 'dm-upload-file' ),
            )
        );

        wp_enqueue_script( 'dm-metaboxes-script' );

        wp_enqueue_style( 'font-awesome', DM_URL . 'css/font-awesome.min.css', '', '4.7.0' );
        wp_enqueue_style( 'dm-metaboxes-style', DM_URL . 'admin/css/metaboxes.css', '', DM_VERSION );
    }

    /**
     * Add to admin menu.
     *
     * @access public
     * @return void
     */
    public function admin_menu() {
        add_options_page( 'Document Manager', 'Document Manager', 'manage_options', 'document-manager', array( $this, 'admin_page' ) );
    }

    /**
     * Display admin page (wrapper).
     *
     * @access public
     * @return void
     */
    public function admin_page() {
        $html       = null;
        $tabs       = array(
            array(
                'slug'     => 'settings',
                'title'    => 'Settings',
                'pagename' => 'settings',
            ),
        );
        $tabs       = apply_filters( 'document_manager_admin_tabs', $tabs, 99 );
        $active_tab = isset( $_GET['tab'] ) ? wp_unslash( $_GET['tab'] ) : 'settings'; // Input var okay.
        $pagename   = 'settings';

        $html     .= '<div class="wrap dm-admin">';
            $html .= '<h1>Document Manager</h1>';

            $html .= '<h2 class="nav-tab-wrapper">';
        foreach ( $tabs as $tab ) :
            if ( $active_tab === $tab['slug'] ) :
                $class = 'nav-tab-active';
            else :
                $class = null;
            endif;

            $pagename = $tab['pagename'];

            $html .= '<a href="?page=document-manager&tab=' . $tab['slug'] . '" class="nav-tab ' . $class . '">' . $tab['title'] . '</a>';
                endforeach;
            $html .= '</h2>';

            $html .= $this->get_admin_page( $pagename );

        $html .= '</div>';

        echo esc_html( $html );
    }

    /**
     * AJAX reload our metabox.
     *
     * @access public
     * @return void
     */
    public function ajax_reload_metabox() {
        $metabox = wp_unslash( $_POST['metabox'] ); // Input var okay.
        $mb      = new $metabox();
        $post    = get_post( wp_unslash( $_POST['post_id'] ) ); // Input var okay.

        echo esc_html( $mb->render_metabox( $post ) );

        wp_die();
    }

    /**
     * Update admin settings.
     *
     * @access public
     * @return void
     */
    public function update_settings() {
        if ( ! isset( $_POST['dm_admin_update'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dm_admin_update'] ), 'update_settings' ) ) { // Input var okay.
            return;
        }

        $settings_data    = ! empty( $_POST['dm_settings'] ) ? wp_unslash( $_POST['dm_settings'] ) : ''; // Input var okay.
        $new_settings     = array();
        $current_settings = get_option( 'dm_settings', '' );

        // update uploads folder.
        if ( $settings_data['uploads_basefolder'] !== $current_settings['uploads']['basefolder'] ) :
            $new_settings['uploads'] = $this->update_uploads_folder( $settings_data['uploads_basefolder'] );
        endif;

        $settings = dm_parse_args( $new_settings, $current_settings );

        update_option( 'dm_settings', $settings );
    }

    /**
     * Updates uploads folder for documents.
     *
     * @access private
     * @param string $basefolder (default: '').
     * @return array
     */
    private function update_uploads_folder( $basefolder = '' ) {
        if ( empty( $basefolder ) ) {
            return;
        }

        $siteurl     = get_option( 'siteurl' );
        $upload_path = trim( get_option( 'upload_path' ) ); // NA.
        $dir         = rtrim( ABSPATH, '/' ) . $basefolder;
        $url         = $siteurl . $basefolder;
        $basedir     = $dir;
        $baseurl     = $url;
        $subdir      = '';

        if ( get_option( 'uploads_use_yearmonth_folders' ) ) : // test this.
            $time   = current_time( 'mysql' );
            $y      = substr( $time, 0, 4 );
            $m      = substr( $time, 5, 2 );
            $subdir = "/$y/$m";
        endif;

        $dir .= $subdir;
        $url .= $subdir;

        // create folder if need be.
        if ( ! is_dir( $basedir ) ) {
            wp_mkdir_p( $basedir, 0700 );
        }

        return array(
            'path'       => $dir,
            'url'        => $url,
            'subdir'     => $subdir,
            'basedir'    => $basedir,
            'baseurl'    => $baseurl,
            'error'      => false,
            'basefolder' => $basefolder,
        );
    }

    /**
     * Displays a specfic admin page.
     *
     * @access public
     * @param bool $template_name (default: false).
     * @return html
     */
    public function get_admin_page( $template_name = false ) {
        if ( ! $template_name ) {
            return false;
        }

        ob_start();

        do_action( 'dm_before_admin_' . $template_name );

        include DM_PATH . 'admin/pages/' . $template_name . '.php';

        do_action( 'dm_after_admin_' . $template_name );

        $html = ob_get_contents();

        ob_end_clean();

        return $html;
    }

}

