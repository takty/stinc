<?php
namespace st\basic;
/**
 *
 * Anti-Spam - Disabling Comment and Trackback Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
 *
 */


function disable_comment_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );

	add_filter( 'comments_open', '__return_false' );
	add_filter( 'comments_array', '__return_empty_array' );
	add_filter( 'comment_reply_link', '__return_false' );
	add_filter( 'comments_rewrite_rules', '__return_empty_array' );
}

function disable_comment_menu() {
	$counts = wp_count_comments();
	$sum = 0;
	foreach ( $counts as $key => $val ) $sum += $val;
	if ( 0 < $sum ) return;

	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
	} );
	add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'comments' );
	}, 300 );
}

function disable_comment_feed() {
	remove_theme_support( 'automatic-feed-links' );

	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'post_comments_feed_link_html', '__return_empty_string' );
	add_filter( 'post_comments_feed_link', '__return_empty_string' );
	add_filter( 'feed_link', function ( $output ) {
		if ( strpos( $output, 'comments' ) === false ) return $output;
		return '';
	} );
	remove_action( 'do_feed_rss2', 'do_feed_rss2' );
	remove_action( 'do_feed_atom', 'do_feed_atom' );
	add_action( 'do_feed_rss2', function ( $for_comments ) {
		if ( ! $for_comments ) load_template( ABSPATH . WPINC . '/feed-rss2.php' );
	} );
	add_action( 'do_feed_atom', function ( $for_comments ) {
		if ( ! $for_comments ) load_template( ABSPATH . WPINC . '/feed-atom.php' );
	} );
}

function disable_trackback() {
	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'trackbacks' );

	add_filter( 'pings_open', '__return_false' );
	add_filter( 'site_url', function ( $url, $path, $scheme, $blog_id ) {
		if ( strpos( $path, 'xmlrpc.php' ) === false ) return $url;
		return '';
	}, 10, 4 );
	add_action( 'template_redirect', function () {
		if ( is_trackback() ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	} );
}
