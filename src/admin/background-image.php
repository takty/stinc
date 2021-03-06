<?php
namespace st;

/**
 *
 * Background Images (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-07-18
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/url.php';


class BackgroundImage {

	const NS = 'st-background-image';

	// Background Images
	const CLS_STRIP  = self::NS . '-strip';
	const CLS_SLIDES = self::NS . '-slides';

	// Admin
	const CLS_BODY      = self::NS . '-body';
	const CLS_TABLE     = self::NS . '-table';
	const CLS_ITEM      = self::NS . '-item';
	const CLS_ITEM_TEMP = self::NS . '-item-template';
	const CLS_HANDLE    = self::NS . '-handle';
	const CLS_ADD_ROW   = self::NS . '-add-row';
	const CLS_ADD       = self::NS . '-add';
	const CLS_DEL_LAB   = self::NS . '-delete-label';
	const CLS_DEL       = self::NS . '-delete';
	const CLS_SEL_IMG   = self::NS . '-select-img';
	const CLS_TN        = self::NS . '-thumbnail';
	const CLS_TN_IMG    = self::NS . '-thumbnail-img';
	const CLS_MEDIA     = self::NS . '-media';

	static private $_instance = [];

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new SlideShow( $key );
	}

	static public function enqueue_script( $url_to = false ) {
		if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( 'picker-media', $url_to . '/asset/lib/picker-media.min.js', [], 1.0, true );
			wp_enqueue_script( self::NS, $url_to . '/asset/background-image.min.js', [ 'picker-media', 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/background-image.min.css' );
		} else {
			wp_enqueue_script( self::NS, $url_to . '/../../stomp/background-image/background-image.min.js', '', 1.0 );
		}
	}

	private $_key;
	private $_id;

	private $_effect_type      = 'fade'; // 'slide' or 'scroll' or 'fade'
	private $_zoom_rate        = 1;
	private $_duration_time    = 8; // [second]
	private $_transition_time  = 1; // [second]
	private $_is_random_timing = true;
	private $_is_shuffled      = false;

	public function __construct( $key ) {
		$this->_key = $key;
		$this->_id  = $key;
		self::$_instance[ $key ] = $this;
	}

	public function set_effect_type( $type ) {
		$this->_effect_type = $type;
		return $this;
	}

	public function set_duration_time( $sec ) {
		$this->_duration_time = $sec;
		return $this;
	}

	public function set_transition_time( $sec ) {
		$this->_transition_time = $sec;
		return $this;
	}

	public function set_zoom_rate( $rate ) {
		$this->_zoom_rate = $rate;
		return $this;
	}

	public function set_random_timing_enabled( $enabled ) {
		$this->_is_random_timing = $enabled;
		return $this;
	}

	public function set_shuffled( $enabled ) {
		$this->_is_shuffled = $enabled;
		return $this;
	}

	private function _create_option_str() {
		$opts = [
			'duration_time'    => $this->_duration_time,
			'transition_time'  => $this->_transition_time,
			'zoom_rate'        => $this->_zoom_rate,
			'effect_type'      => $this->_effect_type,
			'is_random_timing' => $this->_is_random_timing
		];
		return json_encode( $opts );
	}

	public function echo_background_image( $post_id = false, $size = 'large', $cls = '' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$its = $this->_get_items( $post_id, $size );
		if ( empty( $its ) ) return false;

		$dom_id   = "{$this->_id}-$post_id";
		$dom_cls  = self::NS . ( empty( $cls ) ? '' : ( ' ' . $cls ) );
		$opts_str = $this->_create_option_str();
?>
		<section class="<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
			<div class="<?php echo self::CLS_STRIP ?>">
				<ul class="<?php echo self::CLS_SLIDES ?>">
<?php
		foreach ( $its as $it ) {
			if ( isset( $it['images'] ) ) $this->_echo_image_item( $it );
		}
?>
				</ul>
			</div>
			<script>st_background_image_initialize('<?php echo $dom_id ?>', <?php echo $opts_str ?>);</script>
		</section>
<?php
		return true;
	}

	private function _echo_image_item( $it ) {
		$imgs = $it['images'];

		if ( 2 <= count( $imgs ) ) {
			$_img0 = esc_url( $imgs[0] );
			$_img1 = esc_url( $imgs[1] );
			echo "<li data-img=\"$_img1\" data-img-phone=\"$_img0\"></li>";
		} else {
			$_img = esc_url( $imgs[0] );
			echo "<li data-img=\"$_img\"></li>";
		}
	}


	// -------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'side' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST[ "{$this->_key}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->_key}_nonce" ], $this->_key ) ) return;
		$this->_save_items( $post_id );
	}


	// -------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$its = $this->_get_items( $post->ID );
?>
		<input type="hidden" <?php \st\field\name_id( $this->_id ) ?> value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
<?php
		$this->_output_row( [], self::CLS_ITEM_TEMP );
		foreach ( $its as $it ) $this->_output_row( $it, self::CLS_ITEM );
?>
			</div>
			<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php _e( 'Add Media', 'default' ) ?></a></div>
			<script>window.addEventListener('load', function () {
				st_background_image_initialize_admin('<?php echo $this->_id ?>');
			});</script>
		</div>
<?php
	}

	private function _output_row( $it, $cls ) {
		$_img   = isset( $it['image'] ) ? esc_url( $it['image'] )  : '';
		$_media = isset( $it['media'] ) ? esc_attr( $it['media'] ) : '';
		$_style = empty( $_img ) ? '' : " style=\"background-image:url($_img)\"";
?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br /><input type="checkbox" class="<?php echo self::CLS_DEL ?>" /></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $_style ?>></div></a>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>" />
		</div>
<?php
	}


	// -------------------------------------------------------------------------


	private function _save_items( $post_id ) {
		$its = \st\field\get_multiple_post_meta_from_post( $this->_key, [ 'media', 'delete' ] );

		$its = array_filter( $its, function ( $it ) { return ! $it['delete']; } );
		$its = array_values( $its );

		\st\field\update_multiple_post_meta( $post_id, $this->_key, $its, [ 'media' ] );
	}

	private function _get_items( $post_id, $size = 'medium' ) {
		$its = \st\field\get_multiple_post_meta( $post_id, $this->_key, [ 'media' ] );

		foreach ( $its as &$it ) {
			$it['image'] = '';
			if ( empty( $it['media'] ) ) continue;
			$aid = intval( $it['media'] );

			if ( is_array( $size ) ) {
				$imgs = [];
				foreach ( $size as $sz ) {
					$img = wp_get_attachment_image_src( $aid, $sz );
					if ( $img ) $imgs[] = $img[0];
				}
				if ( ! empty( $imgs ) ) {
					$it['images'] = $imgs;
					$it['image' ] = $imgs[ count( $imgs ) - 1 ];
				}
			} else {
				$img = wp_get_attachment_image_src( $aid, $size );
				if ( $img ) {
					$it['images'] = [ $img[0] ];
					$it['image' ] = $img[0];
				}
			}
		}
		if ( ! is_admin() && $this->_is_shuffled ) shuffle( $its );
		return $its;
	}

}


// -----------------------------------------------------------------------------


namespace st\echo_background_image;

function initialize( $key ) { return new \st\BackgroundImage( $key ); }
function enqueue_script( $url_to = false ) { \st\BackgroundImage::enqueue_script( $url_to ); }

function set_effect_type( $key, $type ) { return \st\BackgroundImage::get_instance( $key )->set_effect_type( $type ); }
function set_duration_time( $key, $sec ) { return \st\BackgroundImage::get_instance( $key )->set_duration_time( $sec ); }
function set_transition_time( $key, $sec ) { return \st\BackgroundImage::get_instance( $key )->set_transition_time( $sec ); }
function set_zoom_rate( $key, $rate ) { return \st\BackgroundImage::get_instance( $key )->set_zoom_rate( $rate ); }
function set_random_timing_enabled( $key, $enabled ) { return \st\BackgroundImage::get_instance( $key )->set_random_timing_enabled( $enabled ); }
function echo_slide_show( $key, $post_id = false, $size = 'large', $cls = '' ) { return \st\BackgroundImage::get_instance( $key )->echo_background_image( $post_id, $size, $cls ); }

function add_meta_box( $key, $label, $screen, $context = 'side' ) { \st\BackgroundImage::get_instance( $key )->add_meta_box( $label, $screen, $context ); }
function save_meta_box( $post_id, $key ) { \st\BackgroundImage::get_instance( $key )->save_meta_box( $post_id ); }
