<?php
/**
 * Utilities
 *
 * @package Wpinc Blok
 * @author Takuto Yanagida
 * @version 2022-08-16
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
	$ls = array_diff( range( 1, 6 ), range( $first_level, $first_level + $count - 1 ) );
	$hs = array();
	foreach ( $ls as $l ) {
		$hs[] = '[aria-label="' . __( "Heading $l" ) . '"]';  // phpcs:ignore
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
			wp_localize_script( 'wpinc-blok-used-heading', 'wpinc_blok_used_heading', array( 'first_level' => $first_level ) );
		}
	);
	add_action(
		'save_post',
		function ( int $post_id, \WP_Post $post ) use ( $first_level ): void {
			_cb_save_post( $post_id, $post, $first_level );
		},
		10,
		2
	);
}

/**
 * Callback function for 'save_post' hook.
 *
 * @param int      $post_id     Post ID.
 * @param \WP_Post $post        Post object.
 * @param int      $first_level First level of heading tag.
 */
function _cb_save_post( int $post_id, \WP_Post $post, int $first_level ): void {
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
	}
	$c = _filter_block_attributes(
		$c,
		'heading',
		function ( $ats ) use ( $first_level ) {
			if ( isset( $ats['level'] ) ) {
				if ( $ats['level'] < $first_level ) {
					$ats['level'] = $first_level;
				}
			} else {
				$ats['level'] = $first_level;
			}
			return $ats;
		}
	);
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
}


// -----------------------------------------------------------------------------


/**
 * Filters block attributes.
 *
 * @access private
 *
 * @param string   $doc  Document.
 * @param string   $name Block name.
 * @param callable $fn   Function for filtering attributes.
 * @return string Filtered document.
 */
function _filter_block_attributes( string $doc, string $name, callable $fn ): string {
	$offset = 0;
	do {
		list( $doc, $offset ) = _next_block( $doc, $name, $fn, $offset );
	} while ( $offset );
	return $doc;
}

/**
 * Processes next block.
 *
 * @access private
 *
 * @param string   $doc    Document.
 * @param string   $name   Block name.
 * @param callable $fn     Function for filtering attributes.
 * @param int      $offset Offset index.
 * @return array Array of modified document and new offset index.
 */
function _next_block( string $doc, string $name, callable $fn, int $offset ): array {
	$ms  = null;
	$res = preg_match(
		"/<!--\s+wp:$name\s+(?P<ats>{(?:(?:[^}]+|}+(?=})|(?!}\s+\/?-->).)*+)?}\s+)?-->/s",
		$doc,
		$ms,
		PREG_OFFSET_CAPTURE,
		$offset
	);
	if ( ! $res ) {
		return array( $doc, 0 );
	}
	list( $m, $at ) = $ms[0];

	$has_ats = isset( $ms['ats'] ) && -1 !== $ms['ats'][1];
	$ats     = json_decode( $has_ats ? $ms['ats'][0] : '{}', true );
	$new_ats = $fn( $ats );

	$new = "<!-- wp:$name " . ( empty( $new_ats ) ? '' : ( wp_json_encode( $new_ats ) . ' ' ) ) . '-->';
	$doc = substr_replace( $doc, $new, $at, strlen( $m ) );

	return array( $doc, $at + strlen( $new ) );
}
