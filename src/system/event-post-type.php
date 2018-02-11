<?php
namespace st\event;

/**
 *
 * Event Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-01-15
 *
 */


require_once __DIR__ . '/../admin/duration-picker.php';


function register_event_post_type( $labels, $calendar_locale, $args = [] ) {
	$args = array_merge( [
		'label'         => $labels['type_label'],
		'public'        => true,
		'show_ui'       => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-calendar',
		'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
		'has_archive'   => true,
		'rewrite'       => false,
	], $args );
	register_post_type( 'event', $args );
	\st\post_type\add_rewrite_rules( 'event' );
	\st\post_type\make_custom_date_sortable( 'event', 'date', '_date_bgn' );
	\st\post_type\enable_custom_date_adjacent_post_link( 'event', '_date_bgn' );
	_add_filter_for_replace_date();

	add_action( 'admin_print_scripts', function () {
		\st\duration_picker\enqueue_script_for_admin( get_template_directory_uri() . '/lib/stinc/admin' );
	} );
	add_action( 'admin_menu', function () use ( $labels, $calendar_locale ) {
		$opts = [
			'calendar_locale' => $calendar_locale,
			'date_bgn_label'  => $labels['period_begin_label'],
			'date_end_label'  => $labels['period_end_label'],
			'year_label'      => isset( $labels['year_label'] ) ? $labels['year_label'] : ''
		];
		\st\duration_picker\add_meta_box( '', $labels['period_label'], 'event', 'side', $opts );
	} );
	add_action( 'save_post', function ( $post_id ) {
		\st\duration_picker\save_meta_box( $post_id, '' );
	} );
}

function _add_filter_for_replace_date() {
	add_filter( 'get_the_date', function ( $the_date, $d, $post ) {
		if ( $post->post_type === 'event' ) {
			$date = get_post_meta( $post->ID, '_date_bgn', true );
			$the_date = mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
		}
		return $the_date;
	}, 10, 3 );
}

function set_admin_columns( $all_columns, $sortable_columns ) {
	function echo_date_vals( $vals ) {
		echo esc_attr( date( get_option( 'date_format' ), strtotime( $vals ) ) );
	}
	$cs = [];
	foreach ( $all_columns as $c ) {
		if ( $c === 'date_bgn' ) {
			$cs[] = ['name' => '_date_bgn', 'label' => '開始', 'width' => '15%', 'value' => '\st\event\echo_date_vals'];
		} else if ( $c === 'date_end' ) {
			$cs[] = ['name' => '_date_end', 'label' => '終了', 'width' => '15%', 'value' => '\st\event\echo_date_vals'];
		} else {
			$cs[] = $c;
		}
	}
	$scs = [];
	foreach ( $sortable_columns as $c ) {
		if ( $c === 'date_bgn' )      $scs[] = '_date_bgn';
		else if ( $c === 'date_end' ) $scs[] = '_date_end';
		else                          $scs[] = $c;
	}
	\st\field\set_admin_columns( 'event', $cs, $scs );
}

function get_event_date_tags( $post_id, $raw = false, $format = "<span>%year%</span><span>%month%</span><span>%day%</span>" ) {
	$date_bgn = get_post_meta( $post_id, '_date_bgn', true );
	$date_end = get_post_meta( $post_id, '_date_end', true );
	$lic = '';
	if ( ! empty( $date_bgn ) ) {
		$today = explode( '-', date_i18n( 'Y-m-d' ) );
		$today_bgn = compare_date( $today, explode( '-', $date_bgn ) );

		if ( ! empty( $date_end ) ) {
			$today_end = compare_date( $today, explode( '-', $date_end ) );
			if      ( $today_bgn === '<' ) $lic = ' upcoming';
			else if ( $today_end === '>' ) $lic = ' finished';
			else                           $lic = ' ongoing';
		} else {
			switch ( $today_bgn ) {
			case '=': $lic = ' ongoing';  break;
			case '>': $lic = ' finished'; break;
			case '<': $lic = ' upcoming'; break;
			}
		}
	}
	if ( $raw ) return [ $lic, $date_bgn, $date_end ];
	return [ $lic, make_date_tags( $date_bgn, $format ), make_date_tags( $date_end, $format ) ];
}

function compare_date( $d1, $d2 ) {
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] === $d2[2] ) {
		return '=';
	}
	if (
		  $d1[0]  >  $d2[0] ||
		( $d1[0] === $d2[0] && $d1[1]  >  $d2[1] ) ||
		( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] > $d2[2] )
	) {
		return '>';
	}
	return '<';
}

function make_date_tags( $date, $format ) {
	$is_ja = false;
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		$is_ja = $ml->is_site_lang( 'ja' );
	}
	if ( ! empty( $date ) ) {
		$date = date_create_from_format( 'Y-m-d', $date );
		$ds = explode( "\t", date_format( $date, $is_ja ? "Y\tn\tj" : "Y\tM\tj" ) );
	} else {
		$ds = ['?', '?', '?'];
	}
	$tags = str_replace( '%year%',  $ds[0], $format );
	$tags = str_replace( '%month%', $ds[1], $tags );
	$tags = str_replace( '%day%',   $ds[2], $tags );
	return $tags;
}
