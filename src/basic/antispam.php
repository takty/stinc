<?php
namespace st\basic;

/**
 *
 * Anti-Spam - Disable Comment and Trackback Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-09-10
 *
 * Usage:
 *  require_once get_parent_theme_file_path( '/lib/stinc/basic/antispam.php' );
 *  add_action( 'after_setup_theme', 'st\basic\disable_spam' );
 *
 */


function disable_spam() {
	disable_comment();
	disable_comment_feed();
	disable_trackback();
}

function disable_comment() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );

	add_filter( 'comments_open', '__return_false' );
	add_filter( 'comments_array', '__return_empty_array' );
	add_filter( 'comment_reply_link', '__return_false' );
	add_filter( 'comments_rewrite_rules', '__return_empty_array' );

	$counts = wp_count_comments();
	$sum = 0;
	foreach ( $counts as $key => $val ) {
		$sum += $val;
	}
	if ( $sum === 0 ) {
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit-comments.php' );
		} );
		add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'comments' );
		}, 300 );
	}
}

function disable_comment_feed() {
	remove_theme_support( 'automatic-feed-links' );

	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'post_comments_feed_link_html', '__return_empty_string' );
	add_filter( 'post_comments_feed_link', '__return_empty_string' );
	add_filter( 'feed_link', function ( $output ) {
		return ( false === strpos( $output, 'comments' ) ) ? $output : '';
	} );
	remove_action( 'do_feed_rss2', 'do_feed_rss2' );
	remove_action( 'do_feed_atom', 'do_feed_atom' );
	add_action( 'do_feed_rss2', function ( $for_comments ) {
		if ( !$for_comments ) load_template( ABSPATH . WPINC . '/feed-rss2.php' );
	} );
	add_action( 'do_feed_atom', function ( $for_comments ) {
		if ( !$for_comments ) load_template( ABSPATH . WPINC . '/feed-atom.php' );
	} );
}

function disable_trackback() {
	remove_post_type_support( 'post', 'trackbacks' );
	remove_post_type_support( 'page', 'trackbacks' );

	add_filter( 'pings_open', '__return_false' );
	add_filter( 'site_url', function ( $url, $path, $scheme, $blog_id ) {
		return ( false === strpos( $path, 'xmlrpc.php' ) ) ? $url : '';
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
