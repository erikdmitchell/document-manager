<?php

class Document_Manager_Document_Versions_Meta_Box {

    public function __construct() {
        if (is_admin()) :
            add_action('load-post.php',     array($this, 'init_metabox'));
            add_action('load-post-new.php', array($this, 'init_metabox'));
        endif;
    }

    public function init_metabox() {
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post', array($this, 'save_metabox'), 10, 2); // may not need since this is auto generated
    }

    public function add_metabox() {
        add_meta_box(
            'dm-document-versions',
            __('Versions', 'document-manager'),
            array( $this, 'render_metabox'),
            'document',
            'advanced',
            'default'
        );
 
    }

    public function render_metabox($post) {
	    $html='';
	    $files=get_children(array(
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
			//'post_mime_type' => 'image',
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order' => 'ASC',
	    ));
	    
	    $html.='<div class="dm-meta-box-file-list">';
	    
	    	$html.='<div id="file-header" class="dm-meta-box-file header">';
				$html.='<div class="file-name">Name</div>';
				$html.='<div class="file-type">Type</div>';
				$html.='<div class="file-size">Size</div>';
				$html.='<div class="file-version">Version</div>';
				$html.='<div class="file-action"></div>';												
			$html.='</div>';
	    
		foreach ($files as $file) :	
			$html.='<div id="file-'.$file->ID.'" class="dm-meta-box-file">';
				$html.='<div class="file-name">'.$file->post_title.'</div>';
				$html.='<div class="file-type">'.$file->post_mime_type.'</div>';
				$html.='<div class="file-size">'.dm_get_file_size($file->ID).'</div>';
				$html.='<div class="file-version">Version</div>';
				$html.='<div class="file-action"><a href="#" class="make-current">Make Current</a></div>';	
			$html.='</div>';
		endforeach;
		
		$html.='</div>';
		
		$html.='<input type="hidden" name="dmmetabox[post_id]" id="dm-metabox-post-id" value="'.$post->ID.'" />';
		
		echo $html;
    }

    public function save_metabox($post_id, $post) {
        $nonce_name= isset( $_POST['dm_meta_box'] ) ? $_POST['dm_meta_box'] : '';
        $nonce_action='update_document_details';
 
        // Check if nonce is set.
        if (! isset($nonce_name))
            return;
 
        // Check if nonce is valid.
        if (!wp_verify_nonce($nonce_name, $nonce_action))
            return;
 
        // Check if user has permissions to save data.
        if (!current_user_can('edit_post', $post_id))
            return;
 
        // Check if not an autosave.
        if (wp_is_post_autosave($post_id))
            return;
 
        // Check if not a revision.
        if (wp_is_post_revision($post_id))
            return;
    }
}
 
new Document_Manager_Document_Versions_Meta_Box();
?>