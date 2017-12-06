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
			add_filter('upload_dir', array($this, 'change_upload_dir'));
		
			//$attachment_id=media_handle_upload('file', $data['post_id']);
			$attachment_id=$this->handle_upload('file', $data['post_id']);
		
			remove_filter('upload_dir', array($this, 'change_upload_dir'));
		
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
	
	private function handle_upload($file_id, $post_id) {
        $time = current_time('mysql');

        if ( $post = get_post($post_id) ) {
            // The post date doesn't usually matter for pages, so don't backdate this upload.
            if ( 'page' !== $post->post_type && substr( $post->post_date, 0, 4 ) > 0 )
                $time = $post->post_date;
        }
       
        $file = wp_handle_upload($_FILES[$file_id], array('test_form' => false ), $time);
        
        if( isset( $file['error'] ) )
            return new WP_Error( 'upload_error', $file['error'] );
    
        $name = $_FILES[$file_id]['name'];
        $ext  = pathinfo( $name, PATHINFO_EXTENSION );
        $name = wp_basename( $name, ".$ext" );
        
        $url = $file['url'];
        $type = $file['type'];
        $file = $file['file'];
        $title = sanitize_text_field( $name );
        $content = '';
        $excerpt = '';

        if ( preg_match( '#^audio#', $type ) ) {
            $meta = wp_read_audio_metadata( $file );
     
            if ( ! empty( $meta['title'] ) ) {
                $title = $meta['title'];
            }
     
            if ( ! empty( $title ) ) {
     
                if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio track title, 2: album title, 3: artist name */
                    $content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta['album'], $meta['artist'] );
                } elseif ( ! empty( $meta['album'] ) ) {
                    /* translators: 1: audio track title, 2: album title */
                    $content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta['album'] );
                } elseif ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio track title, 2: artist name */
                    $content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta['artist'] );
                } else {
                    /* translators: 1: audio track title */
                    $content .= sprintf( __( '"%s".' ), $title );
                }
     
            } elseif ( ! empty( $meta['album'] ) ) {
     
                if ( ! empty( $meta['artist'] ) ) {
                    /* translators: 1: audio album title, 2: artist name */
                    $content .= sprintf( __( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
                } else {
                    $content .= $meta['album'] . '.';
                }
     
            } elseif ( ! empty( $meta['artist'] ) ) {
     
                $content .= $meta['artist'] . '.';
     
            }
     
            if ( ! empty( $meta['year'] ) ) {
                /* translators: Audio file track information. 1: Year of audio track release */
                $content .= ' ' . sprintf( __( 'Released: %d.' ), $meta['year'] );
            }
     
            if ( ! empty( $meta['track_number'] ) ) {
                $track_number = explode( '/', $meta['track_number'] );
                if ( isset( $track_number[1] ) ) {
                    /* translators: Audio file track information. 1: Audio track number, 2: Total audio tracks */
                    $content .= ' ' . sprintf( __( 'Track %1$s of %2$s.' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
                } else {
                    /* translators: Audio file track information. 1: Audio track number */
                    $content .= ' ' . sprintf( __( 'Track %1$s.' ), number_format_i18n( $track_number[0] ) );
                }
            }
     
            if ( ! empty( $meta['genre'] ) ) {
                /* translators: Audio file genre information. 1: Audio genre name */
                $content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta['genre'] );
            }
     
        // Use image exif/iptc data for title and caption defaults if possible.
        } elseif ( 0 === strpos( $type, 'image/' ) && $image_meta = @wp_read_image_metadata( $file ) ) {
            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
                $title = $image_meta['title'];
            }
     
            if ( trim( $image_meta['caption'] ) ) {
                $excerpt = $image_meta['caption'];
            }
        }
  
        $attachment = array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset( $attachment['ID'] );
        
        // Save the data
        $id = wp_insert_attachment( $attachment, $file, $post_id, true );
        
        if ( !is_wp_error($id) ) :
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
        endif;
    
        return $id;   	
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
		
		$file['name']=md5($name) . "-version-$new_version.$filename_ext";

		// update version //
		update_post_meta($post_id, '_dm_document_version', $new_version);

		return $file;
	}
	
	public function change_upload_dir($dirs) {
		$dirs['subdir']='';
		$dirs['path']=DocumentManager()->settings['uploads']['basedir'];
		$dirs['url']=DocumentManager()->settings['uploads']['baseurl'];
		
		return $dirs;
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
	    
		if (get_option('uploads_use_yearmonth_folders')) : // test this
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$subdir = "/$y/$m";
		endif;
		
		$dir.= $subdir;
	    $url.= $subdir;
	    
	    // create folder if need be //
		if (!is_dir($basedir))
			mkdir($basedir, 0700);
 
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