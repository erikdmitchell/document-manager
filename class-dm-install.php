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
     * Database updates var
     *
     * (default value: array())
     *
     * @var array
     * @access private
     * @static
     */
    private static $db_updates = array();

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
        self::create_tables();
        self::update_version();
        self::update();
        self::maybe_update_db_version();

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
        $stored_settings                           = get_option( 'dm_settings', array() ); // in case the plugin was previously installed.
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

    /**
     * Set up the database tables which the plugin needs to function.
     *
     * Tables:
     *      document_manager_downloads - Table for storing information about document downloads
     */
    private static function create_tables() {
        $current_db_version = get_option( 'dm_db_version', '' );

        if ( ! empty( $current_db_version ) ) {
            return;
        }

        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( self::get_schema() );
    }
    /**
     * Get Table schema.
     *
     * @return string
     */
    private static function get_schema() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) :
            $collate = $wpdb->get_charset_collate();
        endif;

        $tables = "
            CREATE TABLE {$wpdb->prefix}document_manager_downloads (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id BIGINT UNSIGNED NOT NULL,
                post_title TEXT NOT NULL,
                doc_id BIGINT UNSIGNED NOT NULL,
                file_url VARCHAR(255) NOT NULL, 
                user_ip BINARY(16) NOT NULL,
                date_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
                user_id BIGINT UNSIGNED NOT NULL,
                username VARCHAR(60) NOT NULL,                 
                PRIMARY KEY (id)
            ) $collate;
		";

        return $tables;
    }

    /**
     * Check if we need to update our db.
     *
     * @access private
     * @static
     * @return void
     */
    private static function maybe_update_db_version() {
        if ( self::needs_db_update() ) :
            self::update_db();
        else :
            self::update_db_version();
        endif;
    }

    /**
     * Do we need to update db.
     *
     * @access private
     * @static
     * @return boolean
     */
    private static function needs_db_update() {
        $current_db_version = get_option( 'dm_db_version', null );
        $updates            = self::get_db_update_callbacks();

        return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
    }

    /**
     * Get our db update callbacks function.
     *
     * @access public
     * @static
     * @return array
     */
    public static function get_db_update_callbacks() {
        return self::$db_updates;
    }

    /**
     * Update db.
     *
     * @access private
     * @static
     * @return void
     */
    private static function update_db() {}

    /**
     * Update db version.
     *
     * @access public
     * @static
     * @param mixed $version (default: null).
     * @return void
     */
    public static function update_db_version( $version = null ) {
        delete_option( 'dm_db_version' );

        add_option( 'dm_db_version', is_null( $version ) ? DocumentManager()->version : $version );
    }

}

DM_Install::init();
