<?php
/**
 * Theme plugin URL.
 *
 * @package Wpinc
 * @author Takuto Yanagida
 * @version 2022-09-10
 */

namespace wpinc;

require_once __DIR__ . '/asset-url.php';

/**
 * Initializes theme plugin URL.
 */
function initialize_theme_plugin_url(): void {
	static $do_once = false;
	if ( $do_once ) {
		return;
	}
	$do_once = true;
	add_filter( 'plugins_url', '\wpinc\_cb_plugins_url_for_theme', 10, 3 );
}

/**
 * Callback function for 'plugins_url' hook.
 *
 * @access private
 *
 * @param string $url    The complete URL to the plugins directory including scheme and path.
 * @param string $path   Path relative to the URL to the plugins directory. Blank string if no path is specified.
 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin is specified.
 * @return string Filtered URL.
 */
function _cb_plugins_url_for_theme( string $url, string $path, string $plugin ): string {
	$theme_dir = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_stylesheet_directory() );
	if ( false !== strpos( $plugin, $theme_dir ) ) {
		return \wpinc\abs_url( \wpinc\get_file_uri( dirname( $plugin ) ), $path );
	}
	return $url;
}
