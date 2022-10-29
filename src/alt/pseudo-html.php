<?php
/**
 * Pseudo HTML.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2022-10-28
 */

namespace wpinc\alt;

require_once __DIR__ . '/assets/url.php';

/**
 * Enables pseudo HTML.
 */
function enable_pseudo_html() {
	global $wp_rewrite;
	$wp_rewrite->use_trailing_slashes = false;
	$wp_rewrite->page_structure       = $wp_rewrite->root . '%pagename%.html';

	add_filter(
		'home_url',
		function ( $url, $path, $orig_scheme, $blog_id ) {
			if ( empty( $path ) || '/' === $path ) {
				return $url;
			}
			$pu = wp_parse_url( $url );
			if ( ! isset( $pu['path'] ) ) {
				return $url;
			}
			$p = get_page_by_path( $path );
			if ( null === $p ) {
				return $url;
			}
			$path = rtrim( $pu['path'], '/' );
			if ( substr( $path, - strlen( '.html' ) ) !== '.html' ) {
				$pu['path'] = "$path.html";
			}
			return \wpinc\serialize_url( $pu );
		},
		10,
		4
	);
}
