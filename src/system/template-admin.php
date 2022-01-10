<?php
/**
 * Template Admin
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-01-10
 */

namespace st\template_admin;

/**
 * Initializes template admin.
 *
 * @param string $function_name Function name for admin.
 */
function initialize( string $function_name = 'setup_template_admin' ) {
	$post_fixes = array( '--admin.php', '_admin.php' );

	add_action(
		'admin_menu',
		function () use ( $post_fixes, $function_name ) {
			$post_id = get_post_id();

			$pt = get_post_meta( $post_id, '_wp_page_template', true );
			if ( ! empty( $pt ) && 'default' !== $pt ) {
				foreach ( $post_fixes as $post_fix ) {
					if ( _load_page_template_admin( $post_id, $pt, $post_fix, $function_name ) ) {
						return;
					}
				}
			}
			if ( is_page_on_front( $post_id ) ) {
				foreach ( $post_fixes as $post_fix ) {
					if ( _load_page_template_admin( $post_id, 'front-page.php', $post_fix, $function_name ) ) {
						return;
					}
				}
			}
			$post_type = get_post_type_in_admin( $post_id );
			if ( ! empty( $post_type ) ) {
				foreach ( $post_fixes as $post_fix ) {
					if ( _load_page_template_admin( $post_id, $post_type . '.php', $post_fix, $function_name ) ) {
						return;
					}
				}
			}
		}
	);
}

/**
 * Loads page template admin.
 *
 * @access private
 *
 * @param mixed  $post_id       The post ID.
 * @param string $path          The path to the page template admin.
 * @param string $post_fix      Postfix of the file name.
 * @param string $function_name Function name for admin.
 */
function _load_page_template_admin( $post_id, string $path, string $post_fix, string $function_name ) {
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


// -----------------------------------------------------------------------------


function get_post_id() {
	$post_id = '';
	if ( isset( $_GET['post'] ) || isset( $_POST['post_ID'] ) ) {
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];
	}
	return (int) $post_id;
}

function get_post_type_in_admin( $post_id ) {
	$p = get_post( $post_id );
	if ( null === $p ) {
		if ( isset( $_GET['post_type'] ) ) {
			return $_GET['post_type'];
		}
		return '';
	}
	return $p->post_type;
}

function is_page_on_front( $post_id ) {
	$pof = get_option( 'page_on_front' );
	if ( 'page' === get_option( 'show_on_front' ) && $pof && $post_id === (int) $pof ) {
		return true;
	}
	return false;
}
