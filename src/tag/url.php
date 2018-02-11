<?php
namespace st;

/**
 *
 * URL Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-01-14
 *
 */


function get_current_url() {
	return ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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
