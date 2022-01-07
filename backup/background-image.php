<?php
/**
 * Background Images (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-06-24
 */

namespace st;

require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../util/url.php';
require_once __DIR__ . '/../admin/ss-support.php';


class BackgroundImage {

	const NS = 'st-background-image';

	// Background Images
	const CLS_STRIP  = self::NS . '-strip';
	const CLS_SLIDES = self::NS . '-slides';

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
	const CLS_SEL_IMG         = self::NS . '-select-img';
	const CLS_SEL_VIDEO       = self::NS . '-select-video';
	const CLS_TN              = self::NS . '-thumbnail';
	const CLS_TN_IMG          = self::NS . '-thumbnail-img';
	const CLS_TN_NAME         = self::NS . '-thumbnail-name';

	const CLS_TYPE            = self::NS . '-type';
	const CLS_MEDIA           = self::NS . '-media';
	const CLS_TITLE           = self::NS . '-title';
	const CLS_FILENAME        = self::NS . '-filename';

	const TYPE_IMAGE = 'image';
	const TYPE_VIDEO = 'video';

	private static $_instance     = array();
	private static $_is_ss_active = null;

	public static function get_instance( $key = false ) {
		if ( $key === false ) {
			return reset( self::$_instance );
		}
		if ( isset( self::$_instance[ $key ] ) ) {
			return self::$_instance[ $key ];
		}
		return new BackgroundImage( $key );
	}

	public static function enqueue_script( $url_to = false ) {
		if ( $url_to === false ) {
			$url_to = \st\get_file_uri( __DIR__ );
		}
		if ( is_admin() ) {
			wp_enqueue_script( 'picker-media', \st\abs_url( $url_to, './asset/lib/picker-media.min.js' ), array(), 1.0, true );
			wp_enqueue_script( self::NS, \st\abs_url( $url_to, './asset/background-image.min.js' ), array( 'picker-media', 'jquery-ui-sortable' ) );
			wp_enqueue_style( self::NS, \st\abs_url( $url_to, './asset/background-image.min.css' ) );
		} else {
			wp_enqueue_script( self::NS, \st\abs_url( $url_to, './../../stomp/background-image/background-image.min.js' ), '', 1.0 );
		}
	}

	private static function is_simply_static_active() {
		if ( null === self::$_is_ss_active ) {
			self::$_is_ss_active = get_option( 'is_simply_static_active', false );
		}
		return self::$_is_ss_active;
	}

	private $_key;
	private $_id;

	private $_effect_type      = 'fade'; // 'slide' or 'scroll' or 'fade'
	private $_duration_time    = 8; // [second]
	private $_transition_time  = 1; // [second]
	private $_is_random_timing = true;
	private $_is_autoplay      = true;
	private $_zoom_rate        = 1;

	private $_is_video_enabled = false;
	private $_is_shuffled      = false;
	private $_is_script_output = true;

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

	public function set_random_timing_enabled( $enabled ) {
		$this->_is_random_timing = $enabled;
		return $this;
	}

	public function set_autoplay_enabled( $enabled ) {
		$this->_is_autoplay = $enabled;
		return $this;
	}

	public function set_zoom_rate( $rate ) {
		$this->_zoom_rate = $rate;
		return $this;
	}

	public function set_video_enabled( $enabled ) {
		$this->_is_video_enabled = $enabled;
		return $this;
	}

	public function set_shuffled( $enabled ) {
		$this->_is_shuffled = $enabled;
		return $this;
	}

	public function set_script_output( $enabled ) {
		$this->_is_script_output = $enabled;
		return $this;
	}

	private function _create_option_str() {
		$opts = array(
			'effect_type'      => $this->_effect_type,
			'duration_time'    => $this->_duration_time,
			'transition_time'  => $this->_transition_time,
			'is_random_timing' => $this->_is_random_timing,
			'is_autoplay'      => $this->_is_autoplay,
			'zoom_rate'        => $this->_zoom_rate,
		);
		return json_encode( $opts );
	}

	public function echo_background_image( $post_id = false, $size = 'large', $cls = '' ) {
		if ( false === $post_id ) {
			$post_id = get_the_ID();
		}
		$its = $this->_get_items( $post_id, $size );
		if ( empty( $its ) ) {
			return false;
		}
		$dom_id   = "{$this->_id}-$post_id";
		$dom_cls  = self::NS . ( empty( $cls ) ? '' : ( ' ' . $cls ) );
		$opts_str = $this->_create_option_str();
		$_urls    = array();
?>
		<section class="<?php echo $dom_cls; ?>" id="<?php echo $dom_id; ?>">
			<div class="<?php echo self::CLS_STRIP; ?>">
				<ul class="<?php echo self::CLS_SLIDES; ?>">
<?php
		foreach ( $its as $it ) {
			if ( self::TYPE_IMAGE === $it['type'] ) {
				$this->_echo_slide_item_img( $it, $_urls );
			} elseif ( self::TYPE_VIDEO === $it['type'] ) {
				$this->_echo_slide_item_video( $it, $_urls );
			}
		}
		?>
				</ul>
			</div>
<?php if ( $this->_is_script_output ) : ?>
			<script>st_background_image_initialize('<?php echo $dom_id ?>', <?php echo $opts_str ?>);</script>
<?php endif; ?>
			<?php
			if ( self::is_simply_static_active() ) {
				echo $this->_create_dummy_style( $_urls );
			}
			?>
		</section>
		<?php
		return true;
	}

	private function _echo_slide_item_img( $it, &$_urls ) {
		$imgs = $it['images'];

		if ( 2 <= count( $imgs ) ) {
			$_img0 = esc_url( $imgs[0] );
			$_img1 = esc_url( $imgs[1] );
			$attr = " data-img=\"$_img1\" data-img-phone=\"$_img0\"";
			if ( self::is_simply_static_active() ) {  // for fallback
				$_urls[] = $_img0;
				$_urls[] = $_img1;
			}
		} else {
			$_img = esc_url( $imgs[0] );
			$attr = " data-img=\"$_img\"";
			if ( self::is_simply_static_active() ) {  // for fallback
				$_urls[] = $_img;
			}
		}
		echo "<li$attr></li>";
	}

	private function _echo_slide_item_video( $it, &$_urls ) {
		$_url = esc_url( $it['video'] );
		$attr = " data-video=\"$_url\"";

		if ( self::is_simply_static_active() ) {  // for fallback
			$_urls[] = $_url;
		}
		echo "<li$attr></li>";
	}

	private function _create_dummy_style( $_urls ) {
		$style = '<style>stinc{';
		foreach ( $_urls as $_url ) {
			$style .= "p:url('$_url');";
		}
		$style .= '}</style>';
		return $style;
	}


	// -------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'side' ) {
		\add_meta_box(
			"{$this->_key}_mb",
			$label,
			array( $this, '_cb_output_html' ),
			$screen,
			$context
		);
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST[ "{$this->_key}_nonce" ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST[ "{$this->_key}_nonce" ], $this->_key ) ) {
			return;
		}
		$this->_save_items( $post_id );
	}


	// -------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$its = $this->_get_items( $post->ID );
		?>
		<input type="hidden" <?php \st\field\name_id( $this->_id ); ?> value="" />
		<div class="<?php echo self::CLS_BODY; ?>">
			<div class="<?php echo self::CLS_TABLE; ?>">
		<?php
		$this->_output_row_image( array(), self::CLS_ITEM_TEMP_IMG );
		$this->_output_row_video( array(), self::CLS_ITEM_TEMP_VIDEO );
		foreach ( $its as $it ) {
			if ( self::TYPE_IMAGE === $it['type'] ) {
				$this->_output_row_image( $it, self::CLS_ITEM );
			} elseif ( self::TYPE_VIDEO === $it['type'] ) {
				$this->_output_row_video( $it, self::CLS_ITEM );
			}
		}
		?>
			</div>
			<div class="<?php echo self::CLS_ADD_ROW; ?>">
		<?php
		if ( $this->_is_video_enabled ) {
			?>
				<a href="javascript:void(0);" class="<?php echo self::CLS_ADD_VIDEO; ?> button"><?php _e( 'Add Video', 'default' ) ?></a>
			<?php
		}
		?>
				<a href="javascript:void(0);" class="<?php echo self::CLS_ADD_IMG; ?> button"><?php _e( 'Add Media', 'default' ) ?></a>
			</div>
			<script>window.addEventListener('load', function () {
				st_background_image_initialize_admin('<?php echo $this->_id; ?>');
			});</script>
		</div>
		<?php
	}

	private function _output_row_image( $it, $cls ) {
		$_img   = isset( $it['image'] ) ? esc_url( $it['image'] )  : '';
		$_media = isset( $it['media'] ) ? esc_attr( $it['media'] ) : '';
		$_style = empty( $_img ) ? '' : " style=\"background-image:url($_img)\"";

		$_title = isset( $it['title'] ) ? esc_attr( $it['title'] ) : '';
		$_fn    = isset( $it['filename'] ) ? esc_attr( $it['filename'] ) : '';

		if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) {
			$_title = '';
		}
		?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo self::CLS_DEL ?>" /></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_IMG ?>" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<div class="<?php echo self::CLS_TN_IMG ?>"<?php echo $_style ?>></div>
					</a>
					<div class="<?php echo self::CLS_TN_NAME ?>">
						<div class="<?php echo self::CLS_TITLE ?>"><?php echo $_title ?></div>
						<div class="<?php echo self::CLS_FILENAME ?>"><?php echo $_fn ?></div>
					</div>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>" />
			<input type="hidden" class="<?php echo self::CLS_TYPE ?>" value="image">
		</div>
		<?php
	}

	private function _output_row_video( $it, $cls ) {
		$_video = isset( $it['video'] ) ? esc_url( $it['video'] ) : '';
		$_media = isset( $it['media'] ) ? esc_attr( $it['media'] ) : '';

		$_title = isset( $it['title'] ) ? esc_attr( $it['title'] ) : '';
		$_fn    = isset( $it['filename'] ) ? esc_attr( $it['filename'] ) : '';

		if ( ! empty( $_title ) && strlen( $_title ) < strlen( $_fn ) && strpos( $_fn, $_title ) === 0 ) {
			$_title = '';
		}
		?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br><input type="checkbox" class="<?php echo self::CLS_DEL ?>"></label>
			</div>
			<div>
				<div class="<?php echo self::CLS_TN ?>">
					<a href="javascript:void(0);" class="frame <?php echo self::CLS_SEL_VIDEO ?>" title="<?php echo "$_title&#x0A;$_fn" ?>">
						<video class="<?php echo self::CLS_TN_IMG ?>" src="<?php echo $_video ?>">
					</a>
					<div class="<?php echo self::CLS_TN_NAME ?>">
						<div class="<?php echo self::CLS_TITLE ?>"><?php echo $_title ?></div>
						<div class="<?php echo self::CLS_FILENAME ?>"><?php echo $_fn ?></div>
					</div>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_MEDIA ?>" value="<?php echo $_media ?>">
			<input type="hidden" class="<?php echo self::CLS_TYPE ?>" value="video">
		</div>
		<?php
	}


	// -------------------------------------------------------------------------


	private function _save_items( $post_id ) {
		$its = \st\field\get_multiple_post_meta_from_post( $this->_key, array( 'media', 'type', 'delete' ) );
		$its = array_filter(
			$its,
			function ( $it ) {
				return ! $it['delete'];
			}
		);
		$its = array_values( $its );

		\st\field\update_multiple_post_meta( $post_id, $this->_key, $its, array( 'media', 'type' ) );
	}

	private function _get_items( $post_id, $size = 'medium' ) {
		$its = \st\field\get_multiple_post_meta( $post_id, $this->_key, array( 'media', 'type' ) );

		foreach ( $its as &$it ) {
			if ( empty( $it['type'] ) ) {
				$it['type'] = self::TYPE_IMAGE;
			}
			$it['image'] = '';
			if ( empty( $it['media'] ) ) {
				continue;
			}
			$aid = (int) $it['media'];

			if ( self::TYPE_IMAGE === $it['type'] ) {
				$this->_get_images( $it, $aid, $size );
			} elseif ( self::TYPE_VIDEO === $it['type'] ) {
				$it['video'] = wp_get_attachment_url( $aid );
			}
			$am = $this->_get_image_meta( $aid );
			if ( $am ) {
				$it = array_merge( $it, $am );
			}
		}
		if ( ! is_admin() && $this->_is_shuffled ) {
			shuffle( $its );
		}
		return $its;
	}

	private function _get_images( &$it, $aid, $size, $pf = '' ) {
		if ( is_array( $size ) ) {
			$imgs = array();
			foreach ( $size as $sz ) {
				$img = wp_get_attachment_image_src( $aid, $sz );
				if ( $img ) {
					$imgs[] = $img[0];
				}
			}
			if ( ! empty( $imgs ) ) {
				$it[ "images$pf" ] = $imgs;
				$it[ "image$pf" ] = $imgs[ count( $imgs ) - 1 ];
			}
		} else {
			$img = wp_get_attachment_image_src( $aid, $size );
			if ( $img ) {
				$it[ "images$pf" ] = array( $img[0] );
				$it[ "image$pf" ] = $img[0];
			}
		}
		$am = $this->_get_image_meta( $aid, $pf );
		if ( $am ) {
			$it = array_merge( $it, $am );
		}
	}

	private function _get_image_meta( $aid, $pf = '' ) {
		$p = get_post( $aid );
		if ( null === $p ) {
			return null;
		}
		$t  = $p->post_title;
		$fn = basename( $p->guid );
		return array(
			"title$pf"    => $t,
			"filename$pf" => $fn,
		);
	}

}


// -----------------------------------------------------------------------------


namespace st\background_image;

function initialize( $key ) {
	return new \st\BackgroundImage( $key );
}
function enqueue_script( $url_to = false ) {
	\st\BackgroundImage::enqueue_script( $url_to );
}

function set_effect_type( $key, $type ) {
	return \st\BackgroundImage::get_instance( $key )->set_effect_type( $type );
}
function set_duration_time( $key, $sec ) {
	return \st\BackgroundImage::get_instance( $key )->set_duration_time( $sec );
}
function set_transition_time( $key, $sec ) {
	return \st\BackgroundImage::get_instance( $key )->set_transition_time( $sec );
}
function set_random_timing_enabled( $key, $enabled ) {
	return \st\BackgroundImage::get_instance( $key )->set_random_timing_enabled( $enabled );
}
function set_autoplay_enabled( $key, $enabled ) {
	return \st\BackgroundImage::get_instance( $key )->set_autoplay_enabled( $enabled );
}
function set_zoom_rate( $key, $rate ) {
	return \st\BackgroundImage::get_instance( $key )->set_zoom_rate( $rate );
}
function set_video_enabled( $key, $enabled ) {
	return \st\BackgroundImage::get_instance( $key )->set_video_enabled( $enabled );
}
function set_shuffled( $key, $enabled ) {
	return \st\BackgroundImage::get_instance( $key )->set_shuffled( $enabled );
}
function set_script_output( $key, $enabled ) {
	return \st\BackgroundImage::get_instance( $key )->set_script_output( $enabled );
}

function echo_background_image( $key, $post_id = false, $size = 'large', $cls = '' ) {
	return \st\BackgroundImage::get_instance( $key )->echo_background_image( $post_id, $size, $cls );
}

function add_meta_box( $key, $label, $screen, $context = 'side' ) {
	\st\BackgroundImage::get_instance( $key )->add_meta_box( $label, $screen, $context );
}
function save_meta_box( $post_id, $key ) {
	\st\BackgroundImage::get_instance( $key )->save_meta_box( $post_id );
}
