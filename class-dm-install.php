<?php
/**
 * Install
 *
 * @package Document Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DM_Install class.
 */
class DM_Install {

    /**
     * Updates var
     *
     * (default value: array())
     *
     * @var array
     * @access private
     * @static
     */
    private static $updates = array();

    /**
     * Init function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
    }

    /**
     * Check version function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function check_version() {
        if ( get_option( 'dm_version' ) !== DocumentManager()->version ) {
            self::install();
        }
    }

    /**
     * Install function.
     *
     * @access public
     * @static
     * @return void
     */
    public static function install() {
        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running this routine.
        if ( 'yes' === get_transient( 'dm_installing' ) ) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient( 'dm_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        self::settings();
        self::update_version();
        self::update();

        delete_transient( 'dm_installing' );
    }

    /**
     * Settings function.
     *
     * @access private
     * @static
     * @return void
     */
    private static function settings() {
        $default_settings                          = array(
            'uploads' => wp_upload_dir(),
        );
        $default_settings['uploads']['basefolder'] = str_replace( get_option( 'siteurl' ), '', $default_settings['uploads']['baseurl'] );
        $stored_settings                           = get_option( 'dm_settings', array() ); // in case the plugi nwas previously installed.
        $settings                                  = dm_parse_args( $store_settings, $default_settings );

        update_option( 'dm_settings', $settings );
    }

    /**
     * Update function.
     *
     * @access private
     * @static
     * @return void
     */
    private static function update() {
        $current_version = get_option( 'dm_version' );

        foreach ( self::get_update_callbacks() as $version => $update_callbacks ) :
            if ( version_compare( $current_version, $version, '<' ) ) :
                foreach ( $update_callbacks as $update_callback ) :
                    $update_callback();
                endforeach;
            endif;
        endforeach;
    }

    /**
     * Get update callbacks function.
     *
     * @access public
     * @static
     * @return array
     */
    public static function get_update_callbacks() {
        return self::$updates;
    }

    /**
     * Update version in db function.
     *
     * @access private
     * @static
     * @return void
     */
    private static function update_version() {
        delete_option( 'dm_version' );

        add_option( 'dm_version', DocumentManager()->version );
    }

}

Document_Manager_Install::init();

