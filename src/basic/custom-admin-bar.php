<?php
namespace st\basic;
/**
 *
 * Custom Admin Bar
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-11-18
 *
 */


function remove_wp_logo() {
	add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'wp-logo' );
	}, 300 );
}

function remove_customize_menu() {
	add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'customize' );
	}, 300 );

	add_action( 'admin_menu', function () {
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
				if ( $customize_menu_index !== -1 ) break;
			}
			unset( $submenu['themes.php'][ $customize_menu_index ] );
		}
	} );
}

function remove_post_menu_when_empty() {
	$counts = wp_count_posts();
	$sum = 0;
	foreach ( $counts as $key => $val ) {
		if ( $key === 'auto-draft' ) continue;
		$sum += $val;
	}
	if ( $sum === 0 ) _remove_post_type_post();
}

function _remove_post_type_post() {
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
