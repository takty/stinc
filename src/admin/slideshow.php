<?php
namespace st\slideshow;

/**
 *
 * Slideshow (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-12-24
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/text.php';


const NS = 'st_slideshow';

function enqueue_script( $url_to ) {
	if ( is_admin() ) return;
	wp_enqueue_script( 'st-slideshow', $url_to.'/../../../stomp/slideshow.min.js', '', 1.0 );
}

function enqueue_script_for_admin( $url_to ) {
	if ( ! is_admin() ) return;
	wp_enqueue_script( 'st-slideshow', $url_to.'/asset/slideshow.js', array( 'jquery-ui-sortable' ) );
	wp_enqueue_style( 'st-slideshow', $url_to.'/asset/slideshow.css' );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
}

function add_meta_box( $key, $label, $screen ) {
	\add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key ) {
			wp_nonce_field( $key, $key . '_nonce' );
			output_html( $key );
		},
		$screen
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;

	save_post( $key, $post_id );
}

function output_html( $key ) {
?>
	<input type="hidden" id="<?php echo $key ?>" name="<?php echo $key ?>" value="" />
	<div class="<?php echo NS ?>_table" id="<?php echo $key ?>_tbody">
<?php
	output_row( '', '', '', '', NS.'_item_template' );
	foreach ( get_items( $key ) as $it ) {
		output_row( $it['image'], $it['media'], $it['caption'], $it['url'], NS . '_item' );
	}
?>
		<div class="<?php echo NS ?>_add_row"><a href="javascript:void(0);" class="<?php echo NS ?>_add button"><?php echo __( 'Add Media', 'default' ) ?></a></div>
	</div>
	<script>st_slideshow_init('<?php echo $key ?>');</script>
	<textarea id="<?php echo $key ?>_hidden_textarea" style="display: none;"></textarea>
	<div id="<?php echo $key ?>_hidden_div" style="display: none;"></div>
<?php
}

function output_row( $image, $media, $caption, $url, $class ) {
	$media_style = empty( $image ) ? '' : ' style="background-image: url(' . esc_url( $image ) . ')"';
?>
	<div class="<?php echo $class ?>">
		<div>
			<label class="widget-control-remove <?php echo NS ?>_delete_label"><input type="checkbox" class="<?php echo NS ?>_delete"></input><br /><?php echo __( 'Remove', 'default' ) ?></label>
			<div class="<?php echo NS ?>_handle">=</div>
		</div>
		<div>
			<div class="<?php echo NS ?>_info">
				<div><?php _e( 'Caption', 'default' ) ?>:</div>
				<div><input type="text" class="<?php echo NS ?>_caption" value="<?php echo esc_attr( $caption ) ?>" /></div>
				<div><a href="<?php echo esc_url( $url ) ?>" target="_blank">URL</a>:</div>
				<div><input type="text" class="<?php echo NS ?>_url" value="<?php echo esc_attr( $url ) ?>" />
				<a href="javascript:void(0);" class="button <?php echo NS ?>_select_url"><?php echo __( 'Select', 'default' ) ?></a></div>
			</div>
			<div class="<?php echo NS ?>_thumbnail">
				<a href="javascript:void(0);" class="frame <?php echo NS ?>_select_img"><div class="<?php echo NS ?>_thumbnail_img"<?php echo $media_style ?>></div></a>
			</div>
		</div>
		<input type="hidden" class="<?php echo NS ?>_media" value="<?php echo esc_attr( $media ) ?>" />
	</div>
<?php
}

function get_items( $key, $post_id = false, $size = 'medium' ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$items = \st\field\get_multiple_post_meta( $post_id, $key, array( 'media', 'caption', 'url' ) );

	foreach ( $items as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {
			$permalink = get_permalink( $it['url'] );
			if ( $permalink !== false ) {
				$it['post_id'] = $it['url'];
				$it['url'] = $permalink;
			}
		}
		$it['image'] = '';
		if ( ! empty( $it['media'] ) ) {
			$img = wp_get_attachment_image_src( intval( $it['media'] ), $size );
			if ( $img ) $it['image'] = $img[0];
		}
	}
	return $items;
}

function save_post( $key, $post_id ) {
	$items = \st\field\get_multiple_post_meta_from_post( $key, array( 'media', 'caption', 'url', 'delete' ) );
	$items = array_filter( $items, function ( $it ) { return ! $it['delete']; } );
	$items = array_values( $items );
	foreach ( $items as &$it ) {
		$pid = url_to_postid( $it['url'] );
		if ( $pid !== 0 ) $it['url'] = $pid;
	}
	\st\field\update_multiple_post_meta( $post_id, $key, $items, array( 'media', 'caption', 'url' ) );
}

function the_slideshow( $key, $post_id = false, $show_bg = true, $size = 'large', $class = '', $effect = 'slide', $caption_circle = false, $burns_rate = 1.05 ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$slides = get_items( $key, $post_id, $size );
	if ( count( $slides ) === 0 ) return;
	$cap_class = $caption_circle ? ' st-slideshow-caption-circle' : '';
?>
	<section class="st-slideshow<?php echo empty( $class ) ? '' : (' ' . $class) ?>" id="<?php echo $key.'_'.$post_id ?>">
		<div class="st-slideshow-strip">
			<ul class="st-slideshow-slides">
<?php
			foreach ( $slides as $s ) {
				$cap = $s['caption'];
				if ( ! empty( $cap ) ) {
					$cap_lines = \st\separate_line( $cap );
					if ( count( $cap_lines ) === 1 ) {
						$cap = '<div class="st-slideshow-caption' . $cap_class . '">' . esc_html( $cap ) . '</div>';
					} else {
						$cap = '<div class="st-slideshow-caption' . $cap_class . '"><div><span>' . implode( '</span></div><div><span>', array_map( 'esc_html', $cap_lines ) ) . '</span></div></div>';
					}
				}
				$cont = (!empty( $s['url'] )) ? ('<a href="' . esc_url( $s['url'] ) . '">' . $cap . '</a>') : $cap;
?>
				<li data-img="<?php echo esc_url( $s['image'] ) ?>"><?php echo $cont ?></li>
<?php
			}
?>
			</ul>
			<div class="st-slideshow-prev"></div>
			<div class="st-slideshow-next"></div>
		</div>
		<div class="st-slideshow-buttons st-slideshow-buttons-overlap"></div>
		<script>st_slideshow_initialize('<?php echo $key . '_' . $post_id ?>', <?php echo $show_bg ? 'true' : 'false'; ?>, '<?php echo $effect ?>', <?php echo $burns_rate ?>);</script>
	</section>
<?php
}

function the_images( $size = 'medium' ) {
	global $post;
	$slides = get_items( '_page_image', $post->ID, $size );
	if ( count( $slides ) === 0 ) return;
	foreach ( $slides as $s ) {
?>
		<li>
<?php if ( ! empty( $s['url'] ) ) echo '<a href=' . esc_url( $s['url'] ) . '>'; ?>
			<div style="background-image: url(<?php echo esc_url( $s['image'] ) ?>)">
<?php if ( ! empty( $s['caption'] ) ) echo $s['caption'] ?>
			</div>
<?php if ( ! empty( $s['url'] ) ) echo '</a>'; ?>
		</li>
<?php
	}
}

function the_gallery_slide_list_items() {
?>
<?php
	$images = \st\slideshow\get_items( GALLERY_KEY );
	$i = 0;
	foreach ( $images as $img ) {
?>
		<li><a href="javascript:void(0)" onclick="st_slideshow_page('<?php echo GALLERY_KEY . '_' . get_the_ID() ?>', <?php echo $i ?>);" style="background-image: url('<?php echo esc_url($img['image']); ?>');"></a></li>
<?php
		$i += 1;
	}
?>
<?php
}
