<h2>Settings</h2>
<pre>
<?php print_r(DocumentManager()); ?>
</pre>
<form method="post" action="" method="post">
	<?php wp_nonce_field('update_settings', 'dm_admin_update'); ?>

	<h3 class="title"><?php _e('Options', 'document-manager'); ?></h3>

	<table class="form-table options">
		<tbody>
			<tr>
				<th scope="row"><label for="uploads_folder"><?php _e('Uploads Folder', 'document-manager'); ?></label></th>
				<td>
					<input type="text" name="dm_options[uploads_folder]" id="uploads_folder" class="regular-text" value="" />
					<p class="description"><?php _e('The folder documents should be uploaded to. Default is uploads folder.', 'document-manager'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'document-manager'); ?>"></p>
</form>