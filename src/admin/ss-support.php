<?php
namespace st;
/**
 *
 * Simpley Static Support (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-01-31
 *
 */


if ( is_admin() && ! function_exists( '\st\check_simply_static_active' ) ) {
	function check_simply_static_active() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$is_active = false;
		$ps = get_plugins();
		foreach ( $ps as $path => $plugin ) {
			if ( is_plugin_active( $path ) && $plugin['Name'] === 'Simply Static' ) {
				$is_active = true;
				break;
			}
		}
		update_option( 'is_simply_static_active', $is_active );
	}
	add_action( 'init', '\st\check_simply_static_active' );
}
