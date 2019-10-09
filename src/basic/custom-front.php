<?php
namespace st\basic;
/**
 *
 * Custom Front
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
 *
 */


function remove_single_title_indication( $protected, $private ) {
	if ( $protected ) {
		add_filter( 'protected_title_format', function ( $prepend ) {
			if ( ! is_single() ) return $prepend;
			return '%s';
		} );
	}
	if ( $private ) {
		add_filter( 'private_title_format', function ( $prepend ) {
			if ( ! is_single() ) return $prepend;
			return '%s';
		} );
	}
}


// -----------------------------------------------------------------------------


function remove_archive_title_text() {
	add_filter( 'get_the_archive_title', function ( $title ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_year() ) {
			$title = get_the_date( 'Y' );
		} elseif ( is_month() ) {
			$title = get_the_date( 'Y-m' );
		} elseif ( is_day() ) {
			$title = get_the_date( 'Y-m-d' );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		}
		return $title;
	} );
}

function remove_separator_in_title_and_description() {
	add_filter( 'bloginfo', function ( $output, $show ) {
		if ( $show === 'description' || $show === 'name' || $show === '' ) {
			return implode( ' ', \st\separate_line( $output ) );
		}
		return $output;
	}, 10, 2 );
	add_filter( 'document_title_parts', function ( $title ) {
		$title['title'] = implode( ' ', \st\separate_line( $title['title'] ) );
		return $title;
	} );
}
