<?php
namespace st;

/**
 *
 * Media Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-06-08
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/url.php';


class MediaPicker {

	const NS = 'st-media-picker';

	// Admin
	const CLS_BODY         = self::NS . '-body';
	const CLS_TABLE        = self::NS . '-table';
	const CLS_ITEM         = self::NS . '-item';
	const CLS_ITEM_TEMP    = self::NS . '-item-template';
	const CLS_ITEM_IR      = self::NS . '-item-inside-row';
	const CLS_HANDLE       = self::NS . '-handle';
	const CLS_ADD_ROW      = self::NS . '-add-row';
	const CLS_ADD          = self::NS . '-add';
	const CLS_DEL_LAB      = self::NS . '-delete-label';
	const CLS_DEL          = self::NS . '-delete';
	const CLS_SEL          = self::NS . '-select';
	const CLS_MEDIA_OPENER = self::NS . '-media-opener';

	const CLS_MEDIA        = self::NS . '-media';
	const CLS_URL          = self::NS . '-url';
	const CLS_TITLE        = self::NS . '-title';
	const CLS_FILENAME     = self::NS . '-filename';
	const CLS_H_FILENAME   = self::NS . '-h-filename';

	static private $_instance = [];

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new MediaPicker( $key );
	}

	static public function enqueue_script( $url_to = false ) {
		if ( is_admin() ) {
			if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
			$url_to = untrailingslashit( $url_to );
			wp_enqueue_script( 'picker-media', $url_to . '/asset/lib/picker-media.min.js', [], 1.0, true );
			wp_enqueue_script( self::NS, $url_to . '/asset/media-picker.min.js', [ 'picker-media', 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/media-picker.min.css' );
		}
	}

	private $_key;
	private $_id;

	private $_is_title_editable = true;

	public function __construct( $key ) {
		$this->_key = $key;
		$this->_id  = $key;
		self::$_instance[ $key ] = $this;
	}

	public function set_title_editable( $flag ) {
		$this->_is_title_editable = $flag;
		return $this;
	}

	public function get_items( $post_id = false ) {
		if ( $post_id === false ) $post_id = get_the_ID();

		$skeys = [ 'media', 'url', 'title', 'filename', 'id' ];
		$its = \st\field\get_multiple_post_meta( $post_id, $this->_key, $skeys );

		// For Backward Compatibility
		foreach ( $its as $idx => &$it ) {
			if ( empty( $it['media'] ) ) {
				$it['media'] = $it['id'];
				if ( ! empty( $it['media'] ) ) update_post_meta( $post_id, "{$this->_key}_{$idx}_media", $it['media'] );
			}
			$it['id'] = $it['media'];
		}
		return $its;
	}


	// -------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'advanced' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST["{$this->_key}_nonce"] ) ) return;
		if ( ! wp_verify_nonce( $_POST["{$this->_key}_nonce"], $this->_key ) ) return;
		if ( empty( $_POST[ $this->_key ] ) ) return;  // Do not save before JS is executed
		$this->save_items( $post_id );
	}


	// -------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$its = $this->get_items( $post->ID );
?>
		<input type="hidden" <?php \st\field\name_id( $this->_id ) ?> value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
<?php
		$this->_output_row( [], self::CLS_ITEM_TEMP );
		foreach ( $its as $it ) $this->_output_row( $it, self::CLS_ITEM );
?>
				<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php _e( 'Add Media', 'default' ) ?></a></div>
			</div>
			<script>window.addEventListener('load', function () {
				st_media_picker_initialize_admin('<?php echo $this->_id ?>');
			});</script>
		</div>
<?php
	}

	const CLS_ITEM_CTRL = self::NS . '-item-ctrl';
	const CLS_ITEM_CONT = self::NS . '-item-cont';

	public function _output_row( $it, $cls ) {
		$_url       = isset( $it['url'] )      ? esc_attr( $it['url'] )      : '';
		$_media     = isset( $it['media'] )    ? esc_attr( $it['media'] )    : '';
		$_title     = isset( $it['title'] )    ? esc_attr( $it['title'] )    : '';
		$_filename  = isset( $it['filename'] ) ? esc_attr( $it['filename'] ) : '';
		$h_filename = isset( $it['filename'] ) ? esc_html( $it['filename'] ) : '';

		$ro = $this->_is_title_editable ? '' : 'readonly="readonly"';
?>
		<div class="<?php echo $cls ?>">
			<div class="<?php echo self::CLS_ITEM_CTRL ?>">
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><span><?php _e( 'Remove', 'default' ) ?></span><input type="checkbox" class="<?php echo self::CLS_DEL ?>" /></label>
			</div>
			<div class="<?php echo self::CLS_ITEM_CONT ?>">
				<div class="<?php echo self::CLS_ITEM_IR ?>">
					<span><?php _e( 'Title', 'default' ) ?>:</span>
					<input <?php echo $ro ?> type="text" class="<?php echo self::CLS_TITLE ?>" value="<?php echo $_title ?>" />
				</div>
				<div class="<?php echo self::CLS_ITEM_IR ?>">
					<span><a href="javascript:void(0);" class="<?php echo self::CLS_MEDIA_OPENER ?>"><?php _e( 'File name:', 'default' ) ?></a></span>
					<span>
						<span class="<?php echo self::CLS_H_FILENAME ?>"><?php echo $h_filename ?></span>
						<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL ?>"><?php _e( 'Select', 'default' ) ?></a>
					</span>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>" />
			<input type="hidden" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>" />
			<input type="hidden" class="<?php echo self::CLS_FILENAME ?>" value="<?php echo $_filename ?>" />
		</div>
<?php
	}


	// -------------------------------------------------------------------------


	public function save_items( $post_id ) {
		$skeys = [ 'media', 'url', 'title', 'filename', 'delete' ];

		$its = \st\field\get_multiple_post_meta_from_post( $this->_key, $skeys );
		$its = array_filter( $its, function ( $it ) { return ! $it['delete'] && ! empty( $it['url'] ); } );
		$its = array_values( $its );

		$skeys = [ 'media', 'url', 'title', 'filename' ];
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $its, $skeys );
	}

}


// -----------------------------------------------------------------------------


namespace st\media_picker;

function initialize( $key ) { return new \st\MediaPicker( $key ); }
function enqueue_script( $url_to = false ) { \st\MediaPicker::enqueue_script( $url_to ); }

function get_items( $key, $post_id = false ) { return \st\MediaPicker::get_instance( $key )->get_items( $post_id ); }
function set_title_editable( $key, $flag ) { return \st\MediaPicker::get_instance( $key )->set_title_editable( $flag ); }

function add_meta_box( $key, $label, $screen, $context = 'side', $opts = [] ) {
	if ( isset( $opts['title_editable'] ) ) set_title_editable( $key, $opts['title_editable'] );
	\st\MediaPicker::get_instance( $key )->add_meta_box( $label, $screen, $context );
}
function save_meta_box( $post_id, $key ) { \st\MediaPicker::get_instance( $key )->save_meta_box( $post_id ); }

function save_items( $post_id, $key ) { \st\MediaPicker::get_instance( $key )->save_items( $post_id ); }
