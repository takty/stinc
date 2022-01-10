<?php
/**
 * Remove Default 'post' Post Type.
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2022-01-08
 */

namespace wpinc\robor;

/**
 * Remove UIs for default 'post' post type.
 */
function remove_default_post_ui() {
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
function remove_default_post_when_empty() {
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
function _hide_post_type_post() {
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
 * @param string $tax Taxonomy.
 */
function _hide_taxonomy( $tax ) {
	$tax = get_taxonomy( $tax );
	if ( ! empty( $tax->object_type ) ) {
		return;
	}
	$tax->public             = false;
	$tax->publicly_queryable = false;
	$tax->show_admin_column  = false;
	$tax->show_in_menu       = false;
	$tax->show_in_nav_menus  = false;
	$tax->show_in_quick_edit = false;
	$tax->show_in_rest       = false;
	$tax->show_tagcloud      = false;
	$tax->show_ui            = false;
}
