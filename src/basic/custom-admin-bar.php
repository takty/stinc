<?php
/**
 * Custom Admin Bar
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st\basic;

function remove_wp_logo() {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'wp-logo' );
		},
		300
	);
}

function remove_customize_menu() {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'customize' );
		},
		300
	);
	add_action(
		'admin_menu',
		function () {
			global $submenu;
			if ( isset( $submenu['themes.php'] ) ) {
				$customize_menu_index = -1;
				foreach ( $submenu['themes.php'] as $index => $menu_item ) {
					foreach ( $menu_item as $data ) {
						if ( strpos( $data, 'customize' ) === 0 ) {
							$customize_menu_index = $index;
							break;
						}
					}
					if ( $customize_menu_index !== -1 ) {
						break;
					}
				}
				unset( $submenu['themes.php'][ $customize_menu_index ] );
			}
		}
	);
}

function remove_post_menu_when_empty() {
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
