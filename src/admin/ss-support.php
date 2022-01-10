<?php
/**
 * Simply Static Support (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-01-08
 */

namespace st;

if ( is_admin() && ! function_exists( '\st\check_simply_static_active' ) ) {
	function check_simply_static_active() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$is_active = false;
		$ps        = get_plugins();

		foreach ( $ps as $path => $plugin ) {
			if ( is_plugin_active( $path ) && 'Simply Static' === $plugin['Name'] ) {
				$is_active = true;
				break;
			}
		}
		update_option( 'is_simply_static_active', $is_active );
	}
	add_action( 'init', '\st\check_simply_static_active' );
}


// -----------------------------------------------------------------------------


function add_html_to_page_url() {
	global $wp_rewrite;
	$wp_rewrite->use_trailing_slashes = false;
	$wp_rewrite->page_structure       = $wp_rewrite->root . '%pagename%.html';

	add_filter(
		'home_url',
		function ( $url, $path, $orig_scheme, $blog_id ) {
			if ( empty( $path ) || '/' === $path ) {
				return $url;
			}
			$pu = parse_url( $url );
			if ( ! isset( $pu['path'] ) ) {
				return $url;
			}
			$p = get_page_by_path( $path );
			if ( $p === null ) {
				return $url;
			}
			$path = rtrim( $pu['path'], '/' );
			if ( substr( $path, - strlen( '.html' ) ) !== '.html' ) {
				$pu['path'] = "$path.html";
			}
			return \st\serialize_url( $pu );
		},
		10,
		4
	);
}
