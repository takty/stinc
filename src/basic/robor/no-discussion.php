<?php
/**
 * No Discussion - Disabling Comment and Trackback Functions
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2022-01-08
 */

namespace wpinc\robor;

/**
 * Disable comment supports.
 */
function disable_comment_support() {
	update_option( 'default_comment_status', 0 );

	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );

	add_filter( 'comments_open', '__return_false' );
	add_filter( 'comments_array', '__return_empty_array' );
	add_filter( 'comment_reply_link', '__return_false' );
	add_filter( 'comments_rewrite_rules', '__return_empty_array' );

	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( false !== strpos( $rewrite, 'cpage=' ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}

/**
 * Disable comment feeds.
 */
function disable_comment_feed() {
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'post_comments_feed_link_html', '__return_empty_string' );
	add_filter( 'post_comments_feed_link', '__return_empty_string' );
	add_filter(
		'feed_link',
		function ( $output ) {
			if ( false === strpos( $output, 'comments' ) ) {
				return $output;
			}
			return '';
		}
	);
	remove_action( 'do_feed_rss2', 'do_feed_rss2' );
	remove_action( 'do_feed_atom', 'do_feed_atom' );
	add_action(
		'do_feed_rss2',
		function ( $for_comments ) {
			if ( ! $for_comments ) {
				load_template( ABSPATH . WPINC . '/feed-rss2.php' );
			}
		}
	);
	add_action(
		'do_feed_atom',
		function ( $for_comments ) {
			if ( ! $for_comments ) {
				load_template( ABSPATH . WPINC . '/feed-atom.php' );
			}
		}
	);
}

/**
 * Disable comment menus.
 */
function disable_comment_menu() {
	if ( 0 < array_sum( (array) wp_count_comments() ) ) {
		return;
	}
	add_action(
		'admin_menu',
		function () {
			remove_menu_page( 'edit-comments.php' );
		}
	);
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'comments' );
		},
		300
	);
}


// -----------------------------------------------------------------------------


/**
 * Disable pingback function.
 */
function disable_pingback() {
	update_option( 'default_pingback_flag', 0 );
	update_option( 'default_ping_status', 0 );

	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'trackbacks' );

	remove_action( 'do_all_pings', 'do_all_pingbacks', 10, 0 );

	add_filter( 'pings_open', '__return_false' );
	add_filter( 'pingback_ping_source_uri', '__return_empty_string' );

	add_filter(
		'site_url',
		function ( $url, $path, $scheme, $blog_id ) {
			return 'xmlrpc.php' === $path ? '' : $url;
		},
		10,
		4
	);
	add_filter(
		'xmlrpc_methods',
		function ( $ms ) {
			unset( $ms['pingback.ping'] );
			unset( $ms['pingback.extensions.getPingbacks'] );
			return $ms;
		}
	);
	add_action(
		'wp',
		function () {
			header_remove( 'X-Pingback' );
		},
		9999
	);
}

/**
 * Disable trackback function.
 */
function disable_trackback() {
	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'trackbacks' );

	remove_action( 'do_all_pings', 'do_all_trackbacks', 10, 0 );

	add_action(
		'template_redirect',
		function () {
			if ( is_trackback() ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}
	);
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( false !== strpos( $rewrite, 'tb=1' ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}
