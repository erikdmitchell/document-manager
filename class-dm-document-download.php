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

    /**
     * Downloads the document.
     * 
     * @access public
     * @return void
     */
    public function handle_download_via_post() {
        global $wpdb;
        
        if (!isset($_REQUEST['dm_document_download']) || !wp_verify_nonce($_REQUEST['dm_document_download'], 'process_download'))
            return;
        
        $dm_document_download_logging = true;
        $dispatch = true;
        $document_id = absint( $_REQUEST['document_id'] );
        $document_title = dm_get_document_title( $document_id );
        $document_url = wp_get_attachment_url( $document_id );
        $redirect_url = home_url( $_REQUEST['http_referer'] );
        
        // No document id.
        if (!$document_id)
            return false;
        
        // No document url
        if (empty($document_url))
            return false;
            
        $ipaddress = $this->get_ip_address();
        $date_time = current_time('mysql');
        $userinfo = $this->get_logged_in_userinfo();

        // Log this download (if true).        
    	if ($dm_document_download_logging) :
    	    $table = $wpdb->prefix . 'document_manager_downloads';
    	    $data = array(
    		    'post_id' => wp_get_post_parent_id($document_id),
                'post_title' => $document_title,
                'doc_id' => $document_id,
                'file_url' => $document_url,
                'user_ip' => $ipaddress,
                'date_time' => $date_time,
                'user_id' => $userinfo['ID'],
                'username' => $userinfo['username'],
    	    );

    	    $data = array_filter($data); // Remove any null values.
    	    $insert_table = $wpdb->insert($table, $data);
    
            // check table failed to insert.
    	    if (!$insert_table) :
        	    return false;
            endif;

    	endif;
	
        // Only local file can be dispatched.
        if ($dispatch && (stripos($document_url, WP_CONTENT_URL) === 0)) :
    	    // Get file path.
            $file = path_join(WP_CONTENT_DIR, ltrim(substr($document_url, strlen(WP_CONTENT_URL)), '/'));
            
            // Try to dispatch file (terminates script execution on success)
            $this->dispatch_file($file);
        endif;

    	// As a fallback or when dispatching is disabled, redirect to the file (and terminate script execution).
    	$this->redirect_to_url($document_url);
    }
    
    /**
     * Get remote IP address.
     * @link http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
     *
     * @param bool $ignore_private_and_reserved Ignore IPs that fall into private or reserved IP ranges.
     * @return mixed IP address as a string or null, if remote IP address cannot be determined (or is ignored).
     */
    protected function get_ip_address($ignore_private_and_reserved = false) {
        $flags = $ignore_private_and_reserved ? (FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) : 0;
        
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) :
    	    if (array_key_exists($key, $_SERVER) === true) :
        	    foreach (explode(',', $_SERVER[$key]) as $ip) :
            		$ip = trim($ip); // just to be safe
            
            		if (filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false) :
            		    return $ip;
            		endif;
        	    endforeach;
            endif;
        endforeach;
        
        return null;
    }

    /**
     * get_logged_in_userinfo function.
     * 
     * @access protected
     * @return void
     */
    protected function get_logged_in_userinfo() {
        $userinfo=array(
            'ID' => 0,
            'username' => 'No Username',  
        );
    
         // Get WP user name (if logged in).
        if (is_user_logged_in()) : 
        	$current_user = wp_get_current_user();
        	
        	$userinfo['ID']=$current_user->ID;
        	$userinfo['username']= $current_user->user_login;
        endif;

        return $userinfo;
    }

    /**
     * Force download of the file.
     * 
     * @access protected
     * @param mixed $filename
     * @return void
     */
    protected function dispatch_file($filename) {
    
        if (headers_sent()) {
        	trigger_error(__FUNCTION__ . ": Cannot dispatch file $filename, headers already sent.");
            
            return;
        }
    
        if (!is_readable($filename)) {
    	    trigger_error(__FUNCTION__ . ": Cannot dispatch file $filename, file is not readable.");
            
            return;
        }
    
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream'); // http://stackoverflow.com/a/20509354
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
    
        ob_end_clean();
        readfile($filename);
        exit;
    }

    /**
     * Redirect to specific url.
     * 
     * @access protected
     * @param mixed $url
     * @param string $delay (default: '0')
     * @param string $exit (default: '1')
     * @return void
     */
    protected function redirect_to_url($url, $delay = '0', $exit = '1') {        
        if (empty($url)) :
    	    echo '<strong>';
                _e('Error! The URL value is empty. Please specify a correct URL value to redirect to!', 'simple-download-monitor');
            echo '</strong>';
            
            exit;
        endif;
        
        if (!headers_sent()) :
    	    header('Location: ' . $url);
        else :
        	echo '<meta http-equiv="refresh" content="' . $delay . ';url=' . $url . '" />';
        endif;
        
        if ($exit == '1') :
        	exit;
        endif;
    }
}
