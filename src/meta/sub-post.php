<?php
/**
 * Sub Post-*
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-07-01
 */

namespace wpinc\meta;

/**
 * Retrieves sub post title.
 *
 * @param string       $meta_key Post meta key.
 * @param int|\WP_Post $post     (Optional) Post ID or WP_Post object. Default is global $post.
 * @return string Sub post title.
 */
function get_the_sub_title( string $meta_key, $post = 0 ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$id    = $post->ID ?? 0;
	$title = get_post_meta( $id, $meta_key, true );

	$ls = preg_split( '/<\s*br\s*\/?>/iu', $title );
	return implode( '<br>', array_map( 'wp_kses_post', $ls ) );
}

/**
 * Display sub post title with optional markup.
 *
 * @param string       $meta_key Post meta key.
 * @param string       $before   (Optional) Markup to prepend to the title. Default ''.
 * @param string       $after    (Optional) Markup to append to the title. Default ''.
 * @param int|\WP_Post $post     (Optional) Post ID or WP_Post object. Default is global $post.
 */
function the_sub_title( string $meta_key, string $before = '', string $after = '', $post = 0 ): void {
	$title = get_the_sub_title( $meta_key, $post );
	if ( empty( $title ) ) {
		return;
	}
	$title = $before . $title . $after;
	echo $title;  // phpcs:ignore
}


// -----------------------------------------------------------------------------


/**
 * Retrieves sub post content.
 *
 * @param string       $meta_key Post meta key.
 * @param int|\WP_Post $post     (Optional) Post ID or WP_Post object. Default is global $post.
 * @return string Sub post content.
 */
function get_the_sub_content( string $meta_key, $post = 0 ): string {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$id      = $post->ID ?? 0;
	$content = get_post_meta( $id, $meta_key, true );
	return $content;
}

/**
 * Display the sub post content.
 *
 * @param string       $meta_key Post meta key.
 * @param int|\WP_Post $post     (Optional) Post ID or WP_Post object. Default is global $post.
 */
function the_sub_content( string $meta_key, $post = 0 ): void {
	$content = get_the_sub_content( $meta_key, $post );
	if ( empty( $content ) ) {
		return;
	}
	$content = filter_content( $content );
	echo $title;  // phpcs:ignore
}

/**
 * Apply content filters to string.
 *
 * @param string $str String.
 * @return string Filtered string.
 */
function filter_content( string $str ): string {
	$str = apply_filters( 'the_content', $str );  // Shortcodes are expanded here.
	$str = str_replace( ']]>', ']]&gt;', $str );
	return $str;
}
