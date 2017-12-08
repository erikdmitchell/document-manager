<?php
/**
 * DM_Document_Download File
 *
 * @package Document Manager
 */

/**
 * DM_Document_Download class.
 */
class DM_Document_Download {

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
        add_action( 'init', array( $this, 'handle_download_via_post' ) );
    }

    public function handle_download_via_post() {
        if (!isset($_REQUEST['dm_document_download']) || !wp_verify_nonce($_REQUEST['dm_document_download'], 'process_download'))
            return;
            
        print_r($_REQUEST);

/*
	global $wpdb;
	$download_id = absint($_REQUEST['download_id']);
	$download_title = get_the_title($download_id);
	$download_link = get_post_meta($download_id, 'sdm_upload', true);

	//Do some validation checks
	if (!$download_id) {
	    wp_die(__('Error! Incorrect download item id.', 'simple-download-monitor'));
	}
	if (empty($download_link)) {
	    wp_die(__('Error! This download item (' . $download_id . ') does not have any download link. Edit this item and specify a downloadable file URL for it.', 'simple-download-monitor'));
	}

	//Check download password (if applicable for this download)
	$post_object = get_post($download_id); // Get post object
	$post_pass = $post_object->post_password; // Get post password
	if (!empty($post_pass)) {//This download item has a password. So validate the password.
	    $pass_val = $_REQUEST['pass_text'];
	    if (empty($pass_val)) {//No password was submitted with the downoad request.
		$dl_post_url = get_permalink($download_id);
		$error_msg = __('Error! This download requires a password.', 'simple-download-monitor');
		$error_msg .= '<p>';
		$error_msg .= '<a href="' . $dl_post_url . '">' . __('Click here', 'simple-download-monitor') . '</a>';
		$error_msg .= __(' and enter a valid password for this item', 'simple-download-monitor');
		$error_msg .= '</p>';
		wp_die($error_msg);
	    }
	    if ($post_pass != $pass_val) {
		//Incorrect password submitted.
		wp_die(__('Error! Incorrect password. This download requires a valid password.', 'simple-download-monitor'));
	    } else {
		//Password is valid. Go ahead with the download
	    }
	}
	//End of password check

	$ipaddress = sdm_get_ip_address();
	$date_time = current_time('mysql');
	$visitor_country = $ipaddress ? sdm_ip_info($ipaddress, 'country') : '';

	$main_option = get_option('sdm_downloads_options');

	$visitor_name = sdm_get_logged_in_user();

	// Check if we only allow the download for logged-in users
	if (isset($main_option['only_logged_in_can_download'])) {
	    if ($main_option['only_logged_in_can_download'] && $visitor_name === false) {
		//User not logged in, let's display the error message.
		//But first let's see if we have login page URL set so we can display it as well
		$loginMsg = '';
		if (isset($main_option['general_login_page_url']) && !empty($main_option['general_login_page_url'])) {
		    //We have a login page URL set. Lets use it.
		    $tpl = __("__Click here__ to go to login page.", 'simple-download-monitor');
		    $loginMsg = preg_replace('/__(.*)__/', ' <a href="' . $main_option['general_login_page_url'] . '">$1</a> $2', $tpl);
		}
		wp_die(__('You need to be logged in to download this file.', 'simple-download-monitor') . $loginMsg);
	    }
	}

	$visitor_name = ($visitor_name === false) ? __('Not Logged In', 'simple-download-monitor') : $visitor_name;

	// Get option for global disabling of download logging
	$no_logs = isset($main_option['admin_no_logs']);

	// Get optoin for logging only unique IPs
	$unique_ips = isset($main_option['admin_log_unique']);

	// Get post meta for individual disabling of download logging
	$get_meta = get_post_meta($download_id, 'sdm_item_no_log', true);
	$item_logging_checked = isset($get_meta) && $get_meta === 'on' ? 'on' : 'off';

	$dl_logging_needed = true;

	// Check if download logs have been disabled (globally or per download item)
	if ($no_logs === true || $item_logging_checked === 'on') {
	    $dl_logging_needed = false;
	}

	// Check if we are only logging unique ips
	if ($unique_ips === true) {
	    $check_ip = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'sdm_downloads WHERE post_id="' . $download_id . '" AND visitor_ip = "' . $ipaddress . '"');

	    //This IP is already logged for this download item. No need to log it again.
	    if ($check_ip) {
		$dl_logging_needed = false;
	    }
	}

	// Check if "Do Not Count Downloads from Bots" setting is enabled
	if (isset($main_option['admin_dont_log_bots'])) {
	    //it is. Now let's check if visitor is a bot
	    if (sdm_visitor_is_bot()) {
		//visitor is a bot. We neither log nor count this download
		$dl_logging_needed = false;
	    }
	}

	if ($dl_logging_needed) {
	    // We need to log this download.
	    $table = $wpdb->prefix . 'sdm_downloads';
	    $data = array(
		'post_id' => $download_id,
		'post_title' => $download_title,
		'file_url' => $download_link,
		'visitor_ip' => $ipaddress,
		'date_time' => $date_time,
		'visitor_country' => $visitor_country,
		'visitor_name' => $visitor_name
	    );

	    $data = array_filter($data); //Remove any null values.
	    $insert_table = $wpdb->insert($table, $data);

	    if ($insert_table) {
		//Download request was logged successfully
	    } else {
		//Failed to log the download request
		wp_die(__('Error! Failed to log the download request in the database table', 'simple-download-monitor'));
	    }
	}

	// Allow plugin extensions to hook into download request.
	do_action('sdm_process_download_request', $download_id, $download_link);

	// Should the item be dispatched?
	$dispatch = apply_filters('sdm_dispatch_downloads', get_post_meta($download_id, 'sdm_item_dispatch', true));

	// Only local file can be dispatched.
	if ($dispatch && (stripos($download_link, WP_CONTENT_URL) === 0)) {
	    // Get file path
	    $file = path_join(WP_CONTENT_DIR, ltrim(substr($download_link, strlen(WP_CONTENT_URL)), '/'));
	    // Try to dispatch file (terminates script execution on success)
	    sdm_dispatch_file($file);
	}

	// As a fallback or when dispatching is disabled, redirect to the file
	// (and terminate script execution).
	sdm_redirect_to_url($download_link);
    
*/   
    }

}
