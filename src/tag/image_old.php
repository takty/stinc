<?php
namespace st;

/**
 *
 * Custom Template Tags for Images
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-12-17
 *
 */


function get_first_image_src() {
	if ( ! is_singular() ) return false;
	global $post;
	preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $ms );
	if ( empty( $ms[1][0] ) ) return false;
	$src = $ms[1][0];
	return $src;
}

function get_attachment_id( $url ) {
	global $wpdb;
	preg_match( '/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches );
 	$guid = str_replace( $matches[0], $matches[1] . $matches[4], $url );
	$sql = "SELECT ID FROM {$wpdb->posts} WHERE guid = %s";
	return ( int ) $wpdb->get_var( $wpdb->prepare( $sql, $guid ) );
}


// -----------------------------------------------------------------------------

if ( ! function_exists( 'st\get_the_post_thumbnail_src' ) ) {
	function get_the_post_thumbnail_src( $size = 'thumbnail', $post_id = false, $meta_key = false ) {
		$src = get_the_thumbnail( $size, $post_id, $meta_key );
		return $src[0];
	}
}

function the_thumbnail_style( $size = 'large', $post_id = false, $meta_key = false ) {
	echo get_the_thumbnail_style( $size, $post_id, $meta_key );
}

function get_the_thumbnail_style( $size = 'large', $post_id = false, $meta_key = false ) {
	$src = get_the_post_thumbnail_src( $size, $post_id, $meta_key );
	if ( empty( $src ) ) return '';
	return ' style="background-image: url(' . esc_url( $src ) . ')"';
}

function the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
	$src = get_the_thumbnail( $size, $post_id, $meta_key );
	if ( empty( $src ) ) return '';
	return '<img class="size-' . $size . '" src="' . $src[0] . '" alt="" width="' . $src[1] . '" height="' . $src[2] . '" />';
}

function the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	$src = wp_get_attachment_image_src( $tid, $size );
	if ( empty( $src ) ) return '';
	$p = get_post( $tid );
	$exc = '';
	if ( ! empty( $p ) ) {
		$exc = $p->post_excerpt;
	}
	$img = '<img class="size-' . $size . '" src="' . $src[0] . '" alt="" width="' . $src[1] . '" height="' . $src[2] . '" />';
	return '<figure class="wp-caption">' . $img . '<figcaption class="wp-caption-text">' . $exc . '</figcaption></figure>';
}

function get_the_thumbnail( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	return wp_get_attachment_image_src( $tid, $size );
}

function get_thumbnail_id( $post_id = false, $meta_key = false ) {
	global $post;
	if ( $post_id === false ) {
		if ( ! $post ) return '';
		$post_id = $post->ID;
	}
	if ( $meta_key === false ) {
		if ( ! has_post_thumbnail( $post_id ) ) return false;
		return get_post_thumbnail_id( $post_id );
	}
	return get_post_meta( $post_id, $meta_key, TRUE );
}


// -----------------------------------------------------------------------------

function the_thumbnail_style_responsive( $size = 'large', $size_phone = 'medium' ) {
	echo get_the_thumbnail_style_responsive( $size, $size_phone );
}

$thumbnail_style_responsive = [];
$thumbnail_style_responsive_id = 0;

function get_the_thumbnail_style_responsive( $size = 'large', $size_phone = 'medium' ) {
	global $post;
	if ( ! has_post_thumbnail( $post->ID ) ) return '';

	global $thumbnail_style_responsive, $thumbnail_style_responsive_id;
	$tid = get_post_thumbnail_id( $post->ID );
	$src = esc_url( wp_get_attachment_image_src( $tid, $size )[0] );
	$src_phone = esc_url( wp_get_attachment_image_src( $tid, $size_phone )[0] );
	$id = 'thumbnail_style_responsive-' . ( $thumbnail_style_responsive_id++ );
	$thumbnail_style_responsive[] = "@media (max-width: 599px) {#$id {background-image: url('$src_phone');}}";
	$thumbnail_style_responsive[] = "@media (min-width: 600px) {#$id {background-image: url('$src');}}";
	return " id=\"$id\"";
}

function output_thumbnail_style_responsive() {
	global $thumbnail_style_responsive;
	if ( count( $thumbnail_style_responsive ) === 0 ) return;
	echo '<style>';
	foreach ( $thumbnail_style_responsive as $line ) {
		echo $line;
	}
	echo '</style>';
}
