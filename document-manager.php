<?php
/**
 * Plugin Name: Document Manager
 * Plugin URI: 
 * Description: The ultimate document manager.
 * Version: 1.0.0
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

	public $version='1.0.0';
	
	public $admin='';

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
		add_action('init', array($this, 'init'), 0);
		add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
	}

	public function init() {

	}
	
	public function scripts_styles() {
		wp_enqueue_style('font-awesome', DM_URL.'css/font-awesome.min.css', '', '4.7.0');
	}

}

function DocumentManager() {
	return DocumentManager::instance();
}

// Global for backwards compatibility.
$GLOBALS['documentmanager']=DocumentManager();
?>