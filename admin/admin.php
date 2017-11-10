<?php

class Document_Manager_Admin {
	
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, 'scripts_styles'));
		add_action('init', array($this, 'update_settings'), 0);
		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('wp_handle_upload_prefilter', array($this, 'modify_uploaded_file_names'), 1, 1);
		add_action('wp_ajax_dm_metabox_upload_file', array($this, 'ajax_upload_file'));
		add_action('wp_ajax_dm_reload_metabox', array($this, 'ajax_reload_metabox'));
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
	
	public function admin_menu() {
		add_options_page('Document Manager', 'Document Manager', 'manage_options', 'document-manager', array($this, 'admin_page'));
	}
	
	public function admin_page() {
		$html=null;
		$tabs=array(
			'settings' => 'Settings',
			//'emails' => 'Emails',
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
			
		$html.='<div class="wrap dm-admin">';
			$html.='<h1>Document Manager</h1>';
			
			$html.='<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $key => $name) :
					if ($active_tab==$key) :
						$class='nav-tab-active';
					else :
						$class=null;
					endif;

					$html.='<a href="?page=document-manager&tab='.$key.'" class="nav-tab '.$class.'">'.$name.'</a>';
				endforeach;
			$html.='</h2>';

			switch ($active_tab) :					
				default:
					$html.=$this->get_admin_page('settings');
			endswitch;

		$html.='</div>';

		echo $html;		
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
				$fullsize_path = get_attached_file($attachment_id);
				$pathinfo = pathinfo( $fullsize_path );
				$url = wp_get_attachment_url( $attachment_id );
				$response['response'] = "SUCCESS";
				$response['filename'] = $pathinfo['filename'];
				$response['url'] = $url;
				$response['type'] = $pathinfo['extension'];
				$this->add_file_meta($attachment_id, $data['post_id']);
			endif;
		else :
			// todo
		endif;		
		
		echo json_encode($response);	
		
		wp_die();
	}

	public function modify_uploaded_file_names($file) {
	    // Get the parent post ID, if there is one
	    if( isset($_GET['post_id']) ) {
	        $post_id = $_GET['post_id'];
	    } elseif( isset($_POST['post_id']) ) {
	        $post_id = $_POST['post_id'];
	    }

		if (get_post_type($post_id)!='document')
			return $file;

		$current_version=dm_get_file_version($post_id);
		$new_version=(int) $current_version + 1;
		
		$filename=pathinfo($file['name'], PATHINFO_FILENAME);
		$filename_ext=pathinfo($file['name'], PATHINFO_EXTENSION);
		
		$file['name']="$filename-version-$new_version.$filename_ext";

		// update version //
		update_post_meta($post_id, '_dm_document_version', $new_version);

		return $file;
	}
	
	protected function add_file_meta($file_id=0, $post_id=0) {
		add_post_meta($file_id, '_dm_document_timestamp', current_time('mysql')); // add timestamp
		add_post_meta($file_id, '_dm_document_version_number', dm_get_file_version($post_id)); // add version
	}
	
	public function ajax_reload_metabox() {
		$metabox=$_POST['metabox'];
		$mb=new $metabox();
		$post=get_post($_POST['post_id']);
		
		echo $mb->render_metabox($post);
		
		wp_die();
	}

	public function update_settings() {	
		if (!isset($_POST['dm_admin_update']) || !wp_verify_nonce($_POST['dm_admin_update'], 'update_settings'))
			return;

		$settings_data=$_POST['dm_settings'];
		$new_settings=array();
		$current_settings=get_option('dm_settings', '');
		
		// update uploads folder //
		if ($settings_data['uploads_basefolder']!=$current_settings['uploads']['basefolder']) :
			$new_settings['uploads']=$this->update_uploads_folder($settings_data['uploads_basefolder']);
		endif;
		
		$settings=dm_parse_args($new_settings, $current_settings);
			
		update_option('dm_settings', $settings);
/*			
		$this->admin_notices['updated']='Settings Updated!';
*/
	}
	
	private function update_uploads_folder($basefolder='') {
		if (empty($basefolder))
			return;
			
		$siteurl=get_option('siteurl');
		$upload_path=trim(get_option('upload_path')); // NA
		$dir=rtrim(ABSPATH, '/').$basefolder;
		$url=$siteurl.$basefolder;
		$basedir=$dir;
	    $baseurl=$url;
	    $subdir = '';
	    
		if (get_option('uploads_use_yearmonth_folders')) :
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "/$y/$m";
		endif;
		
		 
		$dir.= $subdir;
	    $url.= $subdir;
 
	    return array(
	        'path'    => $dir,
	        'url'     => $url,
	        'subdir'  => $subdir,
	        'basedir' => $basedir,
	        'baseurl' => $baseurl, 
	        'error'   => false,
	        'basefolder' => $basefolder,
	    );	
	}

	public function get_admin_page($template_name=false) {
		if (!$template_name)
			return false;

		ob_start();

		do_action('dm_before_admin_'.$template_name);

		include(DM_PATH.'admin/pages/'.$template_name.'.php');

		do_action('dm_after_admin_'.$template_name);

		$html=ob_get_contents();

		ob_end_clean();

		return $html;
	}	
	
}	
?>