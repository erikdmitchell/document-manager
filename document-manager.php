<?php
/**
 * Plugin Name: Document Manager
 * Plugin URI: 
 * Description: The ultimate document manager.
 * Version: 1.0.0-alpha
 * Author: 
 * Author URI: 
 * Requires at least: 4.0
 * Tested up to: 4.8.3
 * Text Domain: document-manager
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!defined('DM_PLUGIN_FILE')) {
	define('DM_PLUGIN_FILE', __FILE__);
}

final class DocumentManager {

	public $version='1.0.0-alpha';
	
	public $admin='';
	
	public $settings='';

	protected static $_instance=null;

	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance=new self();
		}
		
		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	private function define_constants() {
		$this->define('DM_VERSION', $this->version);
		$this->define('DM_PATH', plugin_dir_path(__FILE__));
		$this->define('DM_URL', plugin_dir_url(__FILE__));
		
	}

	private function define($name, $value) {
		if (!defined($name)) {
			define($name, $value);
		}
	}

	public function includes() {
		include_once(DM_PATH.'install.php');
		include_once(DM_PATH.'post-type.php');
		include_once(DM_PATH.'taxonomies.php');		
		include_once(DM_PATH.'functions.php');
		include_once(DM_PATH.'document-url.php');
		include_once(DM_PATH.'admin/admin.php');
		
		new Document_Manager_Document_URL();
		
		if (is_admin()) :
			$this->admin=new Document_Manager_Admin();
		endif;
	}

	private function init_hooks() {
		register_activation_hook(DM_PLUGIN_FILE, array('Document_Manager_Install', 'install'));
		
		add_action('admin_init', array($this, 'plugin_updater'));
		add_action('init', array($this, 'get_settings'), 99);
		add_action('init', array($this, 'init'), 0);
		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
	}

	public function init() {
		
	}
	
	public function get_settings() {		
		$this->settings=get_option('dm_settings', '');		
	}
	
	public function scripts_styles() {
		wp_enqueue_style('font-awesome', DM_URL.'css/font-awesome.min.css', '', '4.7.0');
	}
	
	public function plugin_updater() {
		if (!is_admin())
			return false;
	
		if (!defined('WP_GITHUB_FORCE_UPDATE'))
			define('WP_GITHUB_FORCE_UPDATE', true);
			
		$username='erikdmitchell';
		$repo_name='pickle-document-manager';
		$folder_name='pickle-document-manager';
	    
	    $config = array(
	        'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
	        'proper_folder_name' => $folder_name, // this is the name of the folder your plugin lives in
	        'api_url' => 'https://api.github.com/repos/'.$username.'/'.$repo_name, // the github API url of your github repo
	        'raw_url' => 'https://raw.github.com/'.$username.'/'.$repo_name.'/master', // the github raw url of your github repo
	        'github_url' => 'https://github.com/'.$username.'/'.$repo_name, // the github url of your github repo
	        'zip_url' => 'https://github.com/'.$username.'/'.$repo_name.'/zipball/master', // the zip url of the github repo
	        'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
	        'requires' => '4.0', // which version of WordPress does your plugin require?
	        'tested' => '4.9', // which version of WordPress is your plugin tested up to?
	        'readme' => 'readme.txt', // which file to use as the readme for the version number
	    );
	   
		new WP_GitHub_Updater($config);
	}	

}

function DocumentManager() {
	return DocumentManager::instance();
}

// Global for backwards compatibility.
$GLOBALS['documentmanager']=DocumentManager();
?>