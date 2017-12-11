<h2>Settings</h2>

<form method="post" action="" method="post">
	<?php wp_nonce_field( 'update_settings', 'dm_admin_update' ); ?>

    <h3 class="title"><?php esc_attr_e( 'Options', 'document-manager' ); ?></h3>

    <table class="form-table options">
        <tbody>
            <tr>
                <th scope="row"><label for="uploads_basefolder"><?php esc_attr_e( 'Uploads Folder', 'document-manager' ); ?></label></th>
                <td>
                    <code><?php esc_html_e( get_option( 'siteurl' ), 'document-manager' ); ?></code>
                    <input type="text" name="dm_settings[uploads_basefolder]" id="uploads_basefolder" class="regular-text code" value="<?php esc_attr_e( DocumentManager()->settings['uploads']['basefolder'], 'document-manager' ); ?>" />
                    <p class="description"><?php esc_attr_e( 'The folder documents should be uploaded to. Default is uploads folder. Pleaser enter the <strong>folder structure only</strong> i.e. /uploads', 'document-manager' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'document-manager' ); ?>"></p>
</form>
