<?php
/**
 * Utilities
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-03-21
 */

namespace wpinc\blok;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Adds 'small' tag button to the toolbar of heading blocks.
 *
 * @param string|null $url_to (Optional) URL to this script.
 */
function add_small_button_to_heading( ?string $url_to = null ): void {
	$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
	wp_enqueue_script(
		'wpinc-blok-small-tag',
		\wpinc\abs_url( $url_to, './assets/js/small-tag.min.js' ),
		array( 'wp-compose', 'wp-element', 'wp-data', 'wp-element', 'wp-editor' ),
		'1.0',
		true
	);
	wp_set_script_translations( 'wpinc-blok-small-tag', 'wpinc', __DIR__ . '\languages' );
}

/**
 * Adds list styles to the side panel of list blocks.
 *
 * @param string|null $url_to (Optional) URL to this script.
 */
function add_list_styles( ?string $url_to = null ): void {
	$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
	wp_enqueue_script(
		'wpinc-blok-list-style',
		\wpinc\abs_url( $url_to, './assets/js/list-style.min.js' ),
		array( 'wp-compose', 'wp-element', 'wp-blocks', 'wp-i18n' ),
		'1.0',
		true
	);
	wp_set_script_translations( 'wpinc-blok-list-style', 'wpinc', __DIR__ . '\languages' );
}

/**
 * Sets used heading tags.
 *
 * @param int         $first_level First level of heading tag.
 * @param int         $count       Count of headings.
 * @param string|null $url_to      (Optional) URL to this script.
 */
function set_used_heading( int $first_level = 2, int $count = 3, ?string $url_to = null ): void {
	if ( ! is_admin() ) {
		return;
	}
	$ls = array_diff(
		range( 1, 6 ),
		range( $first_level, $first_level + $count - 1 )
	);
	$hs = array();
	foreach ( $ls as $l ) {
		$h    = __( 'Heading ' . $l );  // phpcs:ignore
		$hs[] = "[aria-label=\"$h\"]";
	}
	$style = implode( ',', $hs ) . '{display:none;}';

	add_action(
		'admin_enqueue_scripts',
		function () use ( $style, $first_level, $url_to ) {
			wp_add_inline_style( 'wp-admin', $style );

			$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
			wp_enqueue_script(
				'wpinc-blok-used-heading',
				\wpinc\abs_url( $url_to, './assets/js/used-heading.min.js' ),
				array( 'wp-compose', 'wp-element' ),
				'1.0',
				true
			);
			$ps = array(
				'first_level' => $first_level,
			);
			wp_localize_script( 'wpinc-blok-used-heading', 'wpinc_blok_used_heading', $ps );
		}
	);
}
