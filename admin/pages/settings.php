<h2>Settings</h2>

<form method="post" action="" method="post">
	<?php wp_nonce_field( 'update_settings', 'dm_admin_update' ); ?>

    <h3 class="title"><?php _e( 'Options', 'document-manager' ); ?></h3>

    <table class="form-table options">
        <tbody>
            <tr>
                <th scope="row"><label for="uploads_basefolder"><?php _e( 'Uploads Folder', 'document-manager' ); ?></label></th>
                <td>
                    <code><?php echo get_option( 'siteurl' ); ?></code>
                    <input type="text" name="dm_settings[uploads_basefolder]" id="uploads_basefolder" class="regular-text code" value="<?php echo DocumentManager()->settings['uploads']['basefolder']; ?>" />
                    <p class="description"><?php _e( 'The folder documents should be uploaded to. Default is uploads folder. Pleaser enter the <strong>folder structure only</strong> i.e. /uploads', 'document-manager' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'document-manager' ); ?>"></p>
</form>
