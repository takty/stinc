<?php
namespace st;

/**
 *
 * Open Graph Protocol
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-12
 *
 */


require_once __DIR__ . '/image.php';
require_once __DIR__ . '/text.php';
require_once __DIR__ . '/url.php';


function the_ogp( $logo_src = '', $image_meta_key = false, $alt_image_src = false ) {
	echo '<meta property="og:type" content="' . esc_attr( is_single() ? 'article' : 'website' ) . "\">\n";
	the_ogp_url();
	the_ogp_title();
	the_ogp_description();
	the_ogp_site_name();
	the_ogp_image( $logo_src, $image_meta_key, $alt_image_src );
}

function the_ogp_url() {
	echo '<meta property="og:url" content="' . esc_attr( get_the_ogp_url() ) . "\">\n";
}

function the_ogp_title() {
	echo '<meta property="og:title" content="' . esc_attr( get_the_ogp_title() ) . "\">\n";
}

function the_ogp_description() {
	echo '<meta property="og:description" content="' . esc_attr( get_the_ogp_description() ) . "\">\n";
}

function the_ogp_site_name() {
	echo '<meta property="og:site_name" content="' . esc_attr( get_the_ogp_site_name() ) . "\">\n";
}

function the_ogp_image( $logo_src, $image_meta_key, $alt_image_src ) {
	$src = get_the_ogp_image( $logo_src, $image_meta_key, $alt_image_src );
	if ( empty( $src ) ) return;
	echo '<meta property="og:image" content="' . esc_attr( $src ) . "\">\n";
}

function get_the_ogp_url() {
	if ( is_singular() ) {
		$url = get_permalink();
	} else {
		$url = \st\get_current_uri();
	}
	return $url;
}

function get_the_ogp_title() {
	if ( _ogp_is_singlular() ) {
		$title = implode( ' ', \st\separate_line( get_the_title() ) );
	} else if ( is_archive() ) {
		$title = post_type_archive_title( '', false );
	} else {
		$title = get_the_ogp_site_name();
	}
	return $title;
}

function get_the_ogp_description() {
	$desc = '';
	if ( _ogp_is_singlular() ) {
		if ( has_excerpt() ) {
			$desc = strip_tags( get_the_excerpt() );
		} else {
			global $post;
			$cont = strip_tags( strip_shortcodes( $post->post_content ) );
			$desc = str_replace( "\r\n", ' ', mb_substr( $cont, 0, 100 ) );
			if ( ! empty( trim( $desc ) ) && mb_strlen( $cont ) > 100 ) $desc .= '...';
		}
	}
	if ( empty( trim( $desc ) ) ) {
		if ( class_exists( '\st\Multilang' ) ) {
			$desc = \st\Multilang::get_instance()->get_bloginfo( 'description' );
		} else {
			$desc = get_bloginfo( 'description' );
		}
	}
	if ( empty( trim( $desc ) ) ) {
		$desc = get_the_ogp_site_name();
	}
	return $desc;
}

function _ogp_is_singlular() {
	$is_singular = false;

	if ( class_exists( '\st\Multihome' ) ) {
		$is_singular = is_singular() && ! \st\Multihome::get_instance()->is_front_page();
	} else if ( class_exists( '\st\Multilang' ) ) {
		$is_singular = is_singular() && ! \st\Multilang::get_instance()->is_front_page();
	} else {
		$is_singular = is_singular() && ! is_front_page();
	}
	return $is_singular;
}

function get_the_ogp_site_name() {
	if ( class_exists( '\st\Multilang' ) ) {
		$site_name = \st\Multilang::get_instance()->get_bloginfo( 'name' );
	} else {
		$site_name = get_bloginfo( 'name' );
	}
	$site_name = implode( ' ', \st\separate_line( $site_name ) );
	return $site_name;
}

function get_the_ogp_image( $logo_src = '', $meta_key = false, $alt_image_src = false ) {
	if ( $alt_image_src !== false ) return $alt_image_src;
	if ( ! is_singular() ) return $logo_src;
	global $post;
	$src = \st\Image::get_thumbnail_src( 'large', $post->ID, $meta_key );
	if ( ! empty( $src ) ) return $src;

	$ais = \st\Image::get_first_image_src( 'large' );
	if ( ! empty( $ais ) ) return $ais;
	return $logo_src;
}
