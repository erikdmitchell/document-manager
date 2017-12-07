<?php

/**
 * DM_Bulk_Import class.
 */
class DM_Bulk_Import {

    /**
     * Admin notices
     *
     * (default value: array())
     *
     * @var array
     * @access protected
     */
    protected $admin_notices = array();

    /**
     * Construct class.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts_styles' ) );
        add_action( 'admin_init', array( $this, 'import_check' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_filter( 'document_manager_admin_tabs', array( $this, 'add_tab' ) );
    }

    /**
     * Add admin tab.
     *
     * @access public
     * @param mixed $tabs array.
     * @return array
     */
    public function add_tab( $tabs ) {
        $tabs[] = array(
            'slug'     => 'import',
            'title'    => 'Bulk Import',
            'pagename' => 'bulk-import',
        );

        return $tabs;
    }

    /**
     * Display admin notices.
     *
     * @access public
     * @return void
     */
    public function admin_notices() {
        $html = '';

        foreach ( $this->admin_notices as $notice ) :
            $html     .= '<div class="notice notice-' . $notice['type'] . ' is-dismissible">';
                $html .= '<p>' . esc_html__( $notice['message'], 'document-manager' ) . '</p>';
            $html     .= '</div>';
        endforeach;

        echo esc_html( $html );
    }

    /**
     * Scripts and styles.
     *
     * @access public
     * @return void
     */
    public function scripts_styles() {
        wp_enqueue_script( 'dm-media-upload-script', DM_URL . 'admin/js/media-upload.js', array( 'jquery' ), DM_VERSION, true );

        wp_enqueue_media();
    }

    /**
     * Check that we hav data to import and import it.
     *
     * @access public
     * @return void
     */
    public function import_check() {
        // check nonce.
        if ( ! isset( $_POST['dm_admin'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dm_admin'] ), 'bulk_import' ) ) { // Input var okay.
                return;
        }

        // check we have file info.
        if ( empty( $_POST['dm_media_filename'] ) || empty( $_POST['dm_file_attachment_id'] ) ) { // Input var okay.
            return false;
        }

        $type  = get_post_mime_type( intval( $_POST['dm_file_attachment_id'] ) ); // Input var okay.
        $mimes = array( 'application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv' );

        // check file type.
        if ( ! in_array( $type, $mimes, true ) ) {
            return false;
        }

        $csv_data = $this->process_csv( validate_file( wp_unslash( $_POST['dm_media_filename'] ) ) ); // Input var okay.

        $this->insert_data_into_posts( $csv_data );
    }

    /**
     * Converts csv file to array.
     *
     * @access protected
     * @param mixed $file_url location of file.
     * @return array
     */
    protected function process_csv( $file_url ) {
        $data   = array();
        $header = array( 'title', 'description', 'author', 'categories', 'tags' );

        ini_set( 'auto_detect_line_endings', true ); // added for issues with MAC.

        $handle = wp_remote_fopen( $file_url, 'r' );

        if ( false !== $handle ) :

            $row = fgetcsv( $handle, 1000, ',' );

            while ( false !== $row ) :

                $data[] = array_combine( $header, $row );

            endwhile;

            fclose( $handle );
        endif;

        return $data;
    }

    /**
     * Imports data into posts.
     *
     * @access protected
     * @param mixed $data array.
     * @return void
     */
    protected function insert_data_into_posts( $data ) {
        foreach ( $data as $row ) :

            // insert post.
            $post_id = wp_insert_post(
                array(
                    'post_title'   => wp_strip_all_tags( $row['title'] ),
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'document',
                )
            );

            // we have an error, so move on.
            if ( is_wp_error( $post_id ) || 0 === $post_id ) {
                continue;
            }

            // add description.
            update_post_meta( $post_id, '_dm_document_description', $row['description'] );

            // add categories and tags.
            wp_set_object_terms( $post_id, $row['categories'], 'document-category', true );
            wp_set_object_terms( $post_id, $row['tags'], 'document-tag', true );

            // add author - create new one if it does not exist.
            $user = get_user_by( 'slug', $row['author'] );

            if ( $user ) :
                wp_update_post(
                    array(
                        'ID'          => $post_id,
                        'post_author' => $user->ID,
                    )
                );
            else :
                $username = $this->create_username( $row['author'] );

                $user_id = wp_insert_user(
                    array(
                        'user_login' => $username['user_login'],
                        'user_pass'  => wp_generate_password(),
                        'first_name' => $username['first_name'],
                        'last_name'  => $username['last_name'],
                    )
                );

                if ( ! is_wp_error( $user_id ) ) :
                    wp_update_post(
                        array(
                            'ID'          => $post_id,
                            'post_author' => $user_id,
                        )
                    );
                endif;
            endif;

        endforeach;

        $this->admin_notices[] = array(
            'type'    => 'success',
            'message' => count( $data ) . ' documents added.',
        );
    }

    /**
     * Create username function.
     *
     * @access protected
     * @param mixed $name string.
     * @return string
     */
    protected function create_username( $name ) {
        $username = array();
        $name_arr = explode( ' ', $name );

        $username['user_login'] = strtolower( str_replace( ' ', '', $name ) );
        $username['first_name'] = array_shift( $name_arr );
        $username['last_name']  = implode( ' ', $name_arr );

        return $username;
    }

}

new Document_Manager_Bulk_Import();
