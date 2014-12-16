<?php

/**
 * Registers the hurtigtech Plugin Settings Page in the Wordpress Network Admin
 */
function hurtigtech_register_takedown_page() {
	$cap = hurtigtech_get_takedown_cap();

	if ( current_user_can( $cap ) ) {
		add_menu_page( 'TakeDown!', 'TakeDown!', $cap, 'hurtigtech-takedown-page', 'hurtigtech_takedown_page', 'dashicons-hammer', 165 );
	}
}

add_action( 'network_admin_menu', 'hurtigtech_register_takedown_page' );
add_action( 'admin_menu', 'hurtigtech_register_takedown_page' );

function wp_takedown_admin() {
	?>
	<div class="hurtigtech-setting-section">
		<h2>Takedown!</h2>
		<?php if ( isset( $_REQUEST['message'] ) ) : ?>
			<div class="message error">
				<p><?php echo wp_kses_post( $_REQUEST['message'] ); ?></p>
			</div>
		<?php endif; ?>
		<p>Shutdown the site for a specified amount of time. <b>This locks out everyone, including YOU!</b><br>
			<i>You will be brought to a page where you can disable maintenance mode early,
				<b>do not leave this page!</b></i>
		</p>


		<p>
			<b>Maintenance Mode</b> Disables the entire site and displays a maintenance page. No interaction will happen with the database, plugins, or theme during this time<br>
			<b>Lockout</b> Disables any admin actions from being performed, i.e. Publishing Posts or Logging In, but keeps the front end up. This is good for maintaining uptime<br>
		</p>

		<form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php wp_nonce_field( 'hurtigtech-enable-maintenance-mode', 'hurtigtech-enable-maintenance-mode-nonce' ); ?>
			<?php wp_nonce_field( 'hurtigtech-enable-lockout', 'hurtigtech-enable-lockout-nonce' ); ?>
			<p><b>1. Duration</b></p>
			<input type="hidden" name="action" value="hurtigtech_takedown" />
			<input type="radio" name="timestamp" value="60">1 minute<br>
			<input type="radio" name="timestamp" value="300">5 minutes<br>
			<input type="radio" name="timestamp" value="600">10 minutes<br>
			<input type="radio" name="timestamp" value="1800">30 minutes<br>
			<input type="radio" name="timestamp" value="time()">Indefinitely<br>

			<p><b>2. Confirm</b></p>

			<p>

				<label>
					<input type="checkbox" name="hurtigtech_takedown_confirm">
					<b>STOP!</b> This WILL incur some downtime on some or all components of your site! Check this box indicate that you understand.
				</label>
			</p>

			<p><b>3. Take Down</b></p>

			<p>
				<input type="submit" name="hurtigtech_enable_maintenance_mode" class="button-primary hurtigtech-button-danger" value="Enable Maintenance mode" />
				<input type="submit" name="hurtigtech_enable_lockout" class="button-primary hurtigtech-button-warning" value="Lockout Users" />
			</p>
		</form>

		<div class="clear"></div>
	</div>
<?php
}