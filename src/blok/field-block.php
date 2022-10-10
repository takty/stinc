<?php
/**
 * Field Block Plugin
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-10-10
 */

namespace wpinc\blok\field;

require_once __DIR__ . '/assets/theme-plugin-url.php';
require_once __DIR__ . '/assets/admin-current-post.php';

/**
 * Registers field block.
 */
function register_field_block(): void {
	\wpinc\initialize_theme_plugin_url();

	add_filter( 'init', '\wpinc\blok\field\_cb_init' );
	add_filter( 'save_post', '\wpinc\blok\field\_cb_save_post', 10, 2 );
	add_filter( 'pre_render_block', '\wpinc\blok\field\_cb_pre_render_block', 10, 2 );
}

/**
 * Adds field block.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string 'key'       Key of post meta.
 *     @type string 'label'     Label of the post meta.
 *     @type string 'post_type' Target post type.
 *     @type bool   'do_render' Whether to render before storing contents.
 * }
 */
function add_field_block( array $args = array() ): void {
	// phpcs:disable
	$args += array(
		'key'       => '',
		'label'     => '',
		'post_type' => '*',
		'do_render' => true,
	);
	// phpcs:enable

	$inst = _get_instance();
	if ( ! isset( $args['post_type'] ) ) {
		$inst->pt_entries[ $args['post_type'] ] = array();
	}
	$inst->pt_entries[ $args['post_type'] ][] = array(
		'key'       => $args['key'],
		'label'     => $args['label'],
		'do_render' => $args['do_render'],
	);
}

/**
 * Callback function for 'init' hook.
 */
function _cb_init() {
	$post_type = \wpinc\get_admin_post_type();
	if ( ! $post_type ) {
		return;
	}
	$fes = _get_target_entries( $post_type );
	if ( ! empty( $fes ) ) {
		// Must use 'register_block_type_from_metadata' instead of 'register_block_type' for WP 5.7.
		register_block_type_from_metadata( __DIR__ . '/blocks/field' );

		wp_set_script_translations( 'wpinc-field-editor-script', 'wpinc', __DIR__ . '\languages' );
		wp_localize_script( 'wpinc-field-editor-script', 'wpinc_field_args', array( 'entries' => $fes ) );
	}
}

/**
 * Callback function for 'save_post' hook.
 *
 * @param int      $post_ID Post ID.
 * @param \WP_Post $post    Post.
 */
function _cb_save_post( int $post_ID, \WP_Post $post ): void {
	$inst = _get_instance();
	if ( ! isset( $inst->pt_entries['*'] ) && ! isset( $inst->pt_entries[ $post->post_type ] ) ) {
		return;
	}
	$entries = array_column( _get_target_entries( $post->post_type ), null, 'key' );
	$key_sbs = array();

	$bs = parse_blocks( $post->post_content );
	foreach ( $bs as $b ) {
		if ( 'wpinc/field' === $b['blockName'] ) {
			$key = $b['attrs']['key'];
			if ( isset( $entries[ $key ] ) ) {
				if ( ! isset( $key_sbs[ $key ] ) ) {
					$key_sbs[ $key ] = array();
				}
				foreach ( $b['innerBlocks'] as $ib ) {
					$key_sbs[ $key ][] = $ib;
				}
			}
		}
	}
	foreach ( $key_sbs as $key => $sb ) {
		$fn  = $entries[ $key ]['do_render'] ? 'render_block' : 'serialize_block';
		$val = implode( '', array_map( $fn, $sb ) );
		update_post_meta( $post_ID, $key, $val );
	}
}

/**
 * Callback function for 'pre_render_block' hook.
 *
 * @param string|null $pre_render   The pre-rendered content. Default null.
 * @param array       $parsed_block The block being rendered.
 * @return string|null Pre-rendered string.
 */
function _cb_pre_render_block( ?string $pre_render, array $parsed_block ): ?string {
	if ( 'wpinc/field' === $parsed_block['blockName'] ) {
		return '';
	}
	return $pre_render;
}

/**
 * Retrieves target entries.
 *
 * @access private
 *
 * @param string $post_type Post type.
 * @return array Entries.
 */
function _get_target_entries( string $post_type ): array {
	$inst = _get_instance();
	$fes  = array();
	if ( isset( $inst->pt_entries[ $post_type ] ) ) {
		foreach ( $inst->pt_entries[ $post_type ] as $e ) {
			$fes[] = $e;
		}
	}
	if ( isset( $inst->pt_entries['*'] ) ) {
		foreach ( $inst->pt_entries['*'] as $e ) {
			$fes[] = $e;
		}
	}
	return $fes;
}

/**
 * Gets entries.
 *
 * @access private
 *
 * @return object Entries.
 */
function _get_instance(): object {
	static $inst = null;
	if ( $inst ) {
		return $inst;
	}
	$inst = new class() {
		/**
		 * Array of post type to entries.
		 *
		 * @var array
		 */
		public $pt_entries = array();
	};
	return $inst;
}
