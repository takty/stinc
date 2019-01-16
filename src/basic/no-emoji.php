<?php
namespace st\basic;

/**
 *
 * No Emoji
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-16
 *
 * Usage:
 *  require_once get_parent_theme_file_path( '/lib/stinc/basic/no-emoji.php' );
 *  add_action( 'after_setup_theme', '\st\basic\disable_emoji' );
 *
 */


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
