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
	
	return sprintf('%.0f '.$s[$e], ($bytes/pow(1024, floor($e))));
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
	$meta_version=get_post_meta($post_id, '_dm_document_version', true);
	
	if ($meta_version!='')
		$current_version=$meta_version;
	
	return $current_version;
}

function dm_get_file_timestamp($file_id=0) {
	return get_post_meta($file_id, '_dm_document_timestamp', true);
}

function dm_get_file_version_number($file_id=0) {
	return get_post_meta($file_id, '_dm_document_version_number', true);
}

function dm_get_document_url($post_id=0) {
	global $wpdb;
	
	$version=dm_get_file_version($post_id);
	
	$id=$wpdb->get_var("
		SELECT wp_postmeta.post_id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE post_parent = $post_id AND $wpdb->postmeta.meta_key = '_dm_document_version_number' AND $wpdb->postmeta.meta_value = $version
	");
	
	return get_permalink($id);
}

function dm_get_document_id($post_id=0) {
	global $wpdb;
	
	$version=dm_get_file_version($post_id);
	
	$id=$wpdb->get_var("
		SELECT wp_postmeta.post_id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE post_parent = $post_id AND $wpdb->postmeta.meta_key = '_dm_document_version_number' AND $wpdb->postmeta.meta_value = $version
	");
	
	return $id;
}

function dm_parse_args(&$a, $b) {
	$a = (array) $a;
	$b = (array) $b;
	$result = $b;
	
	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = dm_parse_args($v, $result[$k]);
		} else {
			$result[ $k ] = $v;
		}
	}
	
	return $result;	
}