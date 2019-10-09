<?php
namespace st;
/**
 *
 * Utilities for Admin
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
 *
 */


function get_post_id() {
	$post_id = '';
	if ( isset( $_GET['post'] ) || isset( $_POST['post_ID'] ) ) {
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];
	}
	return intval( $post_id );
}

function get_post_type( $post_id ) {
	$p = get_post( $post_id );
	if ( $p === null ) {
		if ( isset( $_GET['post_type'] ) ) return $_GET['post_type'];
		return '';
	}
	return $p->post_type;
}

function is_post_type( $post_type ) {
	$post_id = get_post_id();
	$pt = get_post_type( $post_id );
	return $post_type === $pt;
}

function is_page_on_front( $post_id ) {
	$pof = get_option( 'page_on_front' );
	if ( 'page' === get_option( 'show_on_front') && $pof && $post_id === intval( $pof ) ) {
		return true;
	}
	return false;
}


// -----------------------------------------------------------------------------


function extract_media_id( $url ) {
	$ud = wp_upload_dir();
	$upload_url = $ud['baseurl'];
	if ( strpos( $url, $upload_url ) !== 0 ) return false;

	$id_url = _search_media_id( $url, $upload_url );
	if ( $id_url !== false ) return $id_url;

	$full_url = preg_replace( '/(-[0-9]+x[0-9]+)(\.[^.]+){0,1}$/i', '${2}', $url );
	if ( $url === $full_url ) return false;
	return _search_media_id( $full_url, $upload_url );
}

function _search_media_id( $url, $upload_url ) {
	global $wpdb;

	$attached_file = str_replace( $upload_url . '/', '', $url );
	$id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attached_file' AND meta_value='%s' LIMIT 1;",
		$attached_file
	) );
	if ( $id === 0 ) return false;
	return [ 'id' => $id, 'url' => $url ];
}
