<?php
/**
 * Pseudo HTML.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\alt;

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
			return _serialize_url( $pu );
		},
		10,
		4
	);
}

/**
 * Serializes URL.
 *
 * @param array $pu Parsed URL.
 * @return string URL.
 */
function _serialize_url( array $pu ): string {
	// phpcs:disable
	$scheme = isset( $pu['scheme'] )   ? $pu['scheme'] . '://' : '';
	$host   = isset( $pu['host'] )     ? $pu['host']           : '';
	$port   = isset( $pu['port'] )     ? ':' . $pu['port']     : '';
	$user   = isset( $pu['user'] )     ? $pu['user']           : '';
	$pass   = isset( $pu['pass'] )     ? ':' . $pu['pass']     : '';
	$pass   = ( $user || $pass )       ? "$pass@"              : '';
	$path   = isset( $pu['path'] )     ? $pu['path']           : '';
	$query  = isset( $pu['query'] )    ? '?' . $pu['query']    : '';
	$frag   = isset( $pu['fragment'] ) ? '#' . $pu['fragment'] : '';
	// phpcs:enable
	return "$scheme$user$pass$host$port$path$query$frag";
}
