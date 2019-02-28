<?php
namespace st;

/**
 *
 * Event Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-28
 *
 */


require_once __DIR__ . '/../admin/duration-picker.php';
require_once __DIR__ . '/../admin/page-template-admin.php';
require_once __DIR__ . '/post-type.php';


class EventPostType {

	const KEY_BGN   = '_date_bgn';
	const KEY_END   = '_date_end';
	const SLUG_DATE = 'date';

	static private $_instance   = [];

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new EventPostType( $key );
	}

	static private function _compare_date( $d1, $d2 ) {
		if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] === $d2[2] ) return '=';
		if ( $d1[0]  >  $d2[0] )                                           return '>';
		if ( $d1[0] === $d2[0] && $d1[1]  >  $d2[1] )                      return '>';
		if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2]  >  $d2[2] ) return '>';
		return '<';
	}

	static private function _make_date_tags( $date, $format, $base_format = false ) {
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
			$ds = ['?', '?', '?'];
		}
		return str_replace( [ '%year%', '%month%', '%day%' ], $ds, $format );
	}

	static public function _echo_date_vals( $vals ) {  // Private
		if ( empty( $vals ) ) return;
		echo esc_attr( date( get_option( 'date_format' ), strtotime( $vals ) ) );
	}

	private $_post_type;
	private $_label_post_type = '';
	private $_label_meta_box = '';

	private $_duration_picker;
	private $_locale     = 'en';
	private $_label_year = '';
	private $_label_bgn = 'Begin';
	private $_label_end = 'End';
	private $_is_autofill_enabled = false;

	private $_order_key = self::KEY_BGN;
	private $_date_replaced = false;

	public function __construct( $post_type = 'event' ) {
		$this->_post_type = $post_type;
		self::$_instance[ $post_type ] = $this;
		if ( is_admin() ) $this->_duration_picker = new \st\DurationPicker( '' );
	}

	public function set_post_type_label( $label ) {
		$this->_label_post_type = $label;
		return $this;
	}

	public function set_meta_box_label( $label ) {
		$this->_label_meta_box = $label;
		return $this;
	}

	public function set_calendar_locale( $locale ) {
		$this->_locale = $locale;
		return $this;
	}

	public function set_year_label( $label ) {
		$this->_label_year = $label;
		return $this;
	}

	public function set_duration_labels( $bgn, $end ) {
		if ( $bgn ) $this->_label_bgn = $bgn;
		if ( $end ) $this->_label_end = $end;
		return $this;
	}

	public function set_autofill_enabled( $enabled ) {
		$this->_is_autofill_enabled = $enabled;
		return $this;
	}

	public function set_order_key( $type ) {
		if ( $type === 'begin' ) $this->_order_key = self::KEY_BGN;
		if ( $type === 'end' )   $this->_order_key = self::KEY_END;
		return $this;
	}

	public function replace_post_date_by( $type ) {
		if ( $type === 'begin' ) $this->_date_replaced = self::KEY_BGN;
		if ( $type === 'end' )   $this->_date_replaced = self::KEY_END;
		return $this;
	}

	public function register( $args = [] ) {
		$args = array_merge( [
			'label'         => $this->_label_post_type,
			'public'        => true,
			'show_ui'       => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-calendar',
			'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
			'has_archive'   => true,
			'rewrite'       => false,
		], $args );
		register_post_type( $this->_post_type, $args );

		\st\post_type\add_rewrite_rules( $this->_post_type );
		\st\post_type\make_custom_date_sortable( $this->_post_type, self::SLUG_DATE, $this->_order_key );
		\st\post_type\enable_custom_date_adjacent_post_link( $this->_post_type, $this->_order_key );

		add_filter( 'get_the_date', [ $this, '_cb_get_the_date' ], 10, 3 );
		if ( is_admin() ) {
			if ( \st\page_template_admin\is_post_type( $this->_post_type ) ) {
				add_action( 'admin_print_scripts', function () { \st\DurationPicker::enqueue_script(); } );
			}
			add_action( 'admin_menu', [ $this, '_cb_admin_menu' ] );
			add_action( 'save_post', [ $this, '_cb_save_post' ] );
		}
	}


	// -----------------------------------------------------------------------------


	public function _cb_get_the_date( $the_date, $d, $post ) {  // Private
		if ( $this->_date_replaced !== false && $post->post_type === $this->_post_type ) {
			$date = get_post_meta( $post->ID, $this->_date_replaced, true );
			$the_date = mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
		}
		return $the_date;
	}

	public function _cb_admin_menu() {  // Private
		\st\DurationPicker::set_calendar_locale( $this->_locale );
		\st\DurationPicker::set_year_label( $this->_label_year );
		$this->_duration_picker->set_duration_labels( $this->_label_bgn, $this->_label_end );
		$this->_duration_picker->set_autofill_enabled( $this->_is_autofill_enabled );
		$this->_duration_picker->add_meta_box( $this->_label_meta_box, $this->_post_type, 'side' );
	}

	public function _cb_save_post( $post_id ) {  // Private
		$this->_duration_picker->save_meta_box( $post_id );
	}


	// -----------------------------------------------------------------------------


	public function set_admin_columns( $all_columns, $sortable_columns ) {
		$cs = [];
		foreach ( $all_columns as $c ) {
			if ( $c === 'date_bgn' ) {
				$cs[] = ['name' => self::KEY_BGN, 'label' => $this->_label_bgn, 'width' => '15%', 'value' => [ '\st\EventPostType', '_echo_date_vals' ] ];
			} else if ( $c === 'date_end' ) {
				$cs[] = ['name' => self::KEY_END, 'label' => $this->_label_end, 'width' => '15%', 'value' => [ '\st\EventPostType', '_echo_date_vals' ] ];
			} else {
				$cs[] = $c;
			}
		}
		$scs = [];
		foreach ( $sortable_columns as $c ) {
			if      ( $c === 'date_bgn' ) $scs[] = self::KEY_BGN;
			else if ( $c === 'date_end' ) $scs[] = self::KEY_END;
			else                          $scs[] = $c;
		}
		\st\field\set_admin_columns( $this->_post_type, $cs, $scs );
	}

	public function get_date_strings( $post_id, $tab_separated_format = "Y\tm\td" ) {
		$date_bgn = get_post_meta( $post_id, self::KEY_BGN, true );
		$date_end = get_post_meta( $post_id, self::KEY_END, true );

		if ( ! empty( $date_bgn ) ) {
			$val_bgn = date_create_from_format( 'Y-m-d', $date_bgn );
			$ds_bgn = explode( "\t", date_format( $val_bgn, $tab_separated_format ) );
		} else {
			$ds_bgn = false;
		}
		if ( ! empty( $date_end ) ) {
			$val_end = date_create_from_format( 'Y-m-d', $date_end );
			$ds_end = explode( "\t", date_format( $val_end, $tab_separated_format ) );
		} else {
			$ds_end = false;
		}
		$lic = '';
		if ( ! empty( $date_bgn ) || ! empty( $date_end ) ) {
			if ( empty( $date_bgn ) ) $date_bgn = $date_end;
			if ( empty( $date_end ) ) $date_end = $date_bgn;

			$today = intval( date_i18n( 'Ymd' ) );
			$bgn = intval( str_replace( '-', '', $date_bgn ) );
			$end = intval( str_replace( '-', '', $date_end ) );
			if ( $today < $bgn ) $lic = ' upcoming';
			else if ( $end < $today ) $lic = ' finished';
			else $lic = ' ongoing';
		}
		return [ $lic, $ds_bgn, $ds_end ];
	}

	public function get_date_tags( $post_id, $raw = false, $format = "<span>%year%</span><span>%month%</span><span>%day%</span>", $base_format = false ) {
		$date_bgn = get_post_meta( $post_id, self::KEY_BGN, true );
		$date_end = get_post_meta( $post_id, self::KEY_END, true );
		$lic = '';

		if ( ! empty( $date_bgn ) ) {
			$today = explode( '-', date_i18n( 'Y-m-d' ) );
			$today_bgn = self::_compare_date( $today, explode( '-', $date_bgn ) );

			if ( ! empty( $date_end ) ) {
				$today_end = self::_compare_date( $today, explode( '-', $date_end ) );
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
		return [
			$lic,
			self::_make_date_tags( $date_bgn, $format, $base_format ),
			self::_make_date_tags( $date_end, $format, $base_format )
		];
	}

}


// -----------------------------------------------------------------------------


namespace st\event;

function register_event_post_type( $labels, $calendar_locale, $args = [] ) {
	$instance = new \st\EventPostType();
	$instance->set_post_type_label( $labels['type_label'] );
	$instance->set_meta_box_label( $labels['period_label'] );
	$instance->set_calendar_locale( $calendar_local );
	if ( isset( $labels['year_label'] ) ) $instance->set_year_label( $labels['year_label'] );
	$instance->set_duration_labels( $labels['period_begin_label'], $labels['period_end_label'] );
	$instance->register( $args );
}

function set_admin_columns( $all_columns, $sortable_columns ) {
	\st\EventPostType::get_instance()->set_admin_columns( $all_columns, $sortable_columns );
}

function get_event_date_tags( $post_id, $raw = false, $format = "<span>%year%</span><span>%month%</span><span>%day%</span>" ) {
	return \st\EventPostType::get_instance()->get_date_tags( $post_id, $raw, $format );
}
