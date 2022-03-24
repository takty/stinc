<?php
/**
 * Unregister Blocks
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-03-21
 */

namespace wpinc\blok;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Unregisters block types.
 *
 * @param string|string[] $type_s A block type or an array of block types.
 * @param string|null     $url_to (Optional) URL to this script.
 */
function unregister_block_type( $type_s, ?string $url_to = null ): void {
	$inst = _get_unregistered_blocks();
	$ts   = is_array( $type_s ) ? $type_s : array( $type_s );

	$inst->types = array_merge( $inst->types, $ts );
	_enqueue_script_for_unregistration( $url_to );
}

/**
 * Unregisters block categories.
 *
 * @param string|string[] $category_s A category or an array of categories.
 * @param string|null     $url_to     (Optional) URL to this script.
 */
function unregister_block_category( $category_s, ?string $url_to = null ): void {
	$inst = _get_unregistered_blocks();
	$cs   = is_array( $category_s ) ? $category_s : array( $category_s );

	$inst->categories = array_merge( $inst->categories, $cs );
	_enqueue_script_for_unregistration( $url_to );
}

/**
 * Unregisters block variations.
 *
 * @param string          $type        A block type.
 * @param string|string[] $variation_s A variation or an array of variations.
 * @param string|null     $url_to      (Optional) URL to this script.
 */
function unregister_block_variation( string $type, $variation_s, ?string $url_to = null ): void {
	$inst = _get_unregistered_blocks();
	$vs   = is_array( $variation_s ) ? $variation_s : array( $variation_s );

	if ( isset( $inst->type_variations[ $type ] ) ) {
		$inst->type_variations[ $type ] = array_merge( $inst->type_variations[ $type ], $vs );
	} else {
		$inst->type_variations[ $type ] = $vs;
	}
	_enqueue_script_for_unregistration( $url_to );
}

/**
 * Unregisters block styles.
 *
 * @param string          $type    A block type.
 * @param string|string[] $style_s A style or an array of styles.
 * @param string|null     $url_to  (Optional) URL to this script.
 */
function unregister_block_style( string $type, $style_s, ?string $url_to = null ): void {
	$inst = _get_unregistered_blocks();
	$ss   = is_array( $style_s ) ? $style_s : array( $style_s );

	if ( isset( $inst->type_styles[ $type ] ) ) {
		$inst->type_styles[ $type ] = array_merge( $inst->type_styles[ $type ], $ss );
	} else {
		$inst->type_styles[ $type ] = $ss;
	}
	_enqueue_script_for_unregistration( $url_to );
}

/**
 * Gets instance of unregistered_blocks.
 *
 * @access private
 *
 * @return object Instance.
 */
function _get_unregistered_blocks(): object {
	static $inst = null;
	if ( $inst ) {
		return $inst;
	}
	$inst = new class() {
		/**
		 * Block types.
		 *
		 * @var array
		 */
		public $types = array();

		/**
		 * Block categories.
		 *
		 * @var array
		 */
		public $categories = array();

		/**
		 * Block type to variations.
		 *
		 * @var array
		 */
		public $type_variations = array();

		/**
		 * Block type to styles.
		 *
		 * @var array
		 */
		public $type_styles = array();
	};
	return $inst;
}

/**
 * Enqueues script for unregistration of blocks.
 *
 * @access private
 *
 * @param string|null $url_to URL to this script.
 */
function _enqueue_script_for_unregistration( ?string $url_to = null ): void {
	static $activated = false;
	if ( $activated ) {
		return;
	}
	$activated = true;

	$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
	add_action(
		'enqueue_block_editor_assets',
		function () use ( $url_to ) {
			wp_enqueue_script(
				'wpinc-blok-unregistration',
				\wpinc\abs_url( $url_to, './assets/js/block-unregistration.min.js' ),
				array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
				'1.0',
				true
			);
			$inst = _get_unregistered_blocks();
			$ps   = array(
				'types'           => $inst->types,
				'categories'      => $inst->categories,
				'type_variations' => $inst->type_variations,
				'type_styles'     => $inst->type_styles,
			);
			wp_localize_script( 'wpinc-blok-unregistration', 'wpinc_blok_unregistration', $ps );
		}
	);
}
