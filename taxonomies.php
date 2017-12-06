<?php

/**
 * Initialize document tag
 *
 * @access public
 * @return void
 */
function document_tag_init() {
    register_taxonomy(
        'document-tag', array( 'document' ), array(
            'hierarchical'          => false,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'query_var'             => true,
            'rewrite'               => true,
            'capabilities'          => array(
                'manage_terms' => 'edit_posts',
                'edit_terms'   => 'edit_posts',
                'delete_terms' => 'edit_posts',
                'assign_terms' => 'edit_posts',
            ),
            'labels'                => array(
                'name'                       => __( 'Document tags', 'document-manager' ),
                'singular_name'              => _x( 'Document tag', 'taxonomy general name', 'document-manager' ),
                'search_items'               => __( 'Search Document tags', 'document-manager' ),
                'popular_items'              => __( 'Popular Document tags', 'document-manager' ),
                'all_items'                  => __( 'All Document tags', 'document-manager' ),
                'parent_item'                => __( 'Parent Document tag', 'document-manager' ),
                'parent_item_colon'          => __( 'Parent Document tag:', 'document-manager' ),
                'edit_item'                  => __( 'Edit Document tag', 'document-manager' ),
                'update_item'                => __( 'Update Document tag', 'document-manager' ),
                'view_item'                  => __( 'View Document tag', 'document-manager' ),
                'add_new_item'               => __( 'New Document tag', 'document-manager' ),
                'new_item_name'              => __( 'New Document tag', 'document-manager' ),
                'separate_items_with_commas' => __( 'Separate document tags with commas', 'document-manager' ),
                'add_or_remove_items'        => __( 'Add or remove document tags', 'document-manager' ),
                'choose_from_most_used'      => __( 'Choose from the most used document tags', 'document-manager' ),
                'not_found'                  => __( 'No document tags found.', 'document-manager' ),
                'no_terms'                   => __( 'No document tags', 'document-manager' ),
                'menu_name'                  => __( 'Tags', 'document-manager' ),
                'items_list_navigation'      => __( 'Document tags list navigation', 'document-manager' ),
                'items_list'                 => __( 'Document tags list', 'document-manager' ),
                'most_used'                  => _x( 'Most Used', 'document-tag', 'document-manager' ),
                'back_to_items'              => __( '&larr; Back to Document tags', 'document-manager' ),
            ),
            'show_in_rest'          => true,
            'rest_base'             => 'document-tag',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
        )
    );

}
add_action( 'init', 'document_tag_init', 11 );

/**
 * Document tag messages
 *
 * @access public
 * @param mixed $messages array.
 * @return array
 */
function document_tag_updated_messages( $messages ) {

    $messages['document-tag'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __( 'Document tag added.', 'document-manager' ),
        2 => __( 'Document tag deleted.', 'document-manager' ),
        3 => __( 'Document tag updated.', 'document-manager' ),
        4 => __( 'Document tag not added.', 'document-manager' ),
        5 => __( 'Document tag not updated.', 'document-manager' ),
        6 => __( 'Document tags deleted.', 'document-manager' ),
    );

    return $messages;
}
add_filter( 'term_updated_messages', 'document_tag_updated_messages' );

/**
 * Initialize document category
 *
 * @access public
 * @return void
 */
function document_category_init() {
    register_taxonomy(
        'document-category', array( 'document' ), array(
            'hierarchical'          => true,
            'public'                => true,
            'show_in_nav_menus'     => true,
            'show_ui'               => true,
            'show_admin_column'     => false,
            'query_var'             => true,
            'rewrite'               => true,
            'capabilities'          => array(
                'manage_terms' => 'edit_posts',
                'edit_terms'   => 'edit_posts',
                'delete_terms' => 'edit_posts',
                'assign_terms' => 'edit_posts',
            ),
            'labels'                => array(
                'name'                       => __( 'Document categories', 'document-manager' ),
                'singular_name'              => _x( 'Document category', 'taxonomy general name', 'document-manager' ),
                'search_items'               => __( 'Search Document categories', 'document-manager' ),
                'popular_items'              => __( 'Popular Document categories', 'document-manager' ),
                'all_items'                  => __( 'All Document categories', 'document-manager' ),
                'parent_item'                => __( 'Parent Document category', 'document-manager' ),
                'parent_item_colon'          => __( 'Parent Document category:', 'document-manager' ),
                'edit_item'                  => __( 'Edit Document category', 'document-manager' ),
                'update_item'                => __( 'Update Document category', 'document-manager' ),
                'view_item'                  => __( 'View Document category', 'document-manager' ),
                'add_new_item'               => __( 'New Document category', 'document-manager' ),
                'new_item_name'              => __( 'New Document category', 'document-manager' ),
                'separate_items_with_commas' => __( 'Separate document categories with commas', 'document-manager' ),
                'add_or_remove_items'        => __( 'Add or remove document categories', 'document-manager' ),
                'choose_from_most_used'      => __( 'Choose from the most used document categories', 'document-manager' ),
                'not_found'                  => __( 'No document categories found.', 'document-manager' ),
                'no_terms'                   => __( 'No document categories', 'document-manager' ),
                'menu_name'                  => __( 'Categories', 'document-manager' ),
                'items_list_navigation'      => __( 'Document categories list navigation', 'document-manager' ),
                'items_list'                 => __( 'Document categories list', 'document-manager' ),
                'most_used'                  => _x( 'Most Used', 'document-category', 'document-manager' ),
                'back_to_items'              => __( '&larr; Back to Document categories', 'document-manager' ),
            ),
            'show_in_rest'          => true,
            'rest_base'             => 'document-category',
            'rest_controller_class' => 'WP_REST_Terms_Controller',
        )
    );

}
add_action( 'init', 'document_category_init', 10 );

/**
 * Document category messages
 *
 * @access public
 * @param mixed $messages array.
 * @return array
 */
function document_category_updated_messages( $messages ) {

    $messages['document-category'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => __( 'Document category added.', 'document-manager' ),
        2 => __( 'Document category deleted.', 'document-manager' ),
        3 => __( 'Document category updated.', 'document-manager' ),
        4 => __( 'Document category not added.', 'document-manager' ),
        5 => __( 'Document category not updated.', 'document-manager' ),
        6 => __( 'Document categories deleted.', 'document-manager' ),
    );

    return $messages;
}
add_filter( 'term_updated_messages', 'document_category_updated_messages' );

