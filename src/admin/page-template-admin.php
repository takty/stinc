<?php
namespace st\page_template_admin;

/**
 *
 * Page Template Admin
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-06
 *
 */


function initialize() {
	$POST_FIX = '_admin.php';

	add_action( 'admin_menu', function () use ( $POST_FIX ) {
		$post_id = get_post_id();

		$pt = get_post_meta( $post_id, '_wp_page_template', TRUE );
		if ( ! empty( $pt ) && $pt !== 'default' ) {
			if ( load_page_template_admin( $post_id, $pt, $POST_FIX ) ) return;
		}
		if ( is_front_page( $post_id ) ) {
			if ( load_page_template_admin( $post_id, 'front-page.php', $POST_FIX ) ) return;
		}
		$post_type = get_post_type( $post_id );
		if ( ! empty( $post_type ) ) {
			if ( load_page_template_admin( $post_id, $post_type . '.php', $POST_FIX ) ) return;
		}
	} );
}

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

function is_front_page( $post_id ) {
	$pof = get_option( 'page_on_front' );
	if ( 'page' == get_option( 'show_on_front') && $pof && $post_id === intval( $pof ) ) {
		return true;
	}
	return false;
}

function load_page_template_admin( $post_id, $path, $post_fix ) {
	$path = str_replace( '.php', $post_fix, $path );
	$path = get_parent_theme_file_path( $path );
	if ( file_exists( $path ) ) {
		require_once $path;
		if ( function_exists( 'setup_page_template_admin' ) ) {
			setup_page_template_admin( $post_id );
			return true;
		}
	}
	return false;
}
