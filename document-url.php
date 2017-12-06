<?php
class Document_Manager_Document_URL {

	public function __construct() {
		$this->init();
	}

	public function init() {
		add_action('single_template', array($this, 'single_document_template_redirect'));
	}
	
	public function single_document_template_redirect() {
        global $post;   
        
        if ($post->post_type != 'document')
            return $single_template;
    
        $template='';
    	$template_name='document';
    
    	if (file_exists(get_stylesheet_directory().'/pickle-custom-login/'.$template_name.'.php')) :
    		$template=get_stylesheet_directory().'/pickle-custom-login/'.$template_name.'.php';
    	elseif (file_exists(get_template_directory().'/pickle-custom-login/'.$template_name.'.php')) :
    		$template=get_template_directory().'/pickle-custom-login/'.$template_name.'.php';
    	else :
    		$template=DM_PATH.'templates/'.$template_name.'.php';
    	endif;
    
        if (!empty($template))
            return $template;
    
        return $single_template;
	}

}