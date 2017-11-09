<?php
function dm_load_files() {
	$dirs=array(
		'metaboxes',
	);

	foreach ($dirs as $dir) :
		foreach(glob(DM_PATH.$dir.'/*.php') as $file) :
			include_once($file);
		endforeach;
	endforeach;
}
add_action('init', 'dm_load_files', 1);

function dm_get_file_size($file_id=0) {
	$file=get_attached_file($file_id);
	$bytes = filesize($file);
	$s = array('b', 'Kb', 'Mb', 'Gb');
	$e = floor(log($bytes)/log(1024));
	
	return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
}

function dm_file_icon($mime_type='') {
	echo dm_get_file_icon($mime_type);	
}

function dm_get_file_icon($mime_type='') {
	switch ($mime_type) :
		case 'application/pdf':
			$icon_class='fa-file-pdf-o';
			break;
		default:
			$icon_class='fa-file-o';
	endswitch;
	
	$icon='<i class="fa '.$icon_class.'" aria-hidden="true"></i>';
	
	return $icon;
}

function dm_move_metaboxes() {
    global $post, $wp_meta_boxes;
        
    do_meta_boxes(get_current_screen(), 'top', $post);

	unset($wp_meta_boxes['post']['top']);
}
add_action('edit_form_after_title', 'dm_move_metaboxes');

function dm_get_file_version($post_id=0) {
	$current_version=0;
	$meta_version=get_post_meta($data['post_id'], '_dm_document_version', true);
	
	if ($meta_version!='')
		$current_version=$meta_version;
	
	return $current_version;
}
?>