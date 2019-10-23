<?php
namespace st\template_admin;
/**
 *
 * Template Admin
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-23
 *
 */


require_once __DIR__ . '/misc.php';


function initialize() {
	$POST_FIX = '_admin.php';

	add_action( 'admin_menu', function () use ( $POST_FIX ) {
		$post_id = \st\get_post_id();

		$pt = get_post_meta( $post_id, '_wp_page_template', TRUE );
		if ( ! empty( $pt ) && $pt !== 'default' ) {
			if ( _load_page_template_admin( $post_id, $pt, $POST_FIX ) ) return;
		}
		if ( \st\is_page_on_front( $post_id ) ) {
			if ( _load_page_template_admin( $post_id, 'front-page.php', $POST_FIX ) ) return;
		}
		$post_type = \st\get_post_type_in_admin( $post_id );
		if ( ! empty( $post_type ) ) {
			if ( _load_page_template_admin( $post_id, $post_type . '.php', $POST_FIX ) ) return;
		}
	} );
}

function _load_page_template_admin( $post_id, $path, $post_fix ) {
	$path = str_replace( '.php', $post_fix, $path );
	$path = get_parent_theme_file_path( $path );
	if ( file_exists( $path ) ) {
		require_once $path;
		if ( function_exists( 'setup_template_admin' ) ) {
			setup_page_template_admin( $post_id );
			return true;
		}
	}
	return false;
}
