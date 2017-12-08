<?php
/**
 * Functions File
 *
 * @package Document Manager
 */

/**
 * Load files function.
 *
 * @access public
 * @return void
 */
function dm_load_files() {
    $dirs = array(
        'metaboxes',
    );

    foreach ( $dirs as $dir ) :
        foreach ( glob( DM_PATH . $dir . '/*.php' ) as $file ) :
            include_once $file;
        endforeach;
    endforeach;
}
add_action( 'init', 'dm_load_files', 1 );

/**
 * Get file size function.
 *
 * @access public
 * @param int $file_id (default: 0).
 * @return string or int
 */
function dm_get_file_size( $file_id = 0 ) {
    $file  = get_attached_file( $file_id );
    $bytes = absint( filesize( $file ) );
    $s     = array( 'b', 'Kb', 'Mb', 'Gb' );
    $e     = floor( log( $bytes ) / log( 1024 ) );

    if ( 0 === $bytes ) {
        return 0;
    }

    return sprintf( '%.0f ' . $s[ $e ], ( $bytes / pow( 1024, floor( $e ) ) ) );
}

/**
 * File icon function.
 *
 * @access public
 * @param string $mime_type (default: '').
 * @return void
 */
function dm_file_icon( $mime_type = '' ) {
    echo esc_html( dm_get_file_icon( $mime_type ) );
}

/**
 * Get file icon function.
 *
 * @access public
 * @param string $mime_type (default: '').
 * @return icon html
 */
function dm_get_file_icon( $mime_type = '' ) {
    switch ( $mime_type ) :
        case 'application/pdf':
            $icon_class = 'fa-file-pdf-o';
            break;
        default:
            $icon_class = 'fa-file-o';
    endswitch;

    $icon = '<i class="fa ' . $icon_class . '" aria-hidden="true"></i>';

    return $icon;
}

/**
 * Move metaboxes function.
 *
 * @access public
 * @return void
 */
function dm_move_metaboxes() {
    global $post, $wp_meta_boxes;

    do_meta_boxes( get_current_screen(), 'top', $post );

    unset( $wp_meta_boxes['post']['top'] );
}
add_action( 'edit_form_after_title', 'dm_move_metaboxes' );

/**
 * Get file version function.
 *
 * @access public
 * @param int $post_id (default: 0).
 * @return version string
 */
function dm_get_file_version( $post_id = 0 ) {
    $current_version = 0;
    $meta_version    = get_post_meta( $post_id, '_dm_document_version', true );

    if ( '' !== $meta_version ) {
        $current_version = $meta_version;
    }

    return $current_version;
}

/**
 * Get file timestamp function.
 *
 * @access public
 * @param int $file_id (default: 0).
 * @return timestamp
 */
function dm_get_file_timestamp( $file_id = 0 ) {
    return get_post_meta( $file_id, '_dm_document_timestamp', true );
}

/**
 * Get file version number function.
 *
 * @access public
 * @param int $file_id (default: 0).
 * @return number
 */
function dm_get_file_version_number( $file_id = 0 ) {
    return get_post_meta( $file_id, '_dm_document_version_number', true );
}

/**
 * Get document url function.
 *
 * @access public
 * @param int $post_id (default: 0).
 * @return url
 */
function dm_get_document_url( $post_id = 0 ) {
    global $wpdb;

    $version = dm_get_file_version( $post_id );

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "
		SELECT wp_postmeta.post_id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE post_parent = %d AND $wpdb->postmeta.meta_key = '_dm_document_version_number' AND $wpdb->postmeta.meta_value = %d
	", $post_id, $version
        )
    );

    return get_permalink( $id );
}

/**
 * Get document id function.
 *
 * @access public
 * @param int $post_id (default: 0).
 * @return id
 */
function dm_get_document_id( $post_id = 0 ) {
    global $wpdb;

    $version = dm_get_file_version( $post_id );

    $id = $wpdb->get_var(
        $wpdb->prepare(
            "
		SELECT wp_postmeta.post_id
		FROM $wpdb->posts
		LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
		WHERE post_parent = %d AND $wpdb->postmeta.meta_key = '_dm_document_version_number' AND $wpdb->postmeta.meta_value = %d
	", $post_id, $version
        )
    );

    return $id;
}

/**
 * Custom parse args function.
 *
 * @access public
 * @param mixed $a string or array.
 * @param mixed $b string.
 * @return array
 */
function dm_parse_args( &$a, $b ) {
    $a      = (array) $a;
    $b      = (array) $b;
    $result = $b;

    foreach ( $a as $k => &$v ) {
        if ( is_array( $v ) && isset( $result[ $k ] ) ) {
            $result[ $k ] = dm_parse_args( $v, $result[ $k ] );
        } else {
            $result[ $k ] = $v;
        }
    }

    return $result;
}

/**
 * Display document id function.
 *
 * @access public
 * @param int $post_id (default: 0).
 * @return void
 */
function dm_document_doc_id( $post_id = 0 ) {
    echo intval( dm_get_document_id( $post_id ) );
}

/**
 * Display document description function.
 *
 * @access public
 * @return void
 */
function dm_document_description() {
    global $post;

    echo esc_html( get_post_meta( $post->ID, '_dm_document_description', true ) );
}

/**
 * dm_document_download_url function.
 * 
 * @access public
 * @param int $id (default: 0).
 * @return void
 */
function dm_document_download_url( $id = 0 ) {
    echo dm_get_document_download_url( $id );
}

/**
 * dm_get_document_download_url function.
 * 
 * @access public
 * @param int $id (default: 0).
 * @return url
 */
function dm_get_document_download_url( $id = 0 ) {
    if ('document' === get_post_type($id))
        $id = dm_get_document_id( $id );
    
    $url = wp_nonce_url(home_url("/?document_id=$id&http_referer=" . esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) )), 'process_download', 'dm_document_download');
    
    return $url;
}

/**
 * dm_document_image function.
 * 
 * @access public
 * @param int $id (default: 0).
 * @param string $size (default: 'medium').
 * @return void
 */
function dm_document_image( $id = 0, $size = 'medium' ) {
    echo dm_get_document_image( $id, $size );
}

/**
 * dm_get_document_image function.
 * 
 * @access public
 * @param int $id (default: 0).
 * @param string $size (default: 'medium').
 * @return url
 */
function dm_get_document_image( $id = 0, $size = 'medium' ) {
    if ('document' === get_post_type($id))
        $id = dm_get_document_id( $id );
        
    $image = wp_get_attachment_image( $id , $size );
    
    return $image;
}