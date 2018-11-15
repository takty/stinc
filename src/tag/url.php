<?php
namespace st;

/**
 *
 * URL Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-15
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

	if ( defined( 'THEME_PATH' ) ) {
		$theme_path = wp_normalize_path( THEME_PATH );
	} else {
		$theme_path = wp_normalize_path( get_theme_file_path() );
	}
	$theme_uri = get_theme_file_uri();

	return str_replace( $theme_path, $theme_uri, $path );
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
