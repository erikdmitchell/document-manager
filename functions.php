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


?>