<?php
/**
 * Open Graph Protocol
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-29
 */

namespace wpinc\social\ogp;

require_once __DIR__ . '/site-meta.php';

const OGP_NS = 'prefix="og:http://ogp.me/ns#"';

/**
 * Output the open graph protocol meta tags.
 *
 * @param array $args {
 *     Options.
 *
 *     @type string $default_image_url     Default image URL.
 *     @type bool   $is_site_name_appended (Optional) Whether the site name is appended.
 *     @type string $separator             (Optional) Separator between the page title and the site name.
 *     @type int    $excerpt_length        (Optional) The length of excerpt.
 *     @type string $alt_description       (Optional) Alternative description.
 *     @type string $image_size            (Optional) The image size.
 *     @type string $image_meta_key        (Optional) Meta key of image.
 *     @type string $alt_image_url         (Optional) Alternative image URL.
 * }
 */
function the_ogp( array $args = array() ) {
	$args += array(
		'is_site_name_appended' => true,
		'separator'             => ' - ',
		'excerpt_length'        => 100,
		'alt_description'       => '',
		'default_image_url'     => '',
		'image_size'            => 'large',
		'image_meta_key'        => '',
		'alt_image_url'         => '',
	);

	$img_url = _get_the_image( $args['default_image_url'], $args['image_size'], $args['image_meta_key'], $args['alt_image_url'] );
	$tw_card = empty( $img_url ) ? 'summary' : 'summary_large_image';

	echo '<meta property="og:type" content="' . esc_attr( is_single() ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_attr( \wpinc\social\site_meta\get_current_url() ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( \wpinc\social\site_meta\get_the_title( $args['is_site_name_appended'], $args['separator'] ) ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( _get_the_description( $args['excerpt_length'], $args['alt_description'] ) ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( \wpinc\social\site_meta\get_site_name() ) . '">' . "\n";

	if ( ! empty( $img_url ) ) {
		echo '<meta property="og:image" content="' . esc_attr( $img_url ) . '">' . "\n";
		if ( class_exists( 'Simply_Static\Plugin' ) ) {
			echo '<link href="' . esc_attr( $img_url ) . '"><!-- for simply static -->' . "\n";
		}
	}
	echo '<meta name="twitter:card" content="' . esc_attr( $tw_card ) . '">' . "\n";
}

/**
 * Retrieve the description of the current page.
 *
 * @access private
 *
 * @param int    $excerpt_length  The length of excerpt.
 * @param string $alt_description Alternative description.
 * @return string The description.
 */
function _get_the_description( int $excerpt_length, string $alt_description ): string {
	if ( ! empty( $alt_description ) ) {
		return $alt_description;
	}
	$desc = '';
	if ( ! is_front_page() && is_singular() ) {
		global $post;
		$text = get_the_content( '', false, $post );
		$text = strip_shortcodes( $text );
		$text = excerpt_remove_blocks( $text );
		$text = apply_filters( 'the_content', $text );
		$text = str_replace( ']]>', ']]&gt;', $text );

		$desc = wp_strip_all_tags( str_replace( array( "\r\n", "\r", "\n" ), ' ', $text ), true );
		$desc = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $desc );  // Multi-byte trim.
		if ( mb_strlen( $desc ) > $excerpt_length ) {
			$desc = mb_substr( $desc, 0, $excerpt_length - 3 ) . '...';
		}
	}
	if ( empty( $desc ) ) {
		$desc = \wpinc\social\site_meta\get_site_description();
	}
	if ( empty( $desc ) ) {
		$desc = \wpinc\social\site_meta\get_site_name();
	}
	return $desc;
}

/**
 * Retrieve the image of the current page.
 *
 * @access private
 *
 * @param string $default_image_url Default image URL.
 * @param string $size              The image size.
 * @param string $meta_key          Meta key of image.
 * @param string $alt_image_url     Alternative image URL.
 * @return string The image URL.
 */
function _get_the_image( string $default_image_url, string $size, string $meta_key, string $alt_image_url ): string {
	if ( ! empty( $alt_image_url ) ) {
		return $alt_image_url;
	}
	if ( is_singular() ) {
		global $post;
		$src = _get_thumbnail_src( $size, $post->ID, $meta_key );
		if ( ! empty( $src ) ) {
			return $src;
		}
	}
	return $default_image_url;
}


// -----------------------------------------------------------------------------


/**
 *
 * Retrieve the thumbnail image source.
 *
 * @access private
 *
 * @param string $size     (Optional) The image size.
 * @param ?int   $post_id  (Optional) Post ID.
 * @param string $meta_key (Optional) Meta key of image.
 * @return string The url of the image.
 */
function _get_thumbnail_src( string $size = 'large', ?int $post_id = null, string $meta_key = '' ): string {
	if ( empty( $post_id ) ) {
		global $post;
		if ( ! $post ) {
			return '';
		}
		$post_id = $post->ID;
	}
	if ( empty( $meta_key ) ) {
		$tid = get_post_thumbnail_id( $post_id );
	} else {
		$tid = get_post_meta( $post_id, $meta_key, true );
	}
	if ( empty( $tid ) ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $tid, $size );
	return $url ? $url : '';
}
