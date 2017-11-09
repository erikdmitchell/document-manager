<?php
class Document_Manager_Document_URL {

	public function __construct() {
		$this->init();
	}

	public function init() {
		// Standard hooks
		//add_action('wp_list_pages', array($this, 'wp_list_pages'));
		add_action('template_redirect', array($this, 'template_redirect'));


		//$this->hook( 'save_post'           );
		//$this->hook( 'edit_attachment'     );
		//$this->hook( 'wp_nav_menu_objects' );
		//$this->hook( 'plugin_row_meta'     );
	}

	protected function get_link($post_id) {		
		return dm_get_document_url($post_id);
	}
	
	public function template_redirect() {
		$link=$this->get_redirect();

		if ($link) :
			wp_redirect($link, 301);
			exit;
		endif;
	}

	protected function get_redirect() {
		if (!is_singular() || !get_queried_object_id())
			return false;
			
		if (get_post_type(get_queried_object_id())!='document')
			return false;

		$link=$this->get_link(get_queried_object_id());

		return $link;
	}

/*
	function wp_list_pages( $pages ) {
		$highlight = false;

		// We use the "fetch all" versions here, because the pages might not be queried here
		$links = $this->get_links();
		$targets = $this->get_targets();
		$targets_by_url = array();
		foreach( array_keys( $targets ) as $targeted_id )
			$targets_by_url[$links[$targeted_id]] = true;

		if ( ! $links ) {
			return $pages;
		}

		$this_url = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		foreach ( (array) $links as $id => $page ) {
			if ( isset( $targets_by_url[$page] ) ) {
				$page .= '#new_tab';
			}
			if ( str_replace( 'http://www.', 'http://', $this_url ) === str_replace( 'http://www.', 'http://', $page ) || ( is_home() && str_replace( 'http://www.', 'http://', trailingslashit( get_bloginfo( 'url' ) ) ) === str_replace( 'http://www.', 'http://', trailingslashit( $page ) ) ) ) {
				$highlight = true;
				$current_page = esc_url( $page );
			}
		}

		if ( count( $targets_by_url ) ) {
			foreach ( array_keys( $targets_by_url ) as $p ) {
				$p = esc_url( $p . '#new_tab' );
				$pages = str_replace( '<a href="' . $p . '"', '<a href="' . $p . '" target="_blank"', $pages );
			}
		}

		if ( $highlight ) {
			$pages = preg_replace( '| class="([^"]+)current_page_item"|', ' class="$1"', $pages ); // Kill default highlighting
			$pages = preg_replace( '|<li class="([^"]+)"><a href="' . preg_quote( $current_page ) . '"|', '<li class="$1 current_page_item"><a href="' . $current_page . '"', $pages );
		}
		return $pages;
	}
*/

}