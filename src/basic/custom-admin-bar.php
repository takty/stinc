<?php
namespace st\basic;
/**
 *
 * Custom Admin Bar
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
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
	if ( $sum === 0 ) {
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit.php' );
		} );
		add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'new-post' );
		}, 100 );
		add_action( 'admin_enqueue_scripts', function () {
			echo '<style>#wp-admin-bar-new-content > a {pointer-events:none;user-select:none;}</style>';
		} );
		if ( is_user_logged_in() && ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', function () {
				echo '<style>#wp-admin-bar-new-content > a {pointer-events:none;user-select:none;}</style>';
			} );
		}
	}
}
