<?php
/**
 * Utilities
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-08-08
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
	add_action(
		'enqueue_block_editor_assets',
		function () use ( $url_to ) {
			wp_enqueue_script(
				'wpinc-blok-small-tag',
				\wpinc\abs_url( $url_to, './assets/js/small-tag.min.js' ),
				array( 'wp-compose', 'wp-element', 'wp-data', 'wp-element', 'wp-editor' ),
				'1.0',
				true
			);
			wp_set_script_translations( 'wpinc-blok-small-tag', 'wpinc', __DIR__ . '\languages' );
		}
	);
}

/**
 * Adds list styles to the side panel of list blocks.
 *
 * @param string|null $url_to (Optional) URL to this script.
 */
function add_list_styles( ?string $url_to = null ): void {
	$url_to = untrailingslashit( $url_to ?? \wpinc\get_file_uri( __DIR__ ) );
	add_action(
		'enqueue_block_editor_assets',
		function () use ( $url_to ) {
			wp_enqueue_script(
				'wpinc-blok-list-style',
				\wpinc\abs_url( $url_to, './assets/js/list-style.min.js' ),
				array( 'wp-compose', 'wp-element', 'wp-blocks', 'wp-i18n' ),
				'1.0',
				true
			);
			wp_set_script_translations( 'wpinc-blok-list-style', 'wpinc', __DIR__ . '\languages' );
		}
	);
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
		'enqueue_block_editor_assets',
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
			$ps = array( 'first_level' => $first_level );
			wp_localize_script( 'wpinc-blok-used-heading', 'wpinc_blok_used_heading', $ps );
		}
	);

	add_action(
		'save_post',
		function ( int $post_id, \WP_Post $post ) use ( $first_level ): void {
			static $doing = false;
			if ( $doing ) {
				return;
			}
			$parent_id = wp_is_post_revision( $post_id );
			if ( $parent_id ) {
				$post_id = $parent_id;
			}

			$c = $post->post_content;
			for ( $i = 2; $i < $first_level; ++$i ) {
				$c = str_replace( "<h$i", "<h$first_level", $c );
				$c = str_replace( "</h$i", "</h$first_level", $c );
				if ( 2 === $i ) {
					$c = str_replace( '<!-- wp:heading -->', "<!-- wp:heading {\"level\":$first_level} -->", $c );
				} else {
					$c = str_replace( "<!-- wp:heading {\"level\":$i} -->", "<!-- wp:heading {\"level\":$first_level} -->", $c );
				}
			}

			if ( $c !== $post->post_content ) {
				$doing = true;
				wp_update_post(
					array(
						'ID'           => $post_id,
						'post_content' => $c,
					)
				);
				$doing = false;
			}
		},
		10,
		2
	);
}
