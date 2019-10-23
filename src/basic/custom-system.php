<?php
namespace st\basic;
/**
 *
 * Custom System
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-23
 *
 */


function disable_embedded_sticky_post_behavior() {
	add_action( 'pre_get_posts', function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) return;
		$query->set( 'ignore_sticky_posts', '1' );  // Only for embedded 'post' type
	} );
}

function disable_emoji() {
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
}

function enable_used_tags() {
	global $allowedtags;
	$allowedtags['sub']  = [];
	$allowedtags['sup']  = [];
	$allowedtags['span'] = [];
}

function enable_default_image_sizes( $add_medium_small = true ) {
	add_image_size( 'small', 320, 9999 );
	add_image_size( 'huge', 2560, 9999 );
	if ( $add_medium_small ) add_image_size( 'medium-small', 480, 9999 );

	add_filter( 'image_size_names_choose', function ( $sizes ) use ( $add_medium_small ) {
		$is_ja = preg_match( '/^ja/', get_locale() );
		$ns = [];
		foreach ( $sizes as $idx => $s ) {
			$ns[ $idx ] = $s;
			if ( $idx === 'thumbnail' ) {
				$ns[ 'small' ] = ( $is_ja ? '小' : 'Small' );
				if ( $add_medium_small ) $ns[ 'medium-small' ] = ( $is_ja ? 'やや小' : 'Medium Small' );
			}
			if ( $idx === 'medium' ) $ns[ 'medium_large' ] = ( $is_ja ? 'やや大' : 'Medium Large' );
		}
		return $ns;
	} );
}

function enable_to_add_timestamp_to_src() {
	add_filter( 'style_loader_src', '\st\basic\_cb_loader_src_timestamp' );
	add_filter( 'script_loader_src', '\st\basic\_cb_loader_src_timestamp' );
}

function _cb_loader_src_timestamp( $src ) {
	if ( strpos( $src, get_template_directory_uri() ) === false ) return $src;

	$removed_src = strtok( $src, '?' );
	$path = wp_normalize_path( ABSPATH );
	$resource_file = str_replace(  trailingslashit( site_url() ), trailingslashit( $path ), $removed_src );
	$resource_file = realpath( $resource_file );
	$src = add_query_arg( 'fver', date( 'Ymdhis', filemtime( $resource_file ) ), $src );
	return $src;
}
