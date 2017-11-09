<?php
function document_init() {
	register_post_type( 'document', array(
		'labels'            => array(
			'name'                  => __( 'Documents', 'document-manager' ),
			'singular_name'         => __( 'Document', 'document-manager' ),
			'all_items'             => __( 'All Documents', 'document-manager' ),
			'archives'              => __( 'Documents Archives', 'document-manager' ),
			'attributes'            => __( 'Documents Attributes', 'document-manager' ),
			'insert_into_item'      => __( 'Insert into Document', 'document-manager' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Document', 'document-manager' ),
			'featured_image'        => _x( 'Featured Image', 'document', 'document-manager' ),
			'set_featured_image'    => _x( 'Set featured image', 'document', 'document-manager' ),
			'remove_featured_image' => _x( 'Remove featured image', 'document', 'document-manager' ),
			'use_featured_image'    => _x( 'Use as featured image', 'document', 'document-manager' ),
			'filter_items_list'     => __( 'Filter Documents list', 'document-manager' ),
			'items_list_navigation' => __( 'Documents list navigation', 'document-manager' ),
			'items_list'            => __( 'Documents list', 'document-manager' ),
			'new_item'              => __( 'New Document', 'document-manager' ),
			'add_new'               => __( 'Add New', 'document-manager' ),
			'add_new_item'          => __( 'Add New Document', 'document-manager' ),
			'edit_item'             => __( 'Edit Document', 'document-manager' ),
			'view_item'             => __( 'View Document', 'document-manager' ),
			'view_items'            => __( 'View Documents', 'document-manager' ),
			'search_items'          => __( 'Search Documents', 'document-manager' ),
			'not_found'             => __( 'No Documents found', 'document-manager' ),
			'not_found_in_trash'    => __( 'No Documents found in trash', 'document-manager' ),
			'parent_item_colon'     => __( 'Parent Documents:', 'document-manager' ),
			'menu_name'             => __( 'Documents', 'document-manager' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'title' ),
		'has_archive'       => true,
		'rewrite'           => true,
		'query_var'         => true,
		'menu_icon'         => 'dashicons-media-document',
		'show_in_rest'      => true,
		'rest_base'         => 'document',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );		
}
add_action('init', 'document_init');

function document_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['document'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Documents updated. <a target="_blank" href="%s">View Document</a>', 'document-manager'), esc_url( $permalink ) ),
		2 => __('Custom field updated.', 'document-manager'),
		3 => __('Custom field deleted.', 'document-manager'),
		4 => __('Document updated.', 'document-manager'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Document restored to revision from %s', 'document-manager'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Document published. <a href="%s">View Document</a>', 'document-manager'), esc_url( $permalink ) ),
		7 => __('Document saved.', 'document-manager'),
		8 => sprintf( __('Document submitted. <a target="_blank" href="%s">Preview Document</a>', 'document-manager'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		9 => sprintf( __('Document scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Document</a>', 'document-manager'),
		// translators: Publish box date format, see https://secure.php.net/manual/en/function.date.php
		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		10 => sprintf( __('Document draft updated. <a target="_blank" href="%s">Preview Document</a>', 'document-manager'), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'document_updated_messages' );
?>