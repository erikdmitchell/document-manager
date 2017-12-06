<?php

class Document_Manager_Bulk_Import {
    
    protected $admin_notices=array();
	
	public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'scripts_styles'));    	
    	add_action('admin_init', array($this, 'import_check'));
    	add_action('admin_notices', array($this, 'admin_notices'));
    	
        add_filter('document_manager_admin_tabs', array($this, 'add_tab'));
	}
	
	public function add_tab($tabs) {
		$tabs[]=array(
    		'slug' => 'import',
    		'title' => 'Bulk Import',
    		'pagename' => 'bulk-import',
		);
    	
    	return $tabs;
	}
	
	public function admin_notices() {
    	$html='';
    	
    	foreach ($this->admin_notices as $notice) :
    	    $html.='<div class="notice notice-'.$notice['type'].' is-dismissible">';
    	        $html.='<p>'.__($notice['message'], 'document-manager').'</p>';
    	    $html.='</div>';
    	endforeach;
    	
    	echo $html;
	}
	
	public function scripts_styles() {
 		wp_enqueue_script('dm-media-upload-script', DM_URL.'admin/js/media-upload.js', array('jquery'), DM_VERSION, true);
   	
        wp_enqueue_media();
	}
	
	public function import_check() {
        // check nonce //
        if (!isset($_POST['dm_admin']) || !wp_verify_nonce($_POST['dm_admin'], 'bulk_import'))
    			return;
    
        // check we have file info //
        if (empty($_POST['dm_media_filename']) || empty($_POST['dm_file_attachment_id']))
            return false;
            
        $type=get_post_mime_type($_POST['dm_file_attachment_id']);
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
        
        // check file type //
        if (!in_array($type, $mimes))
            return false;
        
        $csv_data=$this->process_csv($_POST['dm_media_filename']);
        
        $this->insert_data_into_posts($csv_data);
	}
	
	protected function process_csv($file_url) {
    	$data=array();
    	$header=array('title', 'description', 'author', 'categories', 'tags');
    	
    	ini_set('auto_detect_line_endings',TRUE); // added for issues with MAC
    	
        if (($handle = fopen($file_url, 'r')) !== false) :
        
		    while (($row = fgetcsv($handle, 1000, ',')) !== false) :

                $data[]=array_combine($header, $row);

            endwhile;
            
            fclose($handle);
        endif;	
        
        return $data;
	}
	
	protected function insert_data_into_posts($data) {
    	foreach ($data as $row) :
    	    
    	    // insert post //
    	    $post_id=wp_insert_post(array(
        	    'post_title' => wp_strip_all_tags($row['title']),
        	    'post_content' => '',
        	    'post_status' => 'publish',
        	    'post_type' => 'document',
    	    ));
    	    
    	    // we have an error, so move on //
    	    if (is_wp_error($post_id) || 0 === $post_id)
    	        continue;
    	    
    	    // add description //
    	    update_post_meta($post_id, '_dm_document_description', $row['description']);
    	    
    	    // add categories and tags //
    	    wp_set_object_terms($post_id, $row['categories'], 'document-category', true);
    	    wp_set_object_terms($post_id, $row['tags'], 'document-tag', true);

    	    // add author - create new one if it does not exist //
    	    if ($user=get_user_by('slug', $row['author'])) :
    	        wp_update_post(array(
        	        'ID' => $post_id,
        	        'post_author' => $user->ID,
    	        ));
    	    else :
    	        $username=$this->create_username($row['author']);
    	        
        	    $user_id=wp_insert_user(array(
            	    'user_login' => $username['user_login'],
            	    'user_pass' => wp_generate_password(),
            	    'first_name' => $username['first_name'],
            	    'last_name' => $username['last_name'],
        	    ));
        	    
                if (!is_wp_error($user_id)) :
                    wp_update_post(array(
            	        'ID' => $post_id,
            	        'post_author' => $user_id,
        	        ));
                endif;
            endif;
    	    
    	endforeach;
    	
    	$this->admin_notices[]=array(
            'type' => 'success',
            'message' => count($data).' documents added.',
    	);
	}	
	
	protected function create_username($name) {
    	$username=array();
    	$name_arr=explode(' ', $name);
    	
    	$username['user_login']=strtolower( str_replace(' ', '', $name) );
    	$username['first_name']=array_shift($name_arr);
    	$username['last_name']=implode(' ', $name_arr);
    	
    	return $username;
	}
	
}	

new Document_Manager_Bulk_Import();