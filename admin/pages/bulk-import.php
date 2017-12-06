<h2>Bulk Import</h2>

<form method="post" action="" method="post">
	<?php wp_nonce_field( 'bulk_import', 'dm_admin' ); ?>

    <input type="url" name="dm_media_filename" id="dm-media-filename" class="regular-text code" value="">
    <input id="dm-media-upload-button" type="button" class="button" value="<?php _e( 'Upload CSV File', 'document-manager' ); ?>" />
    <input type="hidden" name="dm_file_attachment_id" id="dm-file-attachment-id" value="">
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Import', 'document-manager' ); ?>"></p>
</form>
