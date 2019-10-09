<?php
namespace st;
/**
 *
 * Slide Show (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-07-18
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/text.php';
require_once __DIR__ . '/../tag/url.php';


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
	const CLS_BODY            = self::NS . '-body';
	const CLS_TABLE           = self::NS . '-table';
	const CLS_ITEM            = self::NS . '-item';
	const CLS_ITEM_TEMP_IMG   = self::NS . '-item-template-img';
	const CLS_ITEM_TEMP_VIDEO = self::NS . '-item-template-video';
	const CLS_HANDLE          = self::NS . '-handle';
	const CLS_ADD_ROW         = self::NS . '-add-row';
	const CLS_ADD_IMG         = self::NS . '-add-img';
	const CLS_ADD_VIDEO       = self::NS . '-add-video';
	const CLS_DEL_LAB         = self::NS . '-delete-label';
	const CLS_DEL             = self::NS . '-delete';
	const CLS_INFO            = self::NS . '-info';
	const CLS_URL_OPENER      = self::NS . '-url-opener';
	const CLS_SEL_URL         = self::NS . '-select-url';
	const CLS_SEL_IMG         = self::NS . '-select-img';
	const CLS_SEL_IMG_SUB     = self::NS . '-select-img-sub';
	const CLS_SEL_VIDEO       = self::NS . '-select-video';
	const CLS_TN              = self::NS . '-thumbnail';
	const CLS_TN_IMG          = self::NS . '-thumbnail-img';
	const CLS_TN_IMG_SUB      = self::NS . '-thumbnail-img-sub';

	const CLS_MEDIA           = self::NS . '-media';
	const CLS_MEDIA_SUB       = self::NS . '-media-sub';
	const CLS_TYPE            = self::NS . '-type';
	const CLS_URL             = self::NS . '-url';

	const TYPE_IMAGE = 'image';
	const TYPE_VIDEO = 'video';

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
			wp_enqueue_script( 'picker-link', $url_to . '/asset/lib/picker-link.min.js', [ 'wplink', 'jquery-ui-autocomplete' ] );
			wp_enqueue_script( 'picker-media', $url_to . '/asset/lib/picker-media.min.js', [], 1.0, true );
			wp_enqueue_script( self::NS, $url_to . '/asset/slide-show.min.js', [ 'picker-media', 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/slide-show.min.css' );
		} else {
			wp_enqueue_script( self::NS, $url_to . '/../../stomp/slide-show/slide-show.min.js', '', 1.0 );
		}
	}

	private $_key;
	private $_id;

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
	private $_is_video_enablde      = false;
	private $_is_shuffled           = false;

	public function __construct( $key ) {
		$this->_key = $key;
		$this->_id  = $key;
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

	public function set_video_enabled( $enabled ) {
		$this->_is_video_enablde = $enabled;
		return $this;
	}

	public function set_shuffled( $enabled ) {
		$this->_is_shuffled = $enabled;
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

	public function echo_slide_show( $post_id = false, $size = 'large', $cls = '' ) {
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
			if ( $it['type'] === self::TYPE_IMAGE ) $this->_echo_slide_item_img( $it );
			else if ( $it['type'] === self::TYPE_VIDEO ) $this->_echo_slide_item_video( $it );
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

	private function _echo_slide_item_img( $it ) {
		$imgs   = $it['images'];
		$imgs_s = isset( $it['images_sub'] ) ? $it['images_sub'] : false;
		$data = [];

		if ( $this->_is_dual && $imgs_s !== false ) {
			self::_set_attrs( $data, 'img-sub', $imgs_s );
		}
		self::_set_attrs( $data, 'img', $imgs );
		$attr = '';
		foreach ( $data as $key => $val ) {
			$attr .= " data-$key=\"$val\"";
		}
		$cont = $this->_create_slide_content( $it['caption'], $it['url'] );
		echo "<li$attr>$cont</li>";
	}

	private function _echo_slide_item_video( $it ) {
		$attr = " data-video=\"{$it['video']}\"";
		$cont = $this->_create_slide_content( $it['caption'], $it['url'] );
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
		$its = $this->_get_items( $post_id, $size );

		foreach ( $its as $idx => $it ) {
			$event = "st_slide_show_page('{$this->_id}_$post_id', {$idx});";
			if ( $it['type'] === self::TYPE_IMAGE ) {
				$_img   = esc_url( $it['image'] );
				$_style = "background-image: url('{$_img}');";
?>
				<li><a href="javascript:void(0)" onclick="<?php echo $event ?>" style="<?php echo $_style ?>"></a></li>
<?php
			} else if ( $it['type'] === self::TYPE_VIDEO ) {
				$_video = esc_url( $it['video'] );
?>
				<li><a href="javascript:void(0)" onclick="<?php echo $event ?>"><video><source src="<?php echo $_video ?>"></video></a></li>
<?php
			}
		}
	}


	// -------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'advanced' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST["{$this->_key}_nonce"] ) ) return;
		if ( ! wp_verify_nonce( $_POST["{$this->_key}_nonce"], $this->_key ) ) return;
		$this->_save_items( $post_id );
	}


	// -------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$its = $this->_get_items( $post->ID );
?>
		<input type="hidden" <?php \st\field\name_id( $this->_id ) ?> value="">
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
<?php
		$this->_output_row_image( [], self::CLS_ITEM_TEMP_IMG );
		$this->_output_row_video( [], self::CLS_ITEM_TEMP_VIDEO );
		foreach ( $its as $it ) {
			if ( $it['type'] === self::TYPE_IMAGE ) $this->_output_row_image( $it, self::CLS_ITEM );
			else if ( $it['type'] === self::TYPE_VIDEO ) $this->_output_row_video( $it, self::CLS_ITEM );
		}
?>
				<div class="<?php echo self::CLS_ADD_ROW ?>">
<?php
		if ( $this->_is_video_enablde ) {
?>
					<a href="javascript:void(0);" class="<?php echo self::CLS_ADD_VIDEO ?> button"><?php _e( 'Add Video', 'default' ) ?></a>
<?php
		}
?>
					<a href="javascript:void(0);" class="<?php echo self::CLS_ADD_IMG ?> button"><?php _e( 'Add Media', 'default' ) ?></a>
				</div>
			</div>
			<script>window.addEventListener('load', function () {
				st_slide_show_initialize_admin('<?php echo $this->_id ?>', <?php echo $this->_is_dual ? 'true' : 'false' ?>);
			});</script>
		</div>
<?php
	}

	private function _output_row_image( $it, $cls ) {
		if ( $this->_is_dual ) {
			$this->_output_row_dual( $it, $cls );
		} else {
			$this->_output_row_single( $it, $cls );
		}
	}

	private function _output_row_single( $it, $cls ) {
		$_cap   = isset( $it['caption'] ) ? esc_attr( $it['caption'] ) : '';
		$_url   = isset( $it['url'] )     ? esc_attr( $it['url'] )     : '';
		$_img   = isset( $it['image'] )   ? esc_url( $it['image'] )    : '';
		$_media = isset( $it['media'] )   ? esc_attr( $it['media'] )   : '';
		$_style = empty( $_img ) ? '' : " style=\"background-image:url($_img)\"";
?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
					<div><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</div>
					<div>
						<input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>">
						<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a>
					</div>
				</div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $_style ?>></div></a>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>">
			<input type="hidden" class="<?php echo self::CLS_TYPE ?>" value="image">
		</div>
<?php
	}

	private function _output_row_dual( $it, $cls ) {
		$_cap     = isset( $it['caption'] )   ? esc_attr( $it['caption'] )   : '';
		$_url     = isset( $it['url'] )       ? esc_attr( $it['url'] )       : '';
		$_img     = isset( $it['image'] )     ? esc_url( $it['image'] )      : '';
		$_img_s   = isset( $it['image_sub'] ) ? esc_url( $it['image_sub'] )  : '';
		$_media   = isset( $it['media'] )     ? esc_attr( $it['media'] )     : '';
		$_media_s = isset( $it['media_sub'] ) ? esc_attr( $it['media_sub'] ) : '';
		$_style   = empty( $_img )    ? '' : " style=\"background-image:url($_img)\"";
		$_style_s = empty( $_img_s )  ? '' : " style=\"background-image:url($_img_s)\"";
	?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
					<div><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</div>
					<div><input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>">
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
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>">
			<input type="hidden" class="<?php echo self::CLS_MEDIA_SUB ?>" value="<?php echo $_media_s ?>">
			<input type="hidden" class="<?php echo self::CLS_TYPE ?>" value="image">
		</div>
	<?php
	}

	private function _output_row_video( $it, $cls ) {
		$_cap   = isset( $it['caption'] ) ? esc_attr( $it['caption'] ) : '';
		$_url   = isset( $it['url'] )     ? esc_attr( $it['url'] )     : '';
		$_media = isset( $it['media'] )   ? esc_attr( $it['media'] )   : '';
		$_video = isset( $it['video'] )   ? esc_url( $it['video'] )    : '';
?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php esc_html_e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo $_cap ?>"></div>
					<div><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</div>
					<div>
						<input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo $_url ?>">
						<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL_URL ?>"><?php _e( 'Select', 'default' ) ?></a>
					</div>
				</div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_VIDEO ?>">
						<video class="<?php echo self::CLS_TN_IMG ?>" src="<?php echo $_video ?>">
					</a>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>">
			<input type="hidden" class="<?php echo self::CLS_TYPE ?>" value="video">
		</div>
<?php
	}


	// -------------------------------------------------------------------------


	private function _save_items( $post_id ) {
		$skeys = [ 'media', 'caption', 'url', 'type', 'delete' ];
		if ( $this->_is_dual ) $skeys[] = 'media_sub';

		$its = \st\field\get_multiple_post_meta_from_post( $this->_key, $skeys );
		$its = array_filter( $its, function ( $it ) { return ! $it['delete']; } );
		$its = array_values( $its );

		foreach ( $its as &$it ) {
			$pid = url_to_postid( $it['url'] );
			if ( $pid !== 0 ) $it['url'] = $pid;
		}
		$skeys = [ 'media', 'caption', 'url', 'type' ];
		if ( $this->_is_dual ) $skeys[] = 'media_sub';
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $its, $skeys );
	}

	private function _get_items( $post_id, $size = 'medium' ) {
		$skeys = [ 'media', 'caption', 'url', 'type' ];
		if ( $this->_is_dual ) $skeys[] = 'media_sub';

		$its = \st\field\get_multiple_post_meta( $post_id, $this->_key, $skeys );

		foreach ( $its as &$it ) {
			if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
				$permalink = get_permalink( $it['url'] );
				if ( $permalink !== false ) {
					$it['post_id'] = $it['url'];
					$it['url'] = $permalink;
				}
			}
			if ( empty( $it['type'] ) ) $it['type'] = self::TYPE_IMAGE;
			$it['image'] = '';
			if ( $it['type'] === self::TYPE_IMAGE ) {
				if ( ! empty( $it['media'] ) ) {
					$this->_get_images( $it, intval( $it['media'] ), $size, 'image', 'images' );
				}
				if ( $this->_is_dual ) {
					$it['image_sub'] = '';
					if ( ! empty( $it['media_sub'] ) ) {
						$this->_get_images( $it, intval( $it['media_sub'] ), $size, 'image_sub', 'images_sub' );
					}
				}
			} else if ( $it['type'] === self::TYPE_VIDEO ) {
				$it['video'] = wp_get_attachment_url( $it['media'] );
			}
		}
		if ( ! is_admin() && $this->_is_shuffled ) shuffle( $its );
		return $its;
	}

	private function _get_images( &$it, $aid, $size, $key, $key_s ) {
		if ( is_array( $size ) ) {
			$imgs = [];
			foreach ( $size as $sz ) {
				$img = wp_get_attachment_image_src( $aid, $sz );
				if ( $img ) $imgs[] = $img[0];
			}
			if ( ! empty( $imgs ) ) {
				$it[ $key_s ] = $imgs;
				$it[ $key   ] = $imgs[ count( $imgs ) - 1 ];
			}
		} else {
			$img = wp_get_attachment_image_src( $aid, $size );
			if ( $img ) {
				$it[ $key_s ] = [ $img[0] ];
				$it[ $key   ] = $img[0];
			}
		}
	}

}


// -----------------------------------------------------------------------------


namespace st\slide_show;

function initialize( $key ) { return new \st\SlideShow( $key ); }
function enqueue_script( $url_to = false ) { \st\SlideShow::enqueue_script( $url_to ); }

function set_duration_time( $key, $sec ) { return \st\SlideShow::get_instance( $key )->set_duration_time( $sec ); }
function set_transition_time( $key, $sec ) { return \st\SlideShow::get_instance( $key )->set_transition_time( $sec ); }
function set_zoom_rate( $key, $rate ) { return \st\SlideShow::get_instance( $key )->set_zoom_rate( $rate ); }
function set_effect_type( $key, $type ) { return \st\SlideShow::get_instance( $key )->set_effect_type( $type ); }
function set_background_opacity( $key, $opacity ) { return \st\SlideShow::get_instance( $key )->set_background_opacity( $opacity ); }
function set_background_visible( $key, $visible ) { return \st\SlideShow::get_instance( $key )->set_background_visible( $visible ); }
function set_side_slide_visible( $key, $visible ) { return \st\SlideShow::get_instance( $key )->set_side_slide_visible( $visible ); }
function set_picture_scroll( $key, $enabled ) { return \st\SlideShow::get_instance( $key )->set_picture_scroll( $enabled ); }
function set_dual_enabled( $key, $enabled ) { return \st\SlideShow::get_instance( $key )->set_dual_enabled( $enabled ); }
function set_caption_type( $key, $type ) { return \st\SlideShow::get_instance( $key )->set_caption_type( $type ); }
function echo_slide_show( $key, $post_id = false, $size = 'large', $cls = '' ) { return \st\SlideShow::get_instance( $key )->echo_slide_show( $post_id, $size, $cls ); }
function echo_slide_items( $key, $post_id = false, $size = 'medium' ) { return \st\SlideShow::get_instance( $key )->echo_slide_items( $post_id, $size ); }

function add_meta_box( $key, $label, $screen, $context = 'side' ) { \st\SlideShow::get_instance( $key )->add_meta_box( $label, $screen, $context ); }
function save_meta_box( $post_id, $key ) { \st\SlideShow::get_instance( $key )->save_meta_box( $post_id ); }
