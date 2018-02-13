<?php
namespace st;

/**
 *
 * Slide Show (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-13
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
	const CLS_SEL_URL     = self::NS . '-select-url';
	const CLS_SEL_IMG     = self::NS . '-select-img';
	const CLS_SEL_IMG_SUB = self::NS . '-select-img-sub';
	const CLS_TN          = self::NS . '-thumbnail';
	const CLS_TN_IMG      = self::NS . '-thumbnail-img';
	const CLS_TN_IMG_SUB  = self::NS . '-thumbnail-img-sub';
	const CLS_MEDIA       = self::NS . '-media';
	const CLS_MEDIA_SUB   = self::NS . '-media-sub';

	static private $_instance = null;
	static public function get_instance() { return self::$_instance; }

	private $_key;
	private $_id;
	private $_id_hta;
	private $_id_hd;

	private $_effect_type           = 'slide'; // 'scroll' or 'fade'
	private $_caption_type          = 'subtitle'; // 'line' or 'circle'
	private $_zoom_rate             = 1;
	private $_duration_time         = 8; // [second]
	private $_transition_time       = 1; // [second]
	private $_is_background_visible = true;
	private $_background_opacity    = 0.33;
	private $_is_picture_scroll     = false;
	private $_is_dual               = false;

	public function __construct( $key ) {
		$this->_key      = $key;
		$this->_id       = $key;
		$this->_id_hta   = $key . '-hidden-textarea';
		$this->_id_hd    = $key . '-hidden-div';

		if ( self::$_instance === null ) self::$_instance = $this;
	}

	public function enqueue_script( $url_to ) {
		if ( is_admin() ) {
			wp_enqueue_script( self::NS, $url_to . '/asset/slide-show.min.js', [ 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/slide-show.min.css' );
		} else {
			wp_enqueue_script( self::NS, $url_to . '/../../../stomp/slide-show/slide-show.min.js', '', 1.0 );
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

	public function set_background_visible( $visible ) {
		$this->_is_background_visible = $visible;
		return $this;
	}

	public function set_background_opacity( $opacity ) {
		$this->_background_opacity = $opacity;
		return $this;
	}

	public function set_picture_scroll( $do ) {
		$this->_is_picture_scroll = $do;
		return $this;
	}

	public function set_dual_enabled( $enabled ) {
		$this->_is_dual = $enabled;
		return $this;
	}

	public function echo_slide_show( $post_id = false, $size = 'large', $class = '' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$ss = $this->_get_slides( $post_id, $size );
		if ( empty( $ss ) ) return false;
		$opts = [
			'effect_type'           => $this->_effect_type,
			'is_background_visible' => $this->_is_background_visible,
			'duration_time'         => $this->_duration_time,
			'transition_time'       => $this->_transition_time,
			'zoom_rate'             => $this->_zoom_rate,
			'background_opacity'    => $this->_background_opacity,
			'picture_scroll'        => $this->_is_picture_scroll,
		];
		$opts_str = json_encode( $opts );
	?>
		<section class="<?php echo self::NS . ( empty( $class ) ? '' : ( ' ' . $class ) ) ?>" id="<?php echo "{$this->_id}_$post_id" ?>">
			<div class="<?php echo self::CLS_STRIP ?>">
				<ul class="<?php echo self::CLS_SLIDES ?>">
	<?php
				foreach ( $ss as $s ) $this->_echo_slide_item( $s['url'], $s['caption'], $s['images'], isset( $s['images_sub'] ) ? $s['images_sub'] : false  );
	?>
				</ul>
				<div class="<?php echo self::CLS_PREV ?>"></div>
				<div class="<?php echo self::CLS_NEXT ?>"></div>
			</div>
			<div class="<?php echo self::CLS_RIVETS ?>"></div>
			<script>st_slide_show_initialize('<?php echo "{$this->_id}_$post_id" ?>', <?php echo $opts_str ?>);</script>
		</section>
	<?php
		return true;
	}

	private function _echo_slide_item( $url, $cap, $imgs, $imgs_sub ) {
		$cap_div = '';
		if ( ! empty( $cap ) ) {
			$cap_sr = \st\separate_line( $cap, 'segment_raw' );
			$cap_str = '<div><span>' . implode( '</span></div><div><span>', $cap_sr ) . '</span></div>';
			$cap_div = '<div class="' . self::CLS_CAP . ' ' . $this->_caption_type . '">' . $cap_str . '</div>';
		}
		$eu_url = esc_url( $url );
		$cont = ( ! empty( $url ) ) ? ( "<a href=\"$eu_url\">$cap_div</a>" ) : $cap_div;
		$data = [];
		if ( $this->_is_dual && $imgs_sub !== false ) {
			if ( 2 <= count( $imgs_sub ) ) {
				$data['img-sub-phone'] = esc_url( $imgs_sub[0] );
				$data['img-sub']       = esc_url( $imgs_sub[1] );
			} else {
				$data['img-sub'] = esc_url( $imgs_sub[0] );
			}
		}
		if ( 2 <= count( $imgs ) ) {
			$data['img-phone'] = esc_url( $imgs[0] );
			$data['img']       = esc_url( $imgs[1] );
		} else {
			$data['img'] = esc_url( $imgs[0] );
		}
		$attr = '';
		foreach ( $data as $key => $val ) {
			$attr .= " data-$key=\"$val\"";
		}
		echo "<li$attr>$cont</li>";
	}

	public function echo_slides( $post_id = false, $size = 'medium' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$ss = $this->_get_slides( $post_id, $size );
		foreach ( $ss as $s ) {
			$img = esc_url( $s['image'] );
			$style = "background-image: url('{$img}');";
	?>
			<li>
	<?php if ( ! empty( $s['url'] ) ) echo '<a href=' . esc_url( $s['url'] ) . '>'; ?>
				<div style="<?php echo $style ?>">
	<?php if ( ! empty( $s['caption'] ) ) echo esc_html( $s['caption'] ) ?>
				</div>
	<?php if ( ! empty( $s['url'] ) ) echo '</a>'; ?>
			</li>
	<?php
		}
	}

	public function echo_slide_items( $post_id = false, $size = 'medium' ) {
		if ( $post_id === false ) $post_id = get_the_ID();
		$ss = $this->_get_slides( $post_id, $size );
		foreach ( $ss as $idx => $s ) {
			$img = esc_url( $s['image'] );
			$style = "background-image: url('{$img}');";
			$event = "st_slide_show_page('{$this->_id}_$post_id', {$idx});"
	?>
			<li><a href="javascript:void(0)" onclick="<?php echo $event ?>" style="<?php echo $style ?>"></a></li>
	<?php
		}
	}


	// -------------------------------------------------------------------------

	public function add_meta_box( $label, $screen ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST[ "{$this->_key}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->_key}_nonce" ], $this->_key ) ) return;
		$this->_save_slides( $post_id );
	}

	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
?>
		<input type="hidden" id="<?php echo $this->_id ?>" name="<?php echo $this->_id ?>" value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo self::CLS_TABLE ?>">
<?php
		$this->_output_row( '', '', self::CLS_ITEM_TEMP, '', '', '', '' );
		foreach ( $this->_get_slides( $post->ID ) as $it ) {
			if ( $this->_is_dual ) {
				$this->_output_row( $it['caption'], $it['url'], self::CLS_ITEM, $it['image'], $it['media'], $it['image_sub'], $it['media_sub']  );
			} else {
				$this->_output_row( $it['caption'], $it['url'], self::CLS_ITEM, $it['image'], $it['media'] );
			}
		}
?>
				<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php echo __( 'Add Media', 'default' ) ?></a></div>
			</div>
			<script>st_slide_show_initialize_admin('<?php echo $this->_id ?>', <?php echo $this->_is_dual ? 'true' : 'false' ?>);</script>
			<textarea id="<?php echo $this->_id_hta ?>" style="display: none;"></textarea>
			<div id="<?php echo $this->_id_hd ?>" style="display: none;"></div>
		</div>
	<?php
	}

	private function _output_row( $caption, $url, $class, $image, $media, $image_sub = '', $media_sub = '' ) {
		$media_style = empty( $image ) ? '' : ' style="background-image:url(' . esc_url( $image ) . ')"';
		if ( $this->_is_dual ) {
			$media_sub_style = empty( $image_sub ) ? '' : ' style="background-image:url(' . esc_url( $image_sub ) . ')"';
		}
	?>
		<div class="<?php echo $class ?>">
			<div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></input><br /><?php echo __( 'Remove', 'default' ) ?></label>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
			</div>
			<div>
				<div class="<?php echo self::CLS_INFO ?>">
					<div><?php _e( 'Caption', 'default' ) ?>:</div>
					<div><input type="text" class="<?php echo self::CLS_CAP ?>" value="<?php echo esc_attr( $caption ) ?>" /></div>
					<div><a href="<?php echo esc_url( $url ) ?>" target="_blank">URL</a>:</div>
					<div><input type="text" class="<?php echo self::CLS_URL ?>" value="<?php echo esc_attr( $url ) ?>" />
					<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL_URL ?>"><?php echo __( 'Select', 'default' ) ?></a></div>
				</div>
		<?php if ( $this->_is_dual ) : ?>
				<div class="st-slide-show-thumbnail-wrap">
		<?php endif; ?>
					<div class="<?php echo self::CLS_TN ?>">
						<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>"><div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $media_style ?>></div></a>
					</div>
		<?php if ( $this->_is_dual ) : ?>
					<div class="<?php echo self::CLS_TN ?>">
						<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG_SUB ?>"><div class="<?php echo self::CLS_TN_IMG_SUB ?>"<?php echo $media_sub_style ?>></div></a>
					</div>
				</div>
		<?php endif; ?>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo esc_attr( $media ) ?>" />
		<?php if ( $this->_is_dual ) : ?>
			<input type="hidden" class="<?php echo self::CLS_MEDIA_SUB ?>" value="<?php echo esc_attr( $media_sub ) ?>" />
		<?php endif; ?>
		</div>
	<?php
	}


	// -------------------------------------------------------------------------

	private function _save_slides( $post_id ) {
		$keys = [ 'media', 'caption', 'url', 'delete' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';

		$ss = \st\field\get_multiple_post_meta_from_post( $this->_key, $keys );
		$ss = array_filter( $ss, function ( $s ) { return ! $s['delete']; } );
		$ss = array_values( $ss );
		foreach ( $ss as &$s ) {
			$pid = url_to_postid( $s['url'] );
			if ( $pid !== 0 ) $s['url'] = $pid;
		}
		$keys = [ 'media', 'caption', 'url' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $ss, $keys );
	}

	private function _get_slides( $post_id, $size = 'medium' ) {
		$keys = [ 'media', 'caption', 'url' ];
		if ( $this->_is_dual ) $keys[] = 'media_sub';
		$ss = \st\field\get_multiple_post_meta( $post_id, $this->_key, $keys );

		foreach ( $ss as &$s ) {
			if ( isset( $s['url'] ) && is_numeric( $s['url'] ) ) {
				$permalink = get_permalink( $s['url'] );
				if ( $permalink !== false ) {
					$s['post_id'] = $s['url'];
					$s['url'] = $permalink;
				}
			}
			$s['image'] = '';
			if ( ! empty( $s['media'] ) ) {
				$this->_get_images( $s, intval( $s['media'] ), $size, 'image', 'images' );
			}
			if ( $this->_is_dual ) {
				$s['image_sub'] = '';
				if ( ! empty( $s['media_sub'] ) ) {
					$this->_get_images( $s, intval( $s['media_sub'] ), $size, 'image_sub', 'images_sub' );
				}
			}

			// $aid = intval( $s['media'] );
			// if ( is_array( $size ) ) {
			// 	$imgs = [];
			// 	foreach ( $size as $sz ) {
			// 		$img = wp_get_attachment_image_src( $aid, $sz );
			// 		if ( $img ) $imgs[] = $img[0];
			// 	}
			// 	if ( ! empty( $imgs ) ) {
			// 		$s['images'] = $imgs;
			// 		$s['image'] = $imgs[ count( $imgs ) - 1 ];
			// 	}
			// } else {
			// 	$img = wp_get_attachment_image_src( $aid, $size );
			// 	if ( $img ) $s['image'] = $img[0];
			// }
		}
		return $ss;
	}

	private function _get_images( &$s, $aid, $size, $key, $key_s ) {
		if ( is_array( $size ) ) {
			$imgs = [];
			foreach ( $size as $sz ) {
				$img = wp_get_attachment_image_src( $aid, $sz );
				if ( $img ) $imgs[] = $img[0];
			}
			if ( ! empty( $imgs ) ) {
				$s[ $key_s ] = $imgs;
				$s[ $key ]   = $imgs[ count( $imgs ) - 1 ];
			}
		} else {
			$img = wp_get_attachment_image_src( $aid, $size );
			if ( $img ) {
				$s[ $key_s ] = [ $img[0] ];
				$s[ $key ]   = $img[0];
			}
		}
	}

}
