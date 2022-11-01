<?php
/**
 * Input Block Plugin
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-11-01
 */

namespace wpinc\blok\input;

require_once __DIR__ . '/assets/theme-plugin-url.php';
require_once __DIR__ . '/assets/admin-current-post.php';

/**
 * Adds input block.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string 'key'       Key of post meta.
 *     @type string 'label'     Label of the post meta.
 *     @type string 'post_type' Target post type.
 * }
 * @return bool True if the meta key was successfully registered, false if not.
 */
function add_block( array $args = array() ): bool {
	_register_block();

	if ( empty( $args['key'] ) || empty( $args['label'] ) || empty( $args['post_type'] ) ) {
		return false;
	}

	$inst = _get_instance();
	if ( ! isset( $args['post_type'] ) ) {
		$inst->pt_entries[ $args['post_type'] ] = array();
	}
	$inst->pt_entries[ $args['post_type'] ][] = array(
		'key'   => $args['key'],
		'label' => $args['label'],
	);

	return register_post_meta(
		$args['post_type'],
		$args['key'],
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				// For support for private fields.
				return current_user_can( 'edit_posts' );
			},
		)
	);
}

/**
 * Registers input block.
 *
 * @access private
 */
function _register_block(): void {
	static $do_once = false;
	if ( $do_once ) {
		return;
	}
	$do_once = true;
	\wpinc\initialize_theme_plugin_url();

	if ( did_action( 'widgets_init' ) ) {
		_cb_widgets_init();
	} else {
		add_action( 'widgets_init', '\wpinc\blok\input\_cb_widgets_init' );
	}
}

/**
 * Callback function for 'widgets_init' hook.
 */
function _cb_widgets_init() {
	$post_type = \wpinc\get_admin_post_type();
	if ( ! $post_type ) {
		return;
	}
	if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
		add_post_type_support( $post_type, 'custom-fields' );
	}
	$fes = _get_target_entries( $post_type );
	if ( ! empty( $fes ) ) {
		// Must use 'register_block_type_from_metadata' instead of 'register_block_type' for WP 5.7.
		register_block_type_from_metadata( __DIR__ . '/blocks/input' );

		wp_set_script_translations( 'wpinc-input-editor-script', 'wpinc', __DIR__ . '\languages' );
		wp_localize_script( 'wpinc-input-editor-script', 'wpinc_input_args', array( 'entries' => $fes ) );
	}
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
