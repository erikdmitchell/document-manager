<h2>Settings</h2>

<form method="post" action="" method="post">
	<?php wp_nonce_field('update_settings', 'pcl_admin_update'); ?>

	<h3 class="title"><?php _e('Pages', 'pcl'); ?></h3>

	<table class="form-table pages">
		<tbody>
			<tr>
				<th scope="row"><label for="login_page"><?php _e('Login Page', 'pcl'); ?></label></th>
				<td>
					<?php pickle_custom_login()->admin->pcl_admin_dropdown_pages('login', pickle_custom_login()->pages['login']); ?>
					<p class="description"><?php _e('Include the shortcode', 'pcl'); ?> [pcl-login-form]</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="register_page"><?php _e('Registration Page', 'pcl'); ?></label></th>
				<td>
					<?php pickle_custom_login()->admin->pcl_admin_dropdown_pages('register', pickle_custom_login()->pages['register']); ?>
					<p class="description"><?php _e('Include the shortcode', 'pcl'); ?> [pcl-registration-form]</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="forgot_password_page"><?php _e('Forgot Password Page', 'pcl'); ?></label></th>
				<td>
					<?php pickle_custom_login()->admin->pcl_admin_dropdown_pages('forgot-password', pickle_custom_login()->pages['forgot-password']); ?>
					<p class="description"><?php _e('Include the shortcode', 'pcl'); ?> [pcl-forgot-password-form]</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="reset_page"><?php _e('Reset Password Page', 'pcl'); ?></label></th>
				<td>
					<?php pickle_custom_login()->admin->pcl_admin_dropdown_pages('reset-password', pickle_custom_login()->pages['reset-password']); ?>
					<p class="description"><?php _e('Include the shortcode', 'pcl'); ?> [pcl-reset-password-form]</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="activate_page"><?php _e('Activation Page', 'pcl'); ?></label></th>
				<td>
					<?php pickle_custom_login()->admin->pcl_admin_dropdown_pages('activate-account', pickle_custom_login()->pages['activate-account']); ?>
					<p class="description"><?php _e('Include the shortcode', 'pcl'); ?> [pcl-user-activation]</p>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="title"><?php _e('Redirects', 'pcl'); ?></h3>

	<table class="form-table redirects">
		<tbody>

			<tr>
				<th scope="row"><label for="redirect_users"><?php _e('Redirect Users', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[redirect][pcl-login-redirect]" type="url" id="redirect_users" value="<?php echo pcl_login_redirect_url(); ?>" class="regular-text code">
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="redirect_after_registration"><?php _e('Redirect Users After Registration', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[redirect][pcl-register-redirect]" type="url" id="redirect_after_registration" value="<?php echo pcl_register_redirect_url(); ?>" class="regular-text code">
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="redirect_after_logout"><?php _e('Redirect Users After Logout', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[redirect][pcl-logout-redirect]" type="url" id="redirect_after_logout" value="<?php echo pcl_logout_redirect_url(); ?>" class="regular-text code">
				</td>
			</tr>

		</tbody>
	</table>

	<h3 class="title"><?php _e('General', 'pcl'); ?></h3>

	<table class="form-table general">
		<tbody>
			<tr>
				<th scope="row"><label for="hide_admin_bar"><?php _e('Hide Admin Bar', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[hide_admin_bar]" type="checkbox" id="hide_admin_bar" value="1" <?php checked(pcl_hide_admin_bar(), 1); ?>>
					<p class="description" id="hide_admin_bar_description"><?php _e('If checked, the admin bar would be hidden for non administrators.', 'pcl'); ?></p></td>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="title"><?php _e('reCaptcha', 'pcl'); ?></h3>

	<table class="form-table recaptcha">
		<tbody>
			<tr>
				<th scope="row"><label for="enable_recaptcha"><?php _e('Enable reCaptcha', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[enable_recaptcha]" type="checkbox" id="enable_recaptcha" value="1" <?php checked(pcl_enable_recaptcha(), 1); ?>>
					<p class="description" id="enable_recaptcha_description"><?php _e('Enable reCaptcha form on registration page.', 'pcl'); ?></p></td>
				</td>
			</tr>
			<tr class="recaptcha-details-field hidden">
				<th scope="row"><label for="recaptcha_site_key"><?php _e('Site Key', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[recaptcha_site_key]" type="text" id="recaptcha_site_key" value="<?php echo get_option('pcl-recaptcha-site-key', ''); ?>" class="regular-text code">
				</td>
			</tr>
			<tr class="recaptcha-details-field hidden">
				<th scope="row"><label for="recaptcha_secret_key"><?php _e('Secret Key</label', 'pcl'); ?>></th>
				<td>
					<input name="pcl_settings[recaptcha_secret_key]" type="text" id="recaptcha_secret_key" value="<?php echo get_option('pcl-recaptcha-secret-key', ''); ?>" class="regular-text code">
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="title"><?php _e('Account Activation', 'pcl'); ?></h3>

	<table class="form-table account-activation">
		<tbody>
			<tr>
				<th scope="row"><label for="require_activation_key"><?php _e('Require Account Activation', 'pcl'); ?></label></th>
				<td>
					<input name="pcl_settings[require_activation_key]" type="checkbox" id="require_activation_key" value="1" <?php checked(pcl_require_activation_key(), 1); ?>>
					<p class="description" id="rquire_activation_key_description"><?php _e('If checked, users would receive an email to activate their account before they can login.', 'pcl'); ?></p></td>
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'pcl'); ?>"></p>
</form>