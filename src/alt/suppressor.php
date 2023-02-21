<?php
/**
 * Suppressor - Suppressing Unnecessary Outputs.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2023-02-21
 */

namespace wpinc\alt;

/**
 * Suppress output for head meta.
 *
 * @param bool $do_remove_feed_link (Optional) Whether feed links are removed.
 */
function suppress_head_meta_output( bool $do_remove_feed_link = true ): void {
	if ( $do_remove_feed_link ) {
		add_filter( 'feed_links_show_posts_feed', '__return_false' );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
}

/**
 * Suppress the output of feed generators.
 */
function suppress_feed_generator_output(): void {
	$as = array(
		'rss2_head',
		'commentsrss2_head',
		'rss_head',
		'rdf_header',
		'atom_head',
		'comments_atom_head',
		'opml_head',
		'app_head',
	);
	foreach ( $as as $a ) {
		remove_action( $a, 'the_generator' );
	}
}

/**
 * Suppress the function for emoji.
 */
function suppress_emoji_function(): void {
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'embed_head', 'print_emoji_detection_script' );
}


// -----------------------------------------------------------------------------


/**
 * Suppress the output of versions for scripts and styles.
 */
function suppress_version_output(): void {
	add_action( 'wp_default_scripts', '\wpinc\alt\_cb_wp_default__suppress_version_output' );
	add_action( 'wp_default_styles', '\wpinc\alt\_cb_wp_default__suppress_version_output' );
	add_filter( 'style_loader_src', '\wpinc\alt\_cb_loader_src__suppress_version_output' );
	add_filter( 'script_loader_src', '\wpinc\alt\_cb_loader_src__suppress_version_output' );
}

/**
 * Callback function for 'wp_default_*' actions.
 *
 * @access private
 *
 * @param object $inst WP_Scripts instance (passed by reference).
 */
function _cb_wp_default__suppress_version_output( object $inst ): void {
	$inst->default_version = '';
}

/**
 * Callback function for '*_loader_src' filters.
 *
 * @access private
 *
 * @param string|null $src The source URL of the enqueued style.
 * @return string|null Source.
 */
function _cb_loader_src__suppress_version_output( ?string $src ): ?string {
	if ( false !== strpos( $src, 'ver=' ) ) {
		return remove_query_arg( 'ver', $src );
	}
	return $src;
}


// -----------------------------------------------------------------------------


/**
 * Suppress the output of log in/out link.
 */
function suppress_loginout_link_output(): void {
	add_filter( 'loginout', '__return_empty_string' );
}

/**
 * Suppress robots.txt output.
 */
function suppress_robots_txt_output(): void {
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( preg_match( '/robots\\.txt\$/', $rule ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}
