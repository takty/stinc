<?php
namespace st;

/**
 *
 * URL Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-06-08
 *
 */


function get_current_uri( $raw = false ) {
	$host = get_server_host();
	if ( $raw && isset( $_SERVER['REQUEST_URI_ORIG'] ) ) {
		return ( is_ssl() ? 'https://' : 'http://' ) . $host . $_SERVER['REQUEST_URI_ORIG'];
	}
	return ( is_ssl() ? 'https://' : 'http://' ) . $host . $_SERVER['REQUEST_URI'];
}

function get_server_host() {
	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {  // When reverse proxy exists
		return $_SERVER['HTTP_X_FORWARDED_HOST'];
	}
	return $_SERVER['HTTP_HOST'];
}

function get_file_uri( $path ) {
	$path = wp_normalize_path( $path );

	if ( is_child_theme() ) {
		$theme_path = wp_normalize_path( defined( 'CHILD_THEME_PATH' ) ? CHILD_THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();

		// When child theme is used, and libraries exist in the parent theme
		$tlen = strlen( $theme_path );
		$len  = strlen( $path );
		if ( $tlen >= $len || 0 !== strncmp( $theme_path . $path[ $tlen ], $path, $tlen + 1 ) ) {
			$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_template_directory() );
			$theme_uri  = get_template_directory_uri();
		}
		return str_replace( $theme_path, $theme_uri, $path );
	} else {
		$theme_path = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_stylesheet_directory() );
		$theme_uri  = get_stylesheet_directory_uri();
		return str_replace( $theme_path, $theme_uri, $path );
	}
}

function get_first_slug( $url ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$hu = \st\Multilang::get_instance()->home_url( '/' );
	} else {
		$hu = home_url( '/' );
	}
	$temp = str_replace( $hu, '', $url );
	$ps = explode( '/', $temp );
	if ( count( $ps ) > 0 ) return $ps[0];
	return '';
}

function get_first_and_second_slug( $url ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$hu = \st\Multilang::get_instance()->home_url( '/' );
	} else {
		$hu = home_url( '/' );
	}
	$temp = str_replace( $hu, '', $url );
	$ps = explode( '/', $temp );
	$ss = ['', ''];
	if ( count( $ps ) > 0 ) $ss[0] = $ps[0];
	if ( count( $ps ) > 1 ) $ss[1] = $ps[1];
	return $ss;
}


// -----------------------------------------------------------------------------


function abs_url( $base, $rel ) {
	if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) return $rel;
	$base = trailingslashit( $base );
	if ( $rel[0] === '#' || $rel[0] === '?' ) return $base . $rel;
	extract( parse_url( $base ) );
	$path = preg_replace( '#/[^/]*$#', '', $path );
	if ( $rel[0] === '/' ) $path = '';
	$abs = "$host$path/$rel";
	$re = [ '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' ];
	for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, -1, $n ) ) {}
	return $scheme . '://' . $abs;
}
