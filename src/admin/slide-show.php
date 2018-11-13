<?php
namespace st;

/**
 *
 * Slide Show (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-13
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/text.php';


class SlideShow {

	const NS = 'st-slide-show';

	// Slide Show
	const CLS_STRIP  = self::NS . '-strip';
	const CLS_SLIDES = self::NS . '-slides';
	const CLS_PREV   = self::NS . '-prev';
	const CLS_NEXT   = self::NS . '-next';
	const CLS_RIVETS = self::NS . '-rivets';
	const CLS_CAP    = self::NS . '-caption';

	// Admin
	const CLS_BODY        = self::NS . '-body';
	const CLS_TABLE       = self::NS . '-table';
	const CLS_ITEM        = self::NS . '-item';
	const CLS_ITEM_TEMP   = self::NS . '-item-template';
	const CLS_HANDLE      = self::NS . '-handle';
	const CLS_ADD_ROW     = self::NS . '-add-row';
	const CLS_ADD         = self::NS . '-add';
	const CLS_DEL_LAB     = self::NS . '-delete-label';
	const CLS_DEL         = self::NS . '-delete';
	const CLS_INFO        = self::NS . '-info';
	const CLS_URL         = self::NS . '-url';
	const CLS_URL_OPENER  = self::NS . '-url-opener';
	const CLS_SEL_URL     = self::NS . '-select-url';
	const CLS_SEL_IMG     = self::NS . '-select-img';
	const CLS_SEL_IMG_SUB = self::NS . '-select-img-sub';
	const CLS_TN          = self::NS . '-thumbnail';
	const CLS_TN_IMG      = self::NS . '-thumbnail-img';
	const CLS_TN_IMG_SUB  = self::NS . '-thumbnail-img-sub';
	const CLS_MEDIA       = self::NS . '-media';
	const CLS_MEDIA_SUB   = self::NS . '-media-sub';

	static private $_instance = [];

	static public function get_instance( $key = false ) {
		return ( $key === false ) ? reset( self::$_instance ) : self::$_instance[ $key ];
	}

	static public function enqueue_script( $url_to ) {
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( self::NS, $url_to . '/asset/slide-show.min.js', [ 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/slide-show.min.css' );
		} else {
			wp_enqueue_script( self::NS, $url_to . '/../../stomp/slide-show/slide-show.min.js', '', 1.0 );
		}
	}

	private $_key;
	private $_id;
	private $_id_hta;
	private $_id_hd;

	private $_effect_type           = 'slide'; // 'scroll' or 'fade'
	private $_caption_type          = 'subtitle'; // 'line' or 'circle'
	private $_zoom_rate             = 1;
	private $_duration_time         = 8; // [second]
	private $_transition_time       = 1; // [second]
	private $_background_opacity    = 0.33;
	private $_is_background_visible = true;
	private $_is_side_slide_visible = false;
	private $_is_picture_scroll     = false;
	private $_is_dual               = false;

	public function __construct( $key ) {
		$this->_key    = $key;
		$this->_id     = $key;
		$this->_id_hta = $key . '-hidden-textarea';
		$this->_id_hd  = $key . '-hidden-div';
		self::$_instance[ $key ] = $this;
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

	public function set_effect_type( $type ) {
		$this->_effect_type = $type;
		return $this;
	}

	public function set_background_opacity( $opacity ) {
		$this->_background_opacity = $opacity;
		return $this;
	}

	public function set_background_visible( $visible ) {
		$this->_is_background_visible = $visible;
		return $this;
	}

	public function set_side_slide_visible( $visible ) {
		$this->_is_side_slide_visible = $visible;
		return $this;
	}

	public function set_picture_scroll( $enabled ) {
		$this->_is_picture_scroll = $enabled;
		return $this;
	}

	public function set_dual_enabled( $enabled ) {
		$this->_is_dual = $enabled;
		return $this;
	}

	public function set_caption_type( $type ) {
		$this->_caption_type = $type;
		return $this;
	}

	private function _create_option_str() {
		$opts = [
			'duration_time'         => $this->_duration_time,
			'transition_time'       => $this->_transition_time,
			'zoom_rate'             => $this->_zoom_rate,
			'effect_type'           => $this->_effect_type,
			'background_opacity'    => $this->_background_opacity,
			'is_background_visible' => $this->_is_background_visible,
			'is_side_slide_visible' => $this->_is_side_slide_visible,
			'is_picture_scroll'     => $this->_is_picture_scroll,
		];
		return json_encode( $opts );
	}

	public function echo_slide_show( $post_id = false, $size = 'large', $class = '' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$sls = $this->_get_slides( $post_id, $size );
		if ( empty( $sls ) ) return false;

		$dom_id   = "{$this->_id}-$post_id";
		$dom_cls  = self::NS . ( empty( $class ) ? '' : ( ' ' . $class ) );
		$opts_str = $this->_create_option_str();
?>
		<section class="<?php echo $dom_cls ?>" id="<?php echo $dom_id ?>">
			<div class="<?php echo self::CLS_STRIP ?>">
				<ul class="<?php echo self::CLS_SLIDES ?>">
<?php
				foreach ( $sls as $sl ) {
					if ( isset( $sl['images'] ) ) $this->_echo_slide_item( $sl );
				}
?>
				</ul>
				<div class="<?php echo self::CLS_PREV ?>"></div>
				<div class="<?php echo self::CLS_NEXT ?>"></div>
			</div>
			<div class="<?php echo self::CLS_RIVETS ?>"></div>
			<script>st_slide_show_initialize('<?php echo $dom_id ?>', <?php echo $opts_str ?>);</script>
		</section>
<?php
		return true;
	}

	private function _echo_slide_item( $sl ) {
		$imgs   = $sl['images'];
		$imgs_s = isset( $sl['images_sub'] ) ? $sl['images_sub'] : false;
		$data = [];

		if ( $this->_is_dual && $imgs_s !== false ) {
			self::_set_attrs( $data, 'img-sub', $imgs_s );
		}
		self::_set_attrs( $data, 'img', $imgs );
		$attr = '';
		foreach ( $data as $key => $val ) {
			$attr .= " data-$key=\"$val\"";
		}
		$cont = $this->_create_slide_content( $sl['caption'], $sl['url'] );
		echo "<li$attr>$cont</li>";
	}

	static private function _set_attrs( &$data, $key, $imgs ) {
		if ( 2 <= count( $imgs ) ) {
			$data["$key-phone"] = esc_url( $imgs[0] );
			$data[ $key ]       = esc_url( $imgs[1] );
		} else {
			$data[ $key ] = esc_url( $imgs[0] );
		}
	}

	private function _create_slide_content( $cap, $url ) {
		$div = '';
		if ( ! empty( $cap ) ) {
			$ss  = \st\separate_line( $cap, 'segment_raw' );
			$str = '<div><span>' . implode( '</span></div><div><span>', $ss ) . '</span></div>';
			$div = '<div class="' . self::CLS_CAP . " {$this->_caption_type}\">$str</div>";
		}
		if ( empty( $url ) ) return $div;
		$_url = esc_url( $url );
		return "<a href=\"$_url\">$div</a>";
	}

	public function echo_slide_items( $post_id = false, $size = 'medium' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$sls = $this->_get_slides( $post_id, $size );

		foreach ( $sls as $idx => $sl ) {
			$img   = esc_url( $sl['image'] );
			$style = "background-image: url('{$img}');";
			$event = "st_slide_show_page('{$this->_id}_$post_id', {$idx});"
?>
			<li><a href="javascript:void(0)" onclick="<?php echo $event ?>" style="<?php echo $style ?>"></a></li>
<?php
		}
	}


	// -------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'advanced' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST["{$this->_key}_nonce"] ) ) return;
		if ( ! wp_verify_nonce( $_POST["{$this->_key}_nonce"], $this->_key ) ) return;
		$this->_save_slides( $post_id );
	}

	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
?>
		<input type="hidden" id="<?php echo $this->_id ?>" name="<?php echo $this->_id ?>" value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
<?php
		if ( $this->_is_dual ) {
			$this->_output_row( '', '', self::CLS_ITEM_TEMP, '', '', '', '' );
		} else {
			$this->_output_row( [], self::CLS_ITEM_TEMP );
		}
		foreach ( $this->_get_slides( $post->ID ) as $sl ) {
			if ( $this->_is_dual ) {
				$this->_output_row_dual( $sl, self::CLS_ITEM );
			} else {
				$this->_output_row( $sl, self::CLS_ITEM );
			}
		}
?>
				<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php _e( 'Add Media', 'default' ) ?></a></div>
			</div>
			<textarea id="<?php echo $this->_id_hta ?>" style="display: none;"></textarea>
			<div id="<?php echo $this->_id_hd ?>" style="display: none;"></div>
			<script>st_slide_show_initialize_admin('<?php echo $this->_id ?>', <?php echo $this->_is_dual ? 'true' : 'false' ?>);</script>
		</div>
<?php
	}

	private function _output_row_dual( $sl, $cls ) {
		$_cap     = isset( $sl['caption'] )   ? esc_attr( $sl['caption'] )   : '';
		$_url     = isset( $sl['url'] )       ? esc_attr( $sl['url'] )       : '';
		$_img     = isset( $sl['image'] )     ? esc_url( $sl['image'] )      : '';
		$_img_s   = isset( $sl['image_sub'] ) ? esc_url( $sl['image_sub'] )  : '';
		$_media   = isset( $sl['media'] )     ? esc_attr( $sl['media'] )     : '';
		$_media_s = isset( $sl['media_sub'] ) ? esc_attr( $sl['media_sub'] ) : '';
		$_style   = empty( $_img )    ? '' : " style=\"background-image:url($_img)\"";
		$_style_s = empty( $_img_s )  ? '' : " style=\"background-image:url($_img_s)\"";
	?>
		<div class="<?php echo $cls ?>">
			<div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></input><br /><?php _e( 'Remove', 'default' ) ?></label>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo $_cap ?>" /></div>
					<div><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</div>
					<div><input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>" />
					<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a></div>
				</div>
				<div class="st-slide-show-thumbnail-wrap">
					<div class="<?php echo self::CLS_TN ?>">
						<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $_style ?>></div></a>
					</div>
					<div class="<?php echo self::CLS_TN ?>">
						<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG_SUB ?>"><div class="<?php echo self::CLS_TN_IMG_SUB ?>"<?php echo $_style_s ?>></div></a>
					</div>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>" />
			<input type="hidden" class="<?php echo self::CLS_MEDIA_SUB ?>" value="<?php echo $_media_s ?>" />
		</div>
	<?php
	}

	private function _output_row( $sl, $cls ) {
		$_cap   = isset( $sl['caption'] ) ? esc_attr( $sl['caption'] ) : '';
		$_url   = isset( $sl['url'] )     ? esc_attr( $sl['url'] )     : '';
		$_img   = isset( $sl['image'] )   ? esc_url( $sl['image'] )    : '';
		$_media = isset( $sl['media'] )   ? esc_attr( $sl['media'] )   : '';
		$_style = empty( $_img ) ? '' : " style=\"background-image:url($_img)\"";
?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br /><input type="checkbox" class="<?php echo self::CLS_DEL ?>" /></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo $_cap ?>" /></div>
					<div><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</div>
					<div><input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>" />
					<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a></div>
				</div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $_style ?>></div></a>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>" />
		</div>
<?php
	}


	// -------------------------------------------------------------------------


	private function _save_slides( $post_id ) {
		$keys = [ 'media', 'caption', 'url', 'delete' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';

		$sls = \st\field\get_multiple_post_meta_from_post( $this->_key, $keys );
		$sls = array_filter( $sls, function ( $sl ) { return ! $sl['delete']; } );
		$sls = array_values( $sls );

		foreach ( $sls as &$sl ) {
			$pid = url_to_postid( $sl['url'] );
			if ( $pid !== 0 ) $sl['url'] = $pid;
		}
		$keys = [ 'media', 'caption', 'url' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $sls, $keys );
	}

	private function _get_slides( $post_id, $size = 'medium' ) {
		$keys = [ 'media', 'caption', 'url' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';

		$sls = \st\field\get_multiple_post_meta( $post_id, $this->_key, $keys );

		foreach ( $sls as &$sl ) {
			if ( isset( $sl['url'] ) && is_numeric( $sl['url'] ) ) {
				$permalink = get_permalink( $sl['url'] );
				if ( $permalink !== false ) {
					$sl['post_id'] = $sl['url'];
					$sl['url'] = $permalink;
				}
			}
			$sl['image'] = '';
			if ( ! empty( $sl['media'] ) ) {
				$this->_get_images( $sl, intval( $sl['media'] ), $size, 'image', 'images' );
			}
			if ( $this->_is_dual ) {
				$sl['image_sub'] = '';
				if ( ! empty( $sl['media_sub'] ) ) {
					$this->_get_images( $sl, intval( $sl['media_sub'] ), $size, 'image_sub', 'images_sub' );
				}
			}
		}
		return $sls;
	}

	private function _get_images( &$sl, $aid, $size, $key, $key_s ) {
		if ( is_array( $size ) ) {
			$imgs = [];
			foreach ( $size as $sz ) {
				$img = wp_get_attachment_image_src( $aid, $sz );
				if ( $img ) $imgs[] = $img[0];
			}
			if ( ! empty( $imgs ) ) {
				$sl[ $key_s ] = $imgs;
				$sl[ $key   ] = $imgs[ count( $imgs ) - 1 ];
			}
		} else {
			$img = wp_get_attachment_image_src( $aid, $size );
			if ( $img ) {
				$sl[ $key_s ] = [ $img[0] ];
				$sl[ $key   ] = $img[0];
			}
		}
	}

}


// -----------------------------------------------------------------------------


namespace st\slide_show;

function initialize( $key ) { return new \st\SlideShow( $key ); }
function enqueue_script( $url_to ) { \st\SlideShow::enqueue_script( $url_to ); }

function set_duration_time( $key, $set_duration_time ) { return \st\SlideShow::get_instance( $key )->set_duration_time( $set_duration_time ); }
function set_transition_time( $key, $sec ) { return \st\SlideShow::get_instance( $key )->set_transition_time( $sec ); }
function set_zoom_rate( $key, $rate ) { return \st\SlideShow::get_instance( $key )->set_zoom_rate( $rate ); }
function set_effect_type( $key, $type ) { return \st\SlideShow::get_instance( $key )->set_effect_type( $type ); }
function set_background_opacity( $key, $opacity ) { return \st\SlideShow::get_instance( $key )->set_background_opacity( $opacity ); }
function set_background_visible( $key, $visible ) { return \st\SlideShow::get_instance( $key )->set_background_visible( $visible ); }
function set_side_slide_visible( $key, $visible ) { return \st\SlideShow::get_instance( $key )->set_side_slide_visible( $visible ); }
function set_picture_scroll( $key, $enabled ) { return \st\SlideShow::get_instance( $key )->set_picture_scroll( $enabled ); }
function set_dual_enabled( $key, $enabled ) { return \st\SlideShow::get_instance( $key )->set_dual_enabled( $enabled ); }
function set_caption_type( $key, $type ) { return \st\SlideShow::get_instance( $key )->set_caption_type( $type ); }
function echo_slide_show( $key, $post_id = false, $size = 'large', $class = '' ) { return \st\SlideShow::get_instance( $key )->echo_slide_show( $post_id, $size, $class ); }
function echo_slide_items( $key, $post_id = false, $size = 'medium' ) { return \st\SlideShow::get_instance( $key )->echo_slide_items( $post_id, $size ); }

function add_meta_box( $key, $label, $screen, $context = 'side' ) { \st\SlideShow::get_instance( $key )->add_meta_box( $label, $screen, $context ); }
function save_meta_box( $post_id, $key ) { \st\SlideShow::get_instance( $key )->save_meta_box( $post_id ); }
