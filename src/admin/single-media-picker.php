<?php
namespace st;

/**
 *
 * Single Media Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-14
 *
 */


require_once __DIR__ . '/../system/field.php';


class SingleMediaPicker {

	const NS = 'st-single-media-picker';

	// Admin
	const CLS_BODY         = self::NS . '-body';
	const CLS_ITEM         = self::NS . '-item';
	const CLS_ITEM_IR      = self::NS . '-item-inside-row';
	const CLS_DEL          = self::NS . '-delete';
	const CLS_SEL          = self::NS . '-select';
	const CLS_ADD_ROW      = self::NS . '-add-row';
	const CLS_ADD          = self::NS . '-add';
	const CLS_MEDIA_OPENER = self::NS . '-media-opener';

	const CLS_TITLE        = self::NS . '-title';
	const CLS_FILENAME     = self::NS . '-filename';

	static private $_instance = [];

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new SingleMediaPicker( $key );
	}

	static public function enqueue_script( $url_to ) {
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( 'picker-media', $url_to . '/asset/lib/picker-media.min.js', [], 1.0, true );
			wp_enqueue_script( self::NS, $url_to . '/asset/single-media-picker.min.js', [ 'picker-media' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/single-media-picker.min.css' );
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

	public function get_item( $post_id = false ) {
		if ( $post_id === false ) $post_id = get_the_ID();

		$media    = get_post_meta( $post_id, "{$this->_key}_media",    true );
		$url      = get_post_meta( $post_id, "{$this->_key}_url",      true );
		$title    = get_post_meta( $post_id, "{$this->_key}_title",    true );
		$filename = get_post_meta( $post_id, "{$this->_key}_filename", true );

		// For Backward Compatibility
		if ( empty( $media ) ) {
			$media = get_post_meta( $post_id, "{$this->_key}_id", true );
			if ( ! empty( $media ) ) update_post_meta( $post_id, "{$this->_key}_media", $media );
		}
		$id = $media;

		return compact( 'media', 'url', 'title', 'filename', 'id' );
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

		$_url       = isset( $it['url'] )      ? esc_attr( $it['url'] )      : '';
		$_title     = isset( $it['title'] )    ? esc_attr( $it['title'] )    : '';
		$h_filename = isset( $it['filename'] ) ? esc_html( $it['filename'] ) : '';

		$id_title = "{$this->_key}_title";
		$ro = $this->_is_title_editable ? '' : 'readonly="readonly"';
	?>
		<div id="<?php echo $this->_id ?>"></div>
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_ITEM ?>">
				<div>
					<a href="javascript:void(0);" class="<?php echo self::CLS_DEL ?> widget-control-remove"><?php _e( 'Remove', 'default' ); ?></a>
				</div>
				<div>
					<div class="<?php echo self::CLS_ITEM_IR ?>">
						<span><?php _e( 'Title', 'default' ) ?>:</span>
						<input <?php echo $ro ?> type="text" <?php \st\field\esc_key_e( $id_title ) ?> value="<?php echo $_title ?>" />
					</div>
					<div class="<?php echo self::CLS_ITEM_IR ?>">
						<span><a href="javascript:void(0);" class="<?php echo self::CLS_MEDIA_OPENER ?>"><?php _e( 'File name:', 'default' ) ?></a></span>
						<span class="<?php echo self::CLS_FILENAME ?>"><?php echo $h_filename ?></span>
						<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL ?>"><?php _e( 'Select', 'default' ) ?></a>
					</div>
				</div>
			</div>
			<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php _e( 'Add Media', 'default' ); ?></a></div>
			<?php $this->_output_hidden_fields( $it, [ 'media', 'url', 'filename' ] ) ?>
			<script>document.addEventListener('DOMContentLoaded', function () {
				st_single_media_picker_initialize_admin('<?php echo $this->_id ?>');
			});</script>
		</div>
	<?php
	}

	private function _output_hidden_fields( $it, $keys ) {
		foreach ( $keys as $key ) {
			$_val = esc_attr( $it[ $key ] );
	?>
			<input type="hidden" <?php \st\field\esc_key_e( "{$this->_key}_$key" ) ?> value="<?php echo $_val ?>" />
	<?php
		}
	}


	// -------------------------------------------------------------------------


	private function _save_item( $post_id ) {
		update_post_meta( $post_id, "{$this->_key}_media",    $_POST["{$this->_key}_media"] );
		update_post_meta( $post_id, "{$this->_key}_url",      $_POST["{$this->_key}_url"] );
		update_post_meta( $post_id, "{$this->_key}_title",    $_POST["{$this->_key}_title"] );
		update_post_meta( $post_id, "{$this->_key}_filename", $_POST["{$this->_key}_filename"] );
	}

}


// -----------------------------------------------------------------------------


namespace st\single_media_picker;

function initialize( $key ) { return new \st\SingleMediaPicker( $key ); }
function enqueue_script( $url_to ) { \st\SingleMediaPicker::enqueue_script( $url_to ); }

function get_item( $key, $post_id = false ) { return \st\SingleMediaPicker::get_instance( $key )->get_item( $post_id ); }
function set_title_editable( $key, $flag ) { return \st\SingleMediaPicker::get_instance( $key )->set_title_editable( $flag ); }

function add_meta_box( $key, $label, $screen, $context = 'side', $opts = [] ) {
	if ( isset( $opts['title_editable'] ) ) set_title_editable( $key, $opts['title_editable'] );
	\st\SingleMediaPicker::get_instance( $key )->add_meta_box( $label, $screen, $context );
}
function save_meta_box( $post_id, $key ) { \st\SingleMediaPicker::get_instance( $key )->save_meta_box( $post_id ); }
