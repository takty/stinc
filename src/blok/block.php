<?php
/**
 * Block Plugins
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-03-03
 */

namespace wpinc\blok;

/**
 * Initializes.
 *
 * @param string|null $category_title Title of added category.
 */
function initialize( ?string $category_title = null ): void {
	if ( null === $category_title ) {
		$category_title = __( 'Custom', 'wpinc_blok' );
	}
	add_action(
		'block_categories_all',
		function ( $categories ) use ( $category_title ) {
			return _cb_block_categories_all( $categories, $category_title );
		},
		10
	);

	add_filter( 'plugins_url', '\wpinc\blok\_cb_plugins_url', 10, 3 );
	$blocks = array( 'tabs', 'cards', 'card' );

	foreach ( $blocks as $b ) {
		register_block_type( __DIR__ . "/blocks/$b" );
		wp_set_script_translations( "wpinc-$b-editor-script", 'wpinc', __DIR__ . '\languages' );
	}
}

/**
 * Callback function for 'block_categories_all' filter.
 *
 * @access private
 *
 * @param array[] $categories Array of categories for block types.
 * @param string  $title      Title of added category.
 * @return array Categories.
 */
function _cb_block_categories_all( array $categories, string $title ): array {
	$cats = array(
		array(
			'slug'  => 'wpinc',
			'title' => $title,
		),
	);
	return array_merge( $cats, $categories );
}

/**
 * Callback function for 'plugins_url' filter.
 *
 * @access private
 *
 * @param string $url    The complete URL to the plugins directory including scheme and path.
 * @param string $path   Path relative to the URL to the plugins directory. Blank string if no path is specified.
 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin is specified.
 * @return string Filtered URL.
 */
function _cb_plugins_url( string $url, string $path, string $plugin ): string {
	$theme_dir = wp_normalize_path( defined( 'THEME_PATH' ) ? THEME_PATH : get_stylesheet_directory() );
	if ( false !== strpos( $plugin, $theme_dir ) ) {
		return \wpinc\abs_url( \wpinc\get_file_uri( dirname( $plugin ) ), $path );
	}
	return $url;
}
