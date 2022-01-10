<?php
/**
 * Customizing Admin Bar and Side Menu.
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2022-01-08
 */

namespace wpinc\robor;

/**
 * Remove WordPress logo icon.
 */
function remove_wp_logo() {
	add_action(
		'admin_bar_menu',
		function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'wp-logo' );
		},
		300
	);
}

/**
 * Remove the customize menu.
 */
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
					if ( -1 !== $customize_menu_index ) {
						break;
					}
				}
				unset( $submenu['themes.php'][ $customize_menu_index ] );
			}
		}
	);
}

/**
 * Customize the side menu order.
 */
function customize_side_menu_order() {
	add_action(
		'admin_menu',
		function () {
			global $menu;
			$menu[19] = $menu[10];  // phpcs:ignore
			unset( $menu[10] );
		}
	);
}
