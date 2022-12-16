<?php
/**
 * Customizing Admin Bar and Side Menu.
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2022-01-31
 */

namespace wpinc\alt;

/**
 * Removes WordPress logo icon.
 */
function remove_wp_logo(): void {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'wp-logo' );
		},
		300
	);
}

/**
 * Removes the customize menu.
 */
function remove_customize_menu(): void {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'customize' );
		},
		300
	);
	add_action( 'admin_menu', '\wpinc\alt\_cb_admin_menu' );
}

/**
 * Callback function for 'admin_menu' action.
 */
function _cb_admin_menu(): void {
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
			if ( -1 !== $customize_menu_index ) {
				break;
			}
		}
		unset( $submenu['themes.php'][ $customize_menu_index ] );
	}
}

/**
 * Customizes the side menu order.
 */
function customize_side_menu_order(): void {
	add_action(
		'admin_menu',
		function () {
			global $menu;
			$menu[19] = $menu[10];  // phpcs:ignore
			unset( $menu[10] );
		}
	);
}
