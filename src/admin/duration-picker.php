<?php
namespace st;

/**
 *
 * Duration Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-24
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/url.php';


class DurationPicker {

	const NS = 'st-duration-picker';

	const CLS_TABLE      = self::NS . '-table';

	static private $_instance   = [];
	static private $_locale     = 'en';
	static private $_label_year = '';
	static private $_is_echo_script = false;

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new DurationPicker( $key );
	}

	static public function enqueue_script( $url_to = false ) {
		if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( 'flatpickr',         $url_to . '/asset/lib/flatpickr.min.js' );
			wp_enqueue_style ( 'flatpickr',         $url_to . '/asset/lib/flatpickr.min.css' );
			wp_enqueue_script( 'flatpickr.l10n.ja', $url_to . '/asset/lib/flatpickr.l10n.ja.min.js' );
			wp_enqueue_style(  self::NS, $url_to . '/asset/duration-picker.min.css' );
			wp_enqueue_script( self::NS, $url_to . '/asset/duration-picker.min.js' );
		}
	}

	static public function set_calendar_locale( $locale ) {
		self::$_locale = $locale;
	}

	static public function set_year_label( $label ) {
		self::$_label_year = $label;
	}

	private $_key;
	private $_id;

	private $_label_bgn = 'Begin';
	private $_label_end = 'End';

	public function __construct( $key ) {
		$this->_key = $key;
		$this->_id  = $key;
		self::$_instance[ $key ] = $this;
	}

	public function set_duration_labels( $bgn, $end ) {
		if ( $bgn ) $this->_label_bgn = $bgn;
		if ( $end ) $this->_label_end = $end;
		return $this;
	}

	public function get_item( $post_id = false ) {
		if ( $post_id === false ) $post_id = get_the_ID();

		$date_bgn = get_post_meta( $post_id, "{$this->_key}_date_bgn", true );
		$date_end = get_post_meta( $post_id, "{$this->_key}_date_end", true );

		return compact( 'date_bgn', 'date_end' );
	}


	// -----------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'side' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST["{$this->_key}_nonce"] ) ) return;
		if ( ! wp_verify_nonce( $_POST["{$this->_key}_nonce"], $this->_key ) ) return;
		$this->_save_item( $post_id );
	}


	// -----------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$it = $this->get_item( $post->ID );

		$_locale     = esc_html( self::$_locale );
		$_label_year = esc_html( self::$_label_year );
		$_label_bgn  = esc_html( $this->_label_bgn );
		$_label_end  = esc_html( $this->_label_end );

		$id_bgn = "{$this->_key}_date_bgn";
		$id_end = "{$this->_key}_date_end";
		$id_row_bgn = "{$this->_key}_row_date_bgn";
		$id_row_end = "{$this->_key}_row_date_end";

		$_bgn = isset( $it['date_bgn'] ) ? esc_attr( $it['date_bgn'] ) : '';
		$_end = isset( $it['date_end'] ) ? esc_attr( $it['date_end'] ) : '';
	?>
		<div>
			<table class="<?php echo self::CLS_TABLE ?>">
				<tr>
					<td><?php echo $_label_bgn; ?>: </td>
					<td class="flatpickr input-group" id="<?php echo $id_row_bgn ?>">
						<input type="text" <?php \st\field\esc_key_e( $id_bgn ) ?> size="12" value="<?php echo $_bgn; ?>" data-input />
						<a class="button" title="clear" data-clear>X</a>
					</td>
				</tr>
				<tr>
					<td><?php echo $_label_end; ?>: </td>
					<td class="flatpickr input-group" id="<?php echo $id_row_end ?>">
						<input type="text" <?php \st\field\esc_key_e( $id_end ) ?> size="12" value="<?php echo $_end; ?>" data-input />
						<a class="button" title="clear" data-clear>X</a>
					</td>
				</tr>
			</table>
			<script>
				flatpickr('#<?php echo $id_row_bgn ?>', { locale: '<?php echo $_locale ?>', wrap: true });
				flatpickr('#<?php echo $id_row_end ?>', { locale: '<?php echo $_locale ?>', wrap: true });
	<?php if ( ! empty( $_label_year ) && ! self::$_is_echo_script ) : self::$_is_echo_script = true; ?>
				st_duration_picker_initialize_admin('<?php echo $_label_year; ?>');
	<?php endif; ?>
			</script>
		</div>
	<?php
	}



	// -----------------------------------------------------------------------------


	private function _save_item( $post_id ) {
		$key_bgn = "{$this->_key}_date_bgn";
		$key_end = "{$this->_key}_date_end";

		$date_bgn = isset( $_POST[ $key_bgn ] ) ? $_POST[ $key_bgn ] : false;
		$date_end = isset( $_POST[ $key_end ] ) ? $_POST[ $key_end ] : false;

		if ( $date_bgn && $date_end ) {
			$date_bgn_val = (int) str_replace( '-', '', $date_bgn );
			$date_end_val = (int) str_replace( '-', '', $date_end );
			if ( $date_end_val < $date_bgn_val ) list( $date_bgn, $date_end ) = [ $date_end, $date_bgn ];
		}

		if ( $date_bgn ) update_post_meta( $post_id, $key_bgn, $date_bgn );
		else delete_post_meta( $post_id, $key_bgn );

		if ( $date_end ) update_post_meta( $post_id, $key_end, $date_end );
		else delete_post_meta( $post_id, $key_end, $date_end );
	}

}


// -----------------------------------------------------------------------------


namespace st\duration_picker;

function initialize( $key ) { return new \st\DurationPicker( $key ); }
function enqueue_script( $url_to = false ) { \st\DurationPicker::enqueue_script( $url_to ); }
function set_calendar_locale( $locale ) { return \st\DurationPicker::set_calendar_locale( $locale ); }
function set_year_label( $label ) { return \st\DurationPicker::set_year_label( $label ); }

function get_item( $key, $post_id = false ) { return \st\DurationPicker::get_instance( $key )->get_item( $post_id ); }
function set_duration_labels( $key, $bgn, $end ) { return \st\DurationPicker::get_instance( $key )->set_duration_labels( $bgn, $end ); }

function add_meta_box( $key, $label, $screen, $context = 'side', $opts = [] ) {
	if ( isset( $opts['calendar_locale'] ) ) set_calendar_locale( $opts['calendar_locale'] );
	if ( isset( $opts['year_label'] ) ) set_year_label( $opts['year_label'] );
	if ( isset( $opts['date_bgn_label'] ) ) set_duration_labels( $key, $opts['date_bgn_label'], false );
	if ( isset( $opts['date_end_label'] ) ) set_duration_labels( $key, false, $opts['date_end_label'] );
	\st\DurationPicker::get_instance( $key )->add_meta_box( $label, $screen, $context );
}
function save_meta_box( $post_id, $key ) { \st\DurationPicker::get_instance( $key )->save_meta_box( $post_id ); }
