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
?>