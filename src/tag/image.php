<?php
namespace st;
/**
 *
 * Custom Template Tags for Responsive Images
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-31
 *
 */


function get_thumbnail_src( $size = 'large', $post_id = false, $meta_key = false ) {
	$tid = get_thumbnail_id( $post_id, $meta_key );
	if ( $tid === false ) return '';
	return get_attachment_src( $size, $tid );
}

function get_attachment_src( $size = 'large', $aid ) {
	$ais = wp_get_attachment_image_src( $aid, $size );
	return $ais === false ? '' : $ais[0];
}

function get_first_image_src( $size = 'large' ) {
	$fis = _scrape_first_image_src();
	if ( $fis === false ) return '';
	$aid = get_attachment_id( $fis );
	if ( $aid === false ) return '';
	return get_attachment_src( $size, $aid );
}

function get_thumbnail_id( $post_id = false, $meta_key = false ) {
	global $post;
	if ( $post_id === false ) {
		if ( ! $post ) return false;
		$post_id = $post->ID;
	}
	if ( $meta_key === false ) {
		if ( ! has_post_thumbnail( $post_id ) ) return false;
		return get_post_thumbnail_id( $post_id );
	}
	$pm = get_post_meta( $post_id, $meta_key, true );
	return empty( $pm ) ? false : $pm;
}

function get_attachment_id( $url ) {
	global $wpdb;
	preg_match( '/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches );
	$guid = str_replace( $matches[0], $matches[1] . $matches[4], $url );
	$sql = "SELECT ID FROM {$wpdb->posts} WHERE guid = %s";
	$v = $wpdb->get_var( $wpdb->prepare( $sql, $guid ) );
	return $v === null ? false : intval( $v );
}

function get_first_image_id() {
	$fis = get_first_image_src();
	if ( $fis === false ) return false;
	$aid = get_attachment_id( $fis );
	if ( $aid === false ) return false;
	return $aid;
}

function _scrape_first_image_src() {
	if ( ! is_singular() ) return false;
	global $post;
	preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $ms );
	if ( empty( $ms[1][0] ) ) return false;
	$src = $ms[1][0];
	return $src;
}


class Image {

	const WIN_SIZE_RESPONSIVE = 600;
	const DATA_ATTR           = 'image-id';

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new Image();
		return self::$_instance;
	}

	private $_res_styles      = [];
	private $_res_styles_id   = 0;
	private $_is_action_added = false;

	private function __construct() {}

	public function the_thumbnail_style( $size = 'large', $post_id = false, $meta_key = false ) {
		echo $this->get_the_thumbnail_style( $size, $post_id, $meta_key );
	}

	public function the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
		echo $this->get_the_thumbnail_image( $size, $post_id, $meta_key );
	}

	public function the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
		echo $this->get_the_thumbnail_figure( $size, $post_id, $meta_key );
	}

	public function get_the_thumbnail_style( $size_s = 'large', $post_id = false, $meta_key = false ) {
		if ( is_array( $size_s ) ) {
			$tsr = $this->_get_res_style( $size_s, $post_id, $meta_key );
			if ( ! empty( $tsr ) ) return $tsr;
			$size_s = empty( $size_s ) ? 'large' : $size_s[0];
		}
		$ts = get_thumbnail_src( $size_s, $post_id, $meta_key );
		if ( empty( $ts ) ) return '';
		$src = esc_attr( $ts );
		return " style=\"background-image: url('$src')\"";
	}

	public function get_the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
		$tid = get_thumbnail_id( $post_id, $meta_key );
		if ( $tid === false ) return '';
		$ais = wp_get_attachment_image_src( $tid, $size );
		if ( $ais === false ) return '';
		$src = esc_attr( $ais[0] );
		return "<img class=\"size-$size\" src=\"$src\" alt=\"\" width=\"$ais[1]\" height=\"$ais[2]\">";
	}

	public function get_the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
		$tid = get_thumbnail_id( $post_id, $meta_key );
		if ( $tid === false ) return '';
		$ais = wp_get_attachment_image_src( $tid, $size );
		if ( $ais === false ) return '';
		$src = esc_attr( $ais[0] );
		$img = "<img class=\"size-$size\" src=\"$src\" alt=\"\" width=\"$ais[1]\" height=\"$ais[2]\">";

		$p = get_post( $tid );
		$exc = empty( $p ) ? '' : $p->post_excerpt;
		return "<figure class=\"wp-caption\">$img<figcaption class=\"wp-caption-text\">$exc</figcaption></figure>";
	}


	// -----------------------------------------------------------------------------


	public function output_responsive_styles() {
		if ( WP_DEBUG ) trigger_error( 'You do not need to call \\st\\output_responsive_styles() or \\st\\Image#output_responsive_styles().', E_WARNING );
	}

	private function _get_res_style( $sizes = [ 'medium', 'large' ], $post_id = false, $meta_key = false ) {
		if ( empty( $sizes ) || count( $sizes ) === 1 ) return '';
		$tid = get_thumbnail_id( $post_id, $meta_key );
		if ( $tid === false ) return '';

		$as0 = get_attachment_src( $sizes[0], $tid );
		$as1 = get_attachment_src( $sizes[1], $tid );
		if ( empty( $as0 ) || empty( $as1 ) ) return '';

		$id = '' . ( $this->_res_styles_id++ );
		$max = self::WIN_SIZE_RESPONSIVE - 1;
		$min = self::WIN_SIZE_RESPONSIVE;
		$this->_add_res_style( $id, "max-width: {$max}px", $as0);
		$this->_add_res_style( $id, "min-width: {$min}px", $as1);
		$da = self::DATA_ATTR;
		return " data-$da=\"$id\"";
	}

	private function _add_res_style( $id, $query, $src ) {
		$da = self::DATA_ATTR;
		$src = esc_attr( $src );
		$this->_res_styles[] = "@media ($query) {*[data-$da='$id'] {background-image: url('$src');}}";

		if ( ! $this->_is_action_added ) {
			add_action( 'wp_footer', [ $this, '_cb_output_responsive_styles' ], 1, 1 );
			$this->_is_action_added = true;
		}
	}

	public function _cb_output_responsive_styles() {
		echo '<style>' . implode( '', $this->_res_styles ) . '</style>';
	}

}


// -------------------------------------------------------------------------


function the_thumbnail_style( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	echo $img->get_the_thumbnail_style( $size, $post_id, $meta_key );
}

function the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	echo $img->get_the_thumbnail_image( $size, $post_id, $meta_key );
}

function the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	echo $img->get_the_thumbnail_figure( $size, $post_id, $meta_key );
}

function get_the_thumbnail_style( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	return $img->get_the_thumbnail_style( $size, $post_id, $meta_key );
}

function get_the_thumbnail_image( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	return $img->get_the_thumbnail_image( $size, $post_id, $meta_key );
}

function get_the_thumbnail_figure( $size = 'large', $post_id = false, $meta_key = false ) {
	$img = Image::get_instance();
	return $img->get_the_thumbnail_figure( $size, $post_id, $meta_key );
}

function output_responsive_styles() {
	$img = Image::get_instance();
	$img->output_responsive_styles();
}
