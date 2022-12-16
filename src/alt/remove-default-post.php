<?php
/**
 * Remove Default 'post' Post Type.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2022-01-16
 */

namespace wpinc\alt;

/**
 * Remove UIs for default 'post' post type.
 */
function remove_default_post_ui(): void {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_node( 'new-post' );
		}
	);
	add_action(
		'wp_dashboard_setup',
		function () {
			remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Remove default 'post' post type when no posts.
 */
function remove_default_post_when_empty(): void {
	$counts = wp_count_posts();
	$sum    = 0;
	foreach ( $counts as $key => $val ) {
		if ( 'auto-draft' === $key ) {
			continue;
		}
		$sum += $val;
	}
	if ( 0 === $sum ) {
		_hide_post_type_post();
		_hide_taxonomy( 'category' );
		_hide_taxonomy( 'post_tag' );
	}
}

/**
 * Hide default 'post' post type.
 *
 * @access private
 */
function _hide_post_type_post(): void {
	unregister_taxonomy_for_object_type( 'category', 'post' );
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	global $wp_post_types;
	$wp_post_types['post']->public             = false;
	$wp_post_types['post']->publicly_queryable = false;
	$wp_post_types['post']->show_in_admin_bar  = false;
	$wp_post_types['post']->show_in_menu       = false;
	$wp_post_types['post']->show_in_nav_menus  = false;
	$wp_post_types['post']->show_in_rest       = false;
	$wp_post_types['post']->show_ui            = false;
}

/**
 * Hide a taxonomy.
 *
 * @access private
 *
 * @param string $tx Taxonomy.
 */
function _hide_taxonomy( $tx ): void {
	$tx = get_taxonomy( $tx );
	if ( ! empty( $tx->object_type ) ) {
		return;
	}
	$tx->public             = false;
	$tx->publicly_queryable = false;
	$tx->show_admin_column  = false;
	$tx->show_in_menu       = false;
	$tx->show_in_nav_menus  = false;
	$tx->show_in_quick_edit = false;
	$tx->show_in_rest       = false;
	$tx->show_tagcloud      = false;
	$tx->show_ui            = false;
}
