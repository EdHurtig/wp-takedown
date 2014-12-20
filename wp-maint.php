<?php
/**
 * Manual Maintenance Mode Disable File.  When Maintenance Mode is Enabled by
 * the WP Takedown! plugin, the administrator is given a page that contains
 * link to this page with a secret hash code that will immediately end the
 * takedown action that they took
 *
 * @author  Eddie Hurtig <hurtige@sudbury.ma.us>
 * @since   2014-12-20
 */

$types     = array( 'maintenance', 'lockout' );
$type      = 'maintenance';
$wp_loader = './wp-load.php';


if ( isset( $_REQUEST['type'] ) && in_array( $_REQUEST['type'], $types ) ) {
	$type = $_REQUEST['type'];
}2

$file = "./.$type";


if ( file_exists( $file ) && is_readable( $file ) && is_writable( $file ) ) {
	include $file;

	if ( isset( $stop_key ) && isset( $_REQUEST['stop_key'] ) && $stop_key === $_REQUEST['stop_key'] ) {


		unlink( $file );
		if ( file_exists( $wp_loader ) && is_readable( $wp_loader ) ) {
			include $wp_loader;
			$link_back = network_admin_url( 'admin.php?page=hurtigtech-takedown-page' );


			wp_die( ucfirst( $type ) . ' Period has been ended.  Please <a href="' . $link_back . '">Click Here</a> to return to the Admin' );
		} else {
			die ( ucfirst( $type ) . ' Period has been ended.  Please return to the Admin' );
		}
	} else {
		die ( "Invalid Key" );
	}
} else {
	die( ucfirst( $type ) . ' Mode Not Active' );
}