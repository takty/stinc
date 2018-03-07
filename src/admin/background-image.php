<?php
namespace st;

/**
 *
 * Background Images (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-07
 *
 */


require_once __DIR__ . '/../system/field.php';


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

	static private $_instance = null;
	static public function get_instance() { return self::$_instance; }

	private $_key;
	private $_id;
	private $_id_hta;
	private $_id_hd;

	private $_effect_type       = 'fade'; // 'slide' or 'scroll' or 'fade'
	private $_zoom_rate         = 1;
	private $_duration_time     = 8; // [second]
	private $_transition_time   = 1; // [second]
	private $_is_random_timing  = true;

	public function __construct( $key ) {
		$this->_key      = $key;
		$this->_id       = $key;
		$this->_id_hta   = $key . '-hidden-textarea';
		$this->_id_hd    = $key . '-hidden-div';

		if ( self::$_instance === null ) self::$_instance = $this;
	}

	public function enqueue_script( $url_to ) {
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( self::NS, $url_to . '/asset/background-image.min.js', [ 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/background-image.min.css' );
		} else {
			wp_enqueue_script( self::NS, $url_to . '/../../../stomp/background-image/background-image.min.js', '', 1.0 );
		}
	}

	public function set_effect_type( $type ) {
		$this->_effect_type = $type;
		return $this;
	}

	public function set_caption_type( $type ) {
		$this->_caption_type = $type;
		return $this;
	}

	public function set_zoom_rate( $rate ) {
		$this->_zoom_rate = $rate;
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

	public function set_random_timing( $do ) {
		$this->_is_random_timing = $do;
		return $this;
	}

	public function echo_background_image( $post_id = false, $size = 'large', $class = '' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$is = $this->_get_images( $post_id, $size );
		if ( empty( $is ) ) return false;
		$opts = [
			'effect_type'     => $this->_effect_type,
			'duration_time'   => $this->_duration_time,
			'transition_time' => $this->_transition_time,
			'zoom_rate'       => $this->_zoom_rate,
			'random_timing'   => $this->_is_random_timing
		];
		$opts_str = json_encode( $opts );
	?>
		<section class="<?php echo self::NS . ( empty( $class ) ? '' : ( ' ' . $class ) ) ?>" id="<?php echo "{$this->_id}_$post_id" ?>">
			<div class="<?php echo self::CLS_STRIP ?>">
				<ul class="<?php echo self::CLS_SLIDES ?>">
	<?php
				foreach ( $is as $i ) $this->_echo_image_item( $i['image'], isset( $i['images'] ) ? $i['images'] : false );
	?>
				</ul>
			</div>
			<script>st_background_image_initialize('<?php echo "{$this->_id}_$post_id" ?>', <?php echo $opts_str ?>);</script>
		</section>
	<?php
		return true;
	}

	private function _echo_image_item( $img, $imgs ) {
		if ( $imgs !== false && 2 <= count( $imgs ) ) {
			$eu_img0 = esc_url( $imgs[0] );
			$eu_img1 = esc_url( $imgs[1] );
			echo "<li data-img=\"$eu_img1\" data-img-phone=\"$eu_img0\"></li>";
		} else {
			$eu_img = esc_url( $img );
			echo "<li data-img=\"$eu_img\"></li>";
		}
	}


	// -------------------------------------------------------------------------

	public function add_meta_box( $label, $screen ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST[ "{$this->_key}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->_key}_nonce" ], $this->_key ) ) return;
		$this->_save_images( $post_id );
	}

	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
	?>
		<input type="hidden" id="<?php echo $this->_id ?>" name="<?php echo $this->_id ?>" value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
	<?php
			$this->_output_row( '', '', self::CLS_ITEM_TEMP );
			foreach ( $this->_get_images( $post->ID ) as $it ) {
				$this->_output_row( $it['image'], $it['media'], self::CLS_ITEM );
			}
	?>
				<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php echo __( 'Add Media', 'default' ) ?></a></div>
			</div>
			<script>st_background_image_initialize_admin('<?php echo $this->_id ?>');</script>
			<textarea id="<?php echo $this->_id_hta ?>" style="display: none;"></textarea>
			<div id="<?php echo $this->_id_hd ?>" style="display: none;"></div>
		</div>
	<?php
	}

	private function _output_row( $image, $media, $class ) {
		$media_style = empty( $image ) ? '' : ' style="background-image: url(' . esc_url( $image ) . ')"';
	?>
		<div class="<?php echo $class ?>">
			<div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></input><br /><?php echo __( 'Remove', 'default' ) ?></label>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
			</div>
			<div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $media_style ?>></div></a>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo esc_attr( $media ) ?>" />
		</div>
	<?php
	}


	// -------------------------------------------------------------------------

	private function _save_images( $post_id ) {
		$is = \st\field\get_multiple_post_meta_from_post( $this->_key, [ 'media', 'delete' ] );
		$is = array_filter( $is, function ( $i ) { return ! $i['delete']; } );
		$is = array_values( $is );
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $is, [ 'media' ] );
	}

	private function _get_images( $post_id, $size = 'medium' ) {
		$is = \st\field\get_multiple_post_meta( $post_id, $this->_key, [ 'media' ] );

		foreach ( $is as &$i ) {
			$i['image'] = '';
			if ( empty( $i['media'] ) ) continue;
			$aid = intval( $i['media'] );

			if ( is_array( $size ) ) {
				$imgs = [];
				foreach ( $size as $sz ) {
					$img = wp_get_attachment_image_src( $aid, $sz );
					if ( $img ) $imgs[] = $img[0];
				}
				if ( ! empty( $imgs ) ) {
					$i['images'] = $imgs;
					$i['image'] = $imgs[ count( $imgs ) - 1 ];
				}
			} else {
				$img = wp_get_attachment_image_src( $aid, $size );
				if ( $img ) $i['image'] = $img[0];
			}
		}
		return $is;
	}

}
