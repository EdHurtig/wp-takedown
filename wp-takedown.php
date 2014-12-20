<?php
/**
 * Plugin Name: WP Takedown!
 * Plugin URI: http://edhurtig.com/
 * Description: Allows administrators to enable and disable maintenance mode
 * Version: 1.0
 * Author: Eddie Hurtig
 * Author URI: http://hurtigtechnologies.com
 */

include( 'wp-takedown-admin.php' );
/**
 * Enable Maintenance mode from the Network Admin
 */
function hurtigtech_enable_maintenance_mode() {
	if ( ! current_user_can( hurtigtech_get_takedown_cap() ) ) {
		wp_die( 'Cheating Detected... Leave!' );
		die();
	}

	check_admin_referer( 'hurtigtech-enable-maintenance-mode', 'hurtigtech-enable-maintenance-mode-nonce' );

	if ( ! isset( $_REQUEST['timestamp'] ) ) {
		hurtigtech_redirect_error( 'You didn\'t specify a number of minutes you will be down for' );

		return;
	}
	$time = $_REQUEST['timestamp'];

	if ( ( ! is_numeric( $time ) || $time <= 0 ) && 'time()' !== $time ) {
		return;
	}

	if ( is_numeric( $time ) ) {
		$time = time() + $time - 600;
	}
	$stop_key             = wp_create_nonce( 'wp-takedown' );
	$cancel_file_required = isset( $_REQUEST['no-cancel-file-ok'] );
	$cancel_file_exists   = file_exists( ABSPATH . '/wp-maint.php' );
	if ( $cancel_file_required && ! $cancel_file_exists ) {
		wp_die( 'The file <code>' . ABSPATH . '/wp-maint.php</code> does not exist.  This file is responsible for allowing you to end maintenance mode early with the click of a link.  Please install it or <a href="&no-cancel-file-ok">proceed without this functionality</a>' );
	} else {
		if ( file_put_contents( ABSPATH . '/.maintenance', '<?php $upgrading = ' . $time . '; $stop_key = \'' . $stop_key . '\' ?>' ) ) {

			$time_msg = 'Maintenance mode has been activated ';

			if ( is_numeric( $time ) ) {
				$time_msg .= 'Until ' . date( 'F j, Y g:i:s a', $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + 600 );
			} else {
				$time_msg .= 'Indefinitely';
			}
			$footer = '<p>You can always end maintenance mode by deleting the <code>.maintenance</code> file in the website\'s root directory: <code>' . ABSPATH . '</code></p>';
			if ( $cancel_file_exists ) {
				$stop_link = '<a href="' . site_url( 'wp-maint.php?stop_key=' . urlencode( $stop_key ) ) . '"> End Maintenance Period </a>';

				$message = $time_msg . '. <p>Use this link to automatically end the maintenance period. ' . $stop_link . '</p>' . $footer;
			} else {
				$message = $time_msg . $footer;
			}
			wp_die( $message, 'Maintenance Mode Enabled!' );
		} else {
			wp_die( 'Failed to write <code>.maintenance</code> file in the website\'s root directory <code>' . ABSPATH . '</code>', 'Maintenance Mode Failed!' );
		}
	}
}

add_action( 'admin_post_hurtigtech_enable_maintenance_mode', 'hurtigtech_enable_maintenance_mode' );


/**
 * Enable use lockout from admin
 */
function hurtigtech_enable_lockout() {
	// Gotta be the best of the best for this one
	if ( ! is_super_admin() ) {
		wp_die( 'Cheating Detected... Leave!' );
		die();
	}

	check_admin_referer( 'hurtigtech-enable-lockout', 'hurtigtech-enable-lockout-nonce' );

	if ( ! isset( $_REQUEST['timestamp'] ) ) {
		hurtigtech_redirect_error( 'You didn\'t specify a number of minutes to loxckout for' );

		return;
	}
	$time = $_REQUEST['timestamp'];

	if ( ( ! is_numeric( $time ) || $time <= 0 ) && 'time()' !== $time ) {
		return;
	}

	if ( is_numeric( $time ) ) {
		$time = time() + $time - 600;
	}
	$stop_key             = wp_create_nonce( 'wp-takedown' );
	$cancel_file_required = isset( $_REQUEST['no-cancel-file-ok'] );
	$cancel_file_exists   = file_exists( ABSPATH . '/wp-maint.php' );
	if ( $cancel_file_required && ! $cancel_file_exists ) {
		wp_die( 'The file <code>' . ABSPATH . '/wp-maint.php</code> does not exist.  This file is responsible for allowing you to end the lockout early with the click of a link.  Please install it or <a href="&no-cancel-file-ok">proceed without this functionality</a>' );
	} else {
		if ( file_put_contents( ABSPATH . '/.lockout', '<?php $upgrading = ' . $time . '; $stop_key = \'' . $stop_key . '\' ?>' ) ) {

			$time_msg = 'Lockout is currently in effect ';

			if ( is_numeric( $time ) ) {
				$time_msg .= 'Until ' . date( 'F j, Y g:i:s a', $time + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + 600 );
			} else {
				$time_msg .= 'Indefinitely';
			}
			$footer = '<p>You can always this lockout mode by deleting the <code>.lockout</code> file in the website\'s root directory: <code>' . ABSPATH . '</code></p>';
			if ( $cancel_file_exists ) {
				$stop_link = '<a href="' . site_url( 'wp-maint.php?stop_key=' . urlencode( $stop_key ) . '&type=lockout' ) . '"> End Lockout </a>';

				$message = $time_msg . '. <p>Use this link to automatically end the lockout. ' . $stop_link . '</p>' . $footer;
			} else {
				$message = $time_msg . $footer;
			}
			wp_die( $message, 'Lockout Enabled!' );
		} else {
			wp_die( 'Failed to write <code>.lockout</code> file in the website\'s root directory <code>' . ABSPATH . '</code>', 'Lockout Failed!' );
		}
	}
}


function hurtigtech_takedown() {
	if ( ! isset( $_REQUEST['hurtigtech_takedown_confirm'] ) || $_REQUEST['hurtigtech_takedown_confirm'] != 'on' ) {
		hurtigtech_redirect_error( 'You did not confirm that you understand the consequences of a takedown.' );

		return;
	}
	if ( isset( $_REQUEST['hurtigtech_enable_lockout'] ) ) {
		hurtigtech_enable_lockout();
	} elseif ( isset( $_REQUEST['hurtigtech_enable_maintenance_mode'] ) ) {
		hurtigtech_enable_maintenance_mode();
	}
}

add_action( 'admin_post_hurtigtech_takedown', 'hurtigtech_takedown' );


function hurtigtech_handle_lockout() {
	if ( hurtigtech_is_lockout() ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json_error( 'Admin System Locked Out' );
		} else {
			$end = hurtigtech_lockout_end();
			if ( - 1 == $end ) {
				$end = "Please check back shortly.  Administrators are performing maintenance.";
			} else {
				$end = "Please check back in " . ceil( $end / 60 ) . " Minutes";
			}

			wp_die( 'Admin System Locked Out. ' . $end );
		}
		die();
	}
}

add_action( 'authenticate', 'hurtigtech_handle_lockout' );
add_action( 'admin_init', 'hurtigtech_handle_lockout' );

/**
 * Deterimines if User Logins are locked out
 * @return bool
 */
function hurtigtech_is_lockout() {
	if ( file_exists( ABSPATH . '/.lockout' ) ) {
		include ABSPATH . '/.lockout';
		if ( isset( $upgrading ) && $upgrading + 600 > time() ) {
			return true;
		} else {
			unlink( ABSPATH . '/.lockout' );
		}
	}

	return false;
}

function hurtigtech_lockout_end() {
	if ( file_exists( ABSPATH . '/.lockout' ) ) {
		include ABSPATH . '/.lockout';
		if ( isset( $upgrading ) ) {
			if ( $upgrading == time() ) {
				return - 1;
			} else {
				return $upgrading + 600 - time();
			}

		} else {
			return false;
		}
	}

	return false;
}

function hurtigtech_get_takedown_cap() {
	return apply_filters( 'hurtigtech_takedown_required_cap', is_multisite() ? 'manage_network' : 'manage_options' );
}


function hurtigtech_redirect_error( $message = '' ) {
	wp_redirect( admin_url( 'admin.php?page=hurtigtech-takedown-page&message=' . urlencode( $message ) ) );
	die();
}

/**
 * Activation
 */
function hurtigtech_takedown_activation() {
	if ( ! file_exists( ABSPATH . '/wp-maint.php' ) ) {
		add_site_option( 'hurtigtech_takedown_cancel_file_hash', md5( file_get_contents( plugin_dir_path( __FILE__ ) . 'wp-maint.php' ) ) );
		copy( plugin_dir_path( __FILE__ ) . 'wp-maint.php', ABSPATH . '/wp-maint.php' );
	} else {
		// error... already exists
	}
}

register_activation_hook( __FILE__, 'hurtigtech_takedown_activation' );

/**
 * Deactivation
 */
function hurtigtech_takedown_deactivation() {
	$maint_file = ABSPATH . '/wp-maint.php';
	if ( file_exists( $maint_file ) ) {
		$hash = get_site_option( 'hurtigtech_takedown_cancel_file_hash' );

		if ( md5( file_get_contents( $maint_file ) ) == $hash ) {
			unlink( ABSPATH . '/wp-maint.php' );
			if ( ! file_exists( $maint_file ) ) {
				delete_site_option( 'hurtigtech_takedown_cancel_file_hash' );

				return;
			}

		}
	}

	//TODO: cancel deactivation

}

register_deactivation_hook( __FILE__, 'hurtigtech_takedown_deactivation' );