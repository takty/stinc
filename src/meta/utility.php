<?php
/**
 * Custom Field Utilities
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-10-05
 */

namespace wpinc\meta;

/**
 * Retrieves a post meta field as date.
 *
 * @param int         $post_id Post ID.
 * @param string      $key     The meta key to retrieve.
 * @param string|null $format  Date format.
 * @return string Date string.
 */
function get_post_meta_date( int $post_id, string $key, string $format = null ) {
	if ( null === $format ) {
		$format = get_option( 'date_format' );
	}
	$val = mb_trim( get_post_meta( $post_id, $key, true ) );
	$val = mysql2date( $format, $val );
	return $val;
}

/**
 * Retrieves a post meta field as multiple lines.
 *
 * @param int    $post_id Post ID.
 * @param string $key     The meta key to retrieve.
 * @return string[] Lines.
 */
function get_post_meta_lines( int $post_id, string $key ): array {
	$val  = mb_trim( get_post_meta( $post_id, $key, true ) );
	$vals = explode( "\n", $val );
	$vals = array_map( '\wpinc\meta\mb_trim', $vals );
	$vals = array_filter(
		$vals,
		function ( $e ) {
			return ! empty( $e );
		}
	);
	$vals = array_values( $vals );
	return $vals;
}


// -----------------------------------------------------------------------------


/**
 * Stores a post meta field.
 *
 * @param int           $post_id Post ID.
 * @param string        $key     Metadata key.
 * @param callable|null $filter  Filter.
 * @param mixed|null    $default Default value.
 */
function set_post_meta( int $post_id, string $key, ?callable $filter = null, $default = null ): void {
	$val = $_POST[ $key ] ?? null;  // phpcs:ignore
	if ( null !== $filter && null !== $val ) {
		$val = $filter( $val );
	}
	if ( empty( $val ) ) {
		if ( null === $default ) {
			delete_post_meta( $post_id, $key );
			return;
		}
		$val = $default;
	}
	update_post_meta( $post_id, $key, $val );
}

/**
 * Stores a post meta field after applying filters.
 *
 * @param int         $post_id     Post ID.
 * @param string      $key         Metadata key.
 * @param string|null $filter_name Filter name.
 * @param mixed|null  $default     Default value.
 */
function set_post_meta_with_wp_filter( int $post_id, string $key, ?string $filter_name = null, $default = null ): void {
	$val = $_POST[ $key ] ?? null;  // phpcs:ignore
	if ( null !== $filter_name && null !== $val ) {
		$val = apply_filters( $filter_name, $val );
	}
	if ( empty( $val ) ) {
		if ( null === $default ) {
			delete_post_meta( $post_id, $key );
			return;
		}
		$val = $default;
	}
	update_post_meta( $post_id, $key, $val );
}


// -----------------------------------------------------------------------------


/**
 * Trims string.
 *
 * @param string $str String.
 * @return string Trimmed string.
 */
function mb_trim( string $str ): string {
	return preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $str );
}

/**
 * Outputs name and id attributes.
 *
 * @param string $key Key.
 */
function name_id( string $key ): void {
	echo 'name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '"';
}

/**
 * Normalizes date string.
 *
 * @param string $str String representing date.
 * @return string Normalized string.
 */
function normalize_date( string $str ): string {
	$str  = mb_convert_kana( $str, 'n', 'utf-8' );
	$nums = preg_split( '/\D/', $str );
	$vals = array();
	foreach ( $nums as $num ) {
		$v = (int) trim( $num );
		if ( 0 !== $v ) {
			$vals[] = $v;
		}
	}
	if ( 3 <= count( $vals ) ) {
		$str = sprintf( '%04d-%02d-%02d', $vals[0], $vals[1], $vals[2] );
	} elseif ( count( $vals ) === 2 ) {
		$str = sprintf( '%04d-%02d', $vals[0], $vals[1] );
	} elseif ( count( $vals ) === 1 ) {
		$str = sprintf( '%04d', $vals[0] );
	}
	return $str;
}

/**
 * Normalizes YouTube video ID.
 *
 * @param string $str String of YouTube video ID.
 * @return string Normalized YouTube video ID.
 */
function normalize_youtube_video_id( string $str ): string {
	$pu = wp_parse_url( trim( $str ) );
	if ( $pu ) {
		if ( 'youtu.be' === $pu['host'] ) {
			return trim( $pu['path'], '/' );
		}
		if ( 'www.youtube.com' === $pu['host'] ) {
			$q  = $pu['query'];
			$qs = explode( '&', $q );

			foreach ( $qs as $qp ) {
				list( $key, $val ) = explode( '=', $qp );
				if ( 'v' === $key ) {
					return $val;
				}
			}
		}
	}
	return $str;
}
