<?php

class Document_Manager_Bulk_Import {
	
	public function __construct() {
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
	
}	

new Document_Manager_Bulk_Import();