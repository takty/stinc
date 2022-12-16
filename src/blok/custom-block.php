<?php
/**
 * Custom Block Plugins
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-10-12
 */

namespace wpinc\blok;

require_once __DIR__ . '/assets/theme-plugin-url.php';

/**
 * Registers custom blocks.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string 'category_title' Title of added category.
 *     @type array  'block_cards' {
 *         Arguments for cards block.
 *
 *         @type string 'class_card' CSS class for card block. Default 'card-%d'.
 *     }
 *     @type array  'block_frame' {
 *         Arguments for frame block.
 *
 *         @type string 'class_frame' CSS class for frame. Default 'frame'.
 *         @type string 'styles'      Name to label array of styles.
 *     }
 *     @type array  'block_tabs' {
 *         Arguments for tabs block.
 *
 *         @type string 'class_tab_scroll' CSS class for tab scroll. Default 'tab-scroll',
 *         @type string 'class_tab_stack'  CSS class for tab stack. Default 'tab-stack',
 *     }
 * }
 */
function register_custom_blocks( array $args = array() ): void {
	// phpcs:disable
	$args += array(
		'category_title' => __( 'Custom', 'wpinc_blok' ),
		'block_cards'    => array(),
		'block_frame'    => array(),
		'block_tabs'     => array(),
	);
	$args['block_cards'] += array(
		'class_card' => 'card-%d',
	);
	$args['block_frame'] += array(
		'class_frame' => 'frame',
		'styles'      => array(
			'alt' => __( 'Alt.', 'wpinc_blok' ),
		),
	);
	$args['block_tabs'] += array(
		'class_tab_scroll' => 'tab-scroll',
		'class_tab_stack'  => 'tab-stack',
	);
	// phpcs:enable

	$hook = version_compare( $GLOBALS['wp_version'], '5.8', '<' ) ? 'block_categories' : 'block_categories_all';
	add_filter(
		$hook,
		function ( $cats ) use ( $args ) {
			return _cb_block_categories_all( $args, $cats );
		}
	);
	\wpinc\initialize_theme_plugin_url();

	$blocks = array( 'card', 'cards', 'frame', 'tabs' );

	add_action(
		'init',
		function () use ( $args, $blocks ) {
			foreach ( $blocks as $b ) {
				register_block_type_from_metadata( __DIR__ . "/blocks/$b" );  // Must use 'register_block_type_from_metadata' instead of 'register_block_type' for WP 5.7.
				wp_set_script_translations( "wpinc-$b-editor-script", 'wpinc', __DIR__ . '\languages' );
				if ( isset( $args[ "block_$b" ] ) ) {
					wp_localize_script( "wpinc-$b-editor-script", "wpinc_{$b}_args", $args[ "block_$b" ] );
				}
			}
		}
	);
	foreach ( $args['block_frame']['styles'] as $name => $label ) {
		register_block_style(
			'wpinc/frame',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}

/**
 * Callback function for 'block_categories_all' filter.
 *
 * @access private
 *
 * @param array   $args       Arguments.
 * @param array[] $categories Array of categories for block types.
 * @return array Categories.
 */
function _cb_block_categories_all( array $args, array $categories ): array {
	$cats = array(
		array(
			'slug'  => 'wpinc',
			'title' => $args['category_title'],
		),
	);
	return array_merge( $cats, $categories );  // Insert at the beginning.
}


// -----------------------------------------------------------------------------


/**
 * Registers custom styles.
 */
function register_custom_styles(): void {
	register_block_style(
		'core/button',
		array(
			'name'  => 'accent',
			'label' => __( 'Accent', 'wpinc_blok' ),
		)
	);
}
