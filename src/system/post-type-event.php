<?php
namespace st\event;
/**
 *
 * Event Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-15
 *
 */


require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/../admin/list-table-column.php';
require_once __DIR__ . '/../admin/util.php';
require_once __DIR__ . '/../metabox/duration-picker.php';


const PMK_DATE_BGN = '_date_bgn';
const PMK_DATE_END = '_date_end';


function register_post_type( $post_type = 'event', $slug = false, $opts = [], $labels = [], $args = [] ) {
	$opts = array_merge( [
		'is_autofill_enabled'   => false,
		'order_by_date'         => 'begin',
		'date_replaced_by_date' => false
	], $opts );
	$labels = array_merge( [
		'name'               => 'Events',
		'period_label'       => '',
		'period_begin_label' => 'Begin',
		'period_end_label'   => 'End',
		'year_label'         => '',
	], $labels );
	$args = array_merge( [
		'labels'        => $labels,
		'public'        => true,
		'show_ui'       => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-calendar',
		'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
		'has_archive'   => true,
		'rewrite'       => false,
	], $args );

	if ( $slug === false ) $slug = $post_type;
	\register_post_type( $post_type, $args );
	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date' );

	$pmk_o = $opts['order_by_date'] === 'begin' ? PMK_DATE_BGN : ( $opts['order_by_date'] === 'end' ? PMK_DATE_END : false );
	if ( $pmk_o ) {
		\st\post_type\make_custom_date_sortable( $post_type, 'date', $pmk_o );
		\st\post_type\enable_custom_date_adjacent_post_link( $post_type, $pmk_o );
	}

	$pmk_d = $opts['date_replaced_by_date'] === 'begin' ? PMK_DATE_BGN : ( $opts['date_replaced_by_date'] === 'end' ? PMK_DATE_END : false );
	if ( $pmk_d ) {
		add_filter( 'get_the_date', function ( $the_date, $d, $post ) use ( $post_type, $pmk_d ) {
			if ( $post->post_type !== $post_type ) return $the_date;
			$date = get_post_meta( $post->ID, $pmk_d, true );
			return mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
		}, 10, 3 );
	}
	if ( is_admin() ) _set_duration_picker( $post_type, $opts, $labels );
}

function _set_duration_picker( $post_type, $opts, $labels ) {
	if ( \st\is_post_type( $post_type ) ) {
		add_action( 'admin_print_scripts', function () { \st\DurationPicker::enqueue_script(); } );
	}
	add_action( 'admin_menu', function () use ( $post_type, $labels, $opts ) {
		\st\DurationPicker::set_year_label( $labels['year_label'] );
		$dp = new \st\DurationPicker( '' );
		$dp->set_duration_labels( $labels['period_begin_label'], $labels['period_end_label'] );
		$dp->set_autofill_enabled( $opts['is_autofill_enabled'] );
		$dp->add_meta_box( $labels['period_label'], $post_type, 'side' );
	} );
	add_action( 'save_post', function ( $post_id ) {
		$dp = new \st\DurationPicker( '' );
		$dp->save_meta_box( $post_id );
	} );
}

function set_admin_columns( $post_type, $add_cat, $add_tag ) {
	add_action( 'wp_loaded', function () use ( $post_type, $add_cat, $add_tag )  {
		$cs = \st\list_table_column\insert_default_columns();
		$cs = \st\list_table_column\insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, -1, $cs );
		$cs = insert_date_columns( $post_type, -1, $cs );
		$cs = \st\list_table_column\insert_mh_tag_columns( $post_type, -1, $cs );
		$cs = \st\list_table_column\insert_ml_tag_columns( $post_type, -1, $cs );
		$scs = insert_date_sortable_columns();
		\st\list_table_column\set_admin_columns( $post_type, $cs, $scs );
	} );
}


// -----------------------------------------------------------------------------


function insert_date_columns( $post_type, $pos = false, $cs = [] ) {
	$pto = get_post_type_object( $post_type );
	$label_bgn = isset( $pto['labels']['period_begin_label'] ) ? $pto['labels']['period_begin_label'] : 'Begin';
	$label_end = isset( $pto['labels']['period_end_label'] ) ? $pto['labels']['period_end_label'] : 'End';
	$ns = [
		[ 'name' => PMK_DATE_BGN, 'label' => $label_bgn, 'width' => '15%', 'value' => '\st\event\_echo_date_val' ],
		[ 'name' => PMK_DATE_END, 'label' => $label_end, 'width' => '15%', 'value' => '\st\event\_echo_date_val' ]
	];
	if ( $pos === false ) return array_marge( $cs, $ns );
	return array_splice( $cs, $pos, 0, $ns );
}

function _echo_date_val( $val ) {
	if ( empty( $val ) ) return;
	echo esc_attr( date( get_option( 'date_format' ), strtotime( $val ) ) );
}

function insert_date_sortable_columns( $pos = false, $scs = [] ) {
	$ns = [ PMK_DATE_BGN, PMK_DATE_END ];
	if ( $pos === false ) return array_marge( $scs, $ns );
	return array_splice( $scs, $pos, 0, $ns );
}


// -----------------------------------------------------------------------------


function get_duration_tag( $post_id, $base_format, $main_date, $fmt_ymd, $fmt_md, $fmt_d ) {
	$ml  = \st\Multilang::get_instance();
	$dur = get_duration( $post_id );
	extract( $dur );

	$bgn = false;
	$end = false;
	if ( $bgn_nums && $end_nums ) {
		if ( $main_date === 'begin' ) {
			$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
			if ( $bgn_nums[0] !== $end_nums[0] ) {
				$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
			} else if ( $bgn_nums[1] !== $end_nums[1] ) {
				$end = _make_date_tags( $end_raw, $fmt_md, $base_format );
			} else if ( $bgn_nums[2] !== $end_nums[2] ) {
				$end = _make_date_tags( $end_raw, $fmt_d, $base_format );
			}
		} else if ( $main_date === 'end' ) {
			$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
			if ( $bgn_nums[0] !== $end_nums[0] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
			} else if ( $bgn_nums[1] !== $end_nums[1] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_md, $base_format );
			} else if ( $bgn_nums[2] !== $end_nums[2] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_d, $base_format );
			}
		}
	} else if ( $bgn_nums ) {
		$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
	} else if ( $end_nums ) {
		$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
	}
	return [ 'state' => $state, 'bgn' => $bgn, 'end' => $end ];
}

function get_duration( $post_id ) {
	$bgn_raw = get_post_meta( $post_id, PMK_DATE_BGN, true );
	$end_raw = get_post_meta( $post_id, PMK_DATE_END, true );
	$bgn_nums = empty( $bgn_raw ) ? false : explode( '-', $bgn_raw );
	$end_nums = empty( $end_raw ) ? false : explode( '-', $end_raw );
	$state = '';

	if ( ! $bgn_nums ) {
		$today = explode( '-', date_i18n( 'Y-m-d' ) );
		$today_bgn = _compare_date( $today, $bgn_nums );

		if ( ! $end_nums ) {
			$today_end = _compare_date( $today, $end_nums );
			if      ( $today_bgn === '<' ) $state = 'upcoming';
			else if ( $today_end === '>' ) $state = 'finished';
			else                           $state = 'ongoing';
		} else {
			switch ( $today_bgn ) {
			case '=': $state = 'ongoing';  break;
			case '>': $state = 'finished'; break;
			case '<': $state = 'upcoming'; break;
			}
		}
	}
	return compact( 'state', 'bgn_raw', 'end_raw', 'bgn_nums', 'end_nums' );
}

function _compare_date( $d1, $d2 ) {
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] === $d2[2] ) return '=';
	if ( $d1[0]  >  $d2[0] )                                           return '>';
	if ( $d1[0] === $d2[0] && $d1[1]  >  $d2[1] )                      return '>';
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2]  >  $d2[2] ) return '>';
	return '<';
}

function _make_date_tags( $date, $format, $base_format = false ) {
	if ( $base_format === false ) {
		$base_format = "Y\tM\tj";
		if ( class_exists( '\st\Multilang' ) ) {
			$ml = \st\Multilang::get_instance();
			$f = $ml->get_date_format();
			if ( strpos( $f, 'm' ) !== false || strpos( $f, 'n' ) !== false ) $base_format = "Y\tn\tj";
		}
	}
	if ( ! empty( $date ) ) {
		$date = date_create_from_format( 'Y-m-d', $date );
		$ds = explode( "\t", date_format( $date, $base_format ) );
	} else {
		$ds = [ '?', '?', '?', '?' ];
	}
	$temp = str_replace( [ '%0', '%1', '%2', '%3' ], $ds, $format );
	return str_replace( [ '%year%', '%month%', '%day%', '%week%' ], $ds, $temp );
}
