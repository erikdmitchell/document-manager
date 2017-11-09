<?php

class Document_Manager_Admin {
	
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'scripts_styles'));
		add_action('wp_ajax_dm_metabox_upload_file', array($this, 'ajax_upload_file'));
	}
	
	public function scripts_styles() {
		wp_register_script('dm-metaboxes-script', DM_URL.'admin/js/metaboxes.js', array('jquery'), DM_VERSION, true);
		
		wp_localize_script('dm-metaboxes-script', 'dmMetaboxOptions', array(
			'nonce' => wp_create_nonce('dm-upload-file'),
		));
		
		wp_enqueue_script('dm-metaboxes-script');
		
		wp_enqueue_style('font-awesome', DM_URL.'css/font-awesome.min.css', '', '4.7.0');
		wp_enqueue_style('dm-metaboxes-style', DM_URL.'admin/css/metaboxes.css', '', DM_VERSION);
	}
	
	public function user_can_save($nonce_name, $nonce) {
		$is_valid_nonce = (isset($nonce) && wp_verify_nonce($nonce, $nonce_name));
		
		return $is_valid_nonce;		
	}
	
	public function ajax_upload_file() {
		$fileErrors = array(
		  	0 => "There is no error, the file uploaded with success",
		  	1 => "The uploaded file exceeds the upload_max_files in server settings",
		  	2 => "The uploaded file exceeds the MAX_FILE_SIZE from html form",
		  	3 => "The uploaded file uploaded only partially",
		  	4 => "No file was uploaded",
		  	6 => "Missing a temporary folder",
		  	7 => "Failed to write file to disk",
		  	8 => "A PHP extension stoped file to upload" 
  		);
  		$data=array_merge($_POST, $_FILES);
		$response=array();	

		if ($this->user_can_save('dm-upload-file', $data['security'])) :
			$attachment_id=media_handle_upload('file', $data['post_id']);
		
			if (is_wp_error($attachment_id)) :
				$response['response'] = "ERROR";
				$response['error']=$fileErrors[$data['file']['error']];
			else :
				$fullsize_path = get_attached_file( $attachment_id );
				$pathinfo = pathinfo( $fullsize_path );
				$url = wp_get_attachment_url( $attachment_id );
				$response['response'] = "SUCCESS";
				$response['filename'] = $pathinfo['filename'];
				$response['url'] = $url;
				$response['type'] = $pathinfo['extension'];
			endif;
		else :
			// todo
		endif;		
		
		echo json_encode($response);	
		
		wp_die();
	}
	
}	
?>