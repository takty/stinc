<?php
namespace st;
/**
 *
 * Date
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-18
 *
 */


const DATE_STRING_FORMAT = 'Y-m-d';


function create_date_from_date_string( $date_str ) {
	return date_create_from_format( DATE_STRING_FORMAT, $date_str );
}

function create_date_string_from_date( $date ) {
	return date_format( $date, DATE_STRING_FORMAT );
}

function create_date_array_from_date( $date ) {
	$date_string = create_date_string_from_date( $date );
	return create_date_array_from_date_string( $date_string );
}

function create_date_array_from_date_string( $date_str ) {
	if ( empty( $date_str ) ) return false;
	$da = explode( '-', $date_str );
	if ( count( $da ) !== 3 ) return false;
	return $da;
}


// -----------------------------------------------------------------------------


function create_date_string_of_today( $offset_year = 0, $offset_month = 0, $offset_day = 0 ) {
	if ( $offset_year === 0 && $offset_month === 0 && $offset_day === 0 ) {
		return date_i18n( \st\DATE_STRING_FORMAT );
	}
	$y = date( 'Y' ) + $offset_year;
	$m = date( 'm' ) + $offset_month;
	$d = date( 'd' ) + $offset_day;
	$od = mktime( 0, 0, 0, $m, $d, $y );  // The order must be month, day, and year!
	return date_i18n( \st\DATE_STRING_FORMAT, $od );
}

function create_date_array_of_today( $offset_year = 0, $offset_month = 0, $offset_day = 0 ) {
	$date_string = create_date_string_of_today( $offset_year, $offset_month, $offset_day );
	return 	explode( '-', $date_str );
}


// -----------------------------------------------------------------------------


function compare_today_with_date_string( $date_str ) {
	$ds = create_date_array_from_date_string( $date_str );
	if ( ! $ds  ) return false;
	$ts = explode( '-', date_i18n( DATE_STRING_FORMAT ) );
	return _compare_date( $ts, $ds );
}

function compare_date_arrays( $d1, $d2 ) {
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] === $d2[2] ) return '=';
	if ( $d1[0]  >  $d2[0] )                                           return '>';
	if ( $d1[0] === $d2[0] && $d1[1]  >  $d2[1] )                      return '>';
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2]  >  $d2[2] ) return '>';
	return '<';
}
