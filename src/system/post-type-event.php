<?php
/**
 * Event Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st\event;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/../admin/list-table-column.php';
require_once __DIR__ . '/../admin/misc.php';
require_once __DIR__ . '/../metabox/duration-picker.php';
require_once __DIR__ . '/../util/date.php';


const PMK_DATE_BGN = '_date_bgn';
const PMK_DATE_END = '_date_end';


function register_post_type( $post_type = 'event', $slug = false, $opts = array(), $labels = array(), $args = array(), ?callable $home_url = null ) {
	$opts = array_merge(
		array(
			'is_autofill_enabled'   => false,
			'order_by_date'         => 'begin',
			'date_replaced_by_date' => false,
		),
		$opts
	);
	$labels = array_merge(
		array(
			'name'               => 'Events',
			'period_label'       => 'Date',
			'period_begin_label' => 'Begin',
			'period_end_label'   => 'End',
			'year_label'         => '',
		),
		$labels
	);
	$args = array_merge(
		array(
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-calendar-alt',
			'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
			'has_archive'   => true,
			'rewrite'       => false,
		),
		$args
	);
	if ( $slug === false ) {
		$slug = $post_type;
	}
	\register_post_type( $post_type, $args );
	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date', false, $home_url );

	$pmk_o = ( 'begin' === $opts['order_by_date'] ) ? PMK_DATE_BGN : ( ( 'end' === $opts['order_by_date'] ) ? PMK_DATE_END : false );
	if ( $pmk_o ) {
		\st\post_type\make_custom_date_sortable( $post_type, 'date', $pmk_o );
		\st\post_type\enable_custom_date_adjacent_post_link( $post_type, $pmk_o );
	}

	$pmk_d = ( 'begin' === $opts['date_replaced_by_date'] ) ? PMK_DATE_BGN : ( ( 'end' === $opts['date_replaced_by_date'] ) ? PMK_DATE_END : false );
	if ( $pmk_d ) {
		add_filter(
			'get_the_date',
			function ( $the_date, $d, $post ) use ( $post_type, $pmk_d ) {
				if ( $post->post_type !== $post_type ) {
					return $the_date;
				}
				$date = get_post_meta( $post->ID, $pmk_d, true );
				return mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
			},
			10,
			3
		);
	}
	if ( is_admin() ) {
		_set_duration_picker( $post_type, $opts, $labels );
	}
}

function _set_duration_picker( $post_type, $opts, $labels ) {
	if ( \st\is_post_type( $post_type ) ) {
		add_action(
			'admin_print_scripts',
			function () {
				\st\DurationPicker::enqueue_script();
			}
		);
	}
	add_action(
		'admin_menu',
		function () use ( $post_type, $labels, $opts ) {
			\st\DurationPicker::set_year_label( $labels['year_label'] );
			$dp = \st\DurationPicker::get_instance( '' );
			$dp->set_duration_labels( $labels['period_begin_label'], $labels['period_end_label'] );
			$dp->set_autofill_enabled( $opts['is_autofill_enabled'] );
			$dp->add_meta_box( $labels['period_label'], $post_type, 'side' );
		}
	);
	add_action(
		'save_post',
		function ( $post_id ) {
			$dp = \st\DurationPicker::get_instance( '' );
			$dp->save_meta_box( $post_id );
		}
	);
}

function set_admin_columns( $post_type, $add_cat, $add_tag, $tax ) {
	add_action(
		'wp_loaded',
		function () use ( $post_type, $add_cat, $add_tag, $tax ) {
			$cs = \st\list_table_column\insert_default_columns();
			$cs = \st\list_table_column\insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, -1, $cs );
			$cs = insert_date_columns( $post_type, -1, $cs );
			array_splice( $cs, -1, 0, array( array( 'name' => $tax, 'width' => '10%' ) ) );
			$scs = insert_date_sortable_columns();
			\st\list_table_column\set_admin_columns( $post_type, $cs, $scs );
		}
	);
}


// -----------------------------------------------------------------------------


function insert_date_columns( $post_type, $pos = false, $cs = array() ) {
	$pto       = get_post_type_object( $post_type );
	$label_bgn = isset( $pto->labels->period_begin_label ) ? $pto->labels->period_begin_label : __( 'Begin' );
	$label_end = isset( $pto->labels->period_end_label ) ? $pto->labels->period_end_label : __( 'End' );
	$ns = array(
		array(
			'name'  => PMK_DATE_BGN,
			'label' => $label_bgn,
			'width' => '15%',
			'value' => '\st\event\_echo_date_val'
		),
		array(
			'name'  => PMK_DATE_END,
			'label' => $label_end,
			'width' => '15%',
			'value' => '\st\event\_echo_date_val'
		),
	);
	if ( false === $pos ) {
		return array_merge( $cs, $ns );
	}
	array_splice( $cs, $pos, 0, $ns );
	return $cs;
}

function _echo_date_val( $val ) {
	if ( empty( $val ) ) {
		return;
	}
	echo esc_attr( date( get_option( 'date_format' ), strtotime( $val ) ) );
}

function insert_date_sortable_columns( $pos = false, $scs = array() ) {
	$ns = array( PMK_DATE_BGN, PMK_DATE_END );
	if ( false === $pos ) {
		return array_merge( $scs, $ns );
	}
	array_splice( $scs, $pos, 0, $ns );
	return $scs;
}


// -----------------------------------------------------------------------------


function get_duration_tag( $post_id, $base_format, $main_date, $fmt_ymd, $fmt_md, $fmt_d ) {
	$dur = get_duration( $post_id );
	extract( $dur );

	$bgn = false;
	$end = false;
	if ( $bgn_nums && $end_nums ) {
		if ( 'begin' === $main_date ) {
			$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
			if ( $bgn_nums[0] !== $end_nums[0] ) {
				$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
			} elseif ( $bgn_nums[1] !== $end_nums[1] ) {
				$end = _make_date_tags( $end_raw, $fmt_md, $base_format );
			} elseif ( $bgn_nums[2] !== $end_nums[2] ) {
				$end = _make_date_tags( $end_raw, $fmt_d, $base_format );
			}
		} elseif ( 'end' === $main_date ) {
			$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
			if ( $bgn_nums[0] !== $end_nums[0] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
			} elseif ( $bgn_nums[1] !== $end_nums[1] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_md, $base_format );
			} elseif ( $bgn_nums[2] !== $end_nums[2] ) {
				$bgn = _make_date_tags( $bgn_raw, $fmt_d, $base_format );
			}
		}
	} elseif ( $bgn_nums ) {
		$bgn = _make_date_tags( $bgn_raw, $fmt_ymd, $base_format );
	} elseif ( $end_nums ) {
		$end = _make_date_tags( $end_raw, $fmt_ymd, $base_format );
	}
	return array(
		'state' => $state,
		'bgn'   => $bgn,
		'end'   => $end,
	);
}

function get_duration( $post_id ) {
	$bgn_raw  = get_post_meta( $post_id, PMK_DATE_BGN, true );
	$end_raw  = get_post_meta( $post_id, PMK_DATE_END, true );
	$bgn_nums = empty( $bgn_raw ) ? false : explode( '-', $bgn_raw );
	$end_nums = empty( $end_raw ) ? false : explode( '-', $end_raw );
	$state    = '';

	if ( false !== $bgn_nums ) {
		$today     = \st\create_date_array_of_today();
		$today_bgn = \st\compare_date_arrays( $today, $bgn_nums );

		if ( false !== $end_nums ) {
			$today_end = \st\compare_date_arrays( $today, $end_nums );
			if ( '<' === $today_bgn ) {
				$state = 'upcoming';
			} elseif ( '>' === $today_end ) {
				$state = 'finished';
			} else {
				$state = 'ongoing';
			}
		} else {
			switch ( $today_bgn ) {
				case '=':
					$state = 'ongoing';
					break;
				case '>':
					$state = 'finished';
					break;
				case '<':
					$state = 'upcoming';
					break;
			}
		}
	}
	return compact( 'state', 'bgn_raw', 'end_raw', 'bgn_nums', 'end_nums' );
}

function _make_date_tags( $date_str, $format, $base_format = false ) {
	if ( false === $base_format ) {
		$base_format = "Y\tM\tj";

		$f = get_option( 'date_format' );
		if ( strpos( $f, 'm' ) !== false || strpos( $f, 'n' ) !== false ) {
			$base_format = "Y\tn\tj";
		}
	}
	if ( ! empty( $date_str ) ) {
		$date = \st\create_date_from_date_string( $date_str );
		if ( strpos( $base_format, 'x' ) !== false ) {
			$yi          = date_format( $date, 'w' );
			$yobis       = array( '日', '月', '火', '水', '木', '金', '土' );
			$yobi        = $yobis[ $yi ];
			$base_format = str_replace( 'x', $yobi, $base_format );
		}
		$ds = explode( "\t", date_format( $date, $base_format ) );
	} else {
		$ds = [ '?', '?', '?', '?' ];
	}
	$temp = str_replace( array( '%0', '%1', '%2', '%3' ), $ds, $format );
	return str_replace( array( '%year%', '%month%', '%day%', '%week%' ), $ds, $temp );
}
