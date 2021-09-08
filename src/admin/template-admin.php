<?php
namespace st\template_admin;
/**
 *
 * Template Admin
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-09-08
 *
 */


require_once __DIR__ . '/misc.php';


function initialize( $function_name = 'setup_template_admin' ) {
	$post_fixes = array( '--admin.php', '_admin.php' );

	add_action( 'admin_menu', function () use ( $post_fixes, $function_name ) {
		$post_id = \st\get_post_id();

		$pt = get_post_meta( $post_id, '_wp_page_template', TRUE );
		if ( ! empty( $pt ) && $pt !== 'default' ) {
			foreach ( $post_fixes as $post_fix ) {
				if ( _load_page_template_admin( $post_id, $pt, $post_fix, $function_name ) ) {
					return;
				}
			}
		}
		if ( \st\is_page_on_front( $post_id ) ) {
			foreach ( $post_fixes as $post_fix ) {
				if ( _load_page_template_admin( $post_id, 'front-page.php', $post_fix, $function_name ) ) {
					return;
				}
			}
		}
		$post_type = \st\get_post_type_in_admin( $post_id );
		if ( ! empty( $post_type ) ) {
			foreach ( $post_fixes as $post_fix ) {
				if ( _load_page_template_admin( $post_id, $post_type . '.php', $post_fix, $function_name ) ) {
					return;
				}
			}
		}
	} );
}

function _load_page_template_admin( $post_id, $path, $post_fix, $function_name ) {
	$path = str_replace( '.php', $post_fix, $path );
	$path = get_parent_theme_file_path( $path );
	if ( file_exists( $path ) ) {
		require_once $path;
		if ( function_exists( $function_name ) ) {
			$function_name( $post_id );
			return true;
		}
	}
	return false;
}
