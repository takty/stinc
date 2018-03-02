<?php
namespace st\post_thumbnail;

/**
 *
 * Custom Post Thumbnail (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-02
 *
 */


const NS = 'st_post_thumbnail';

function enqueue_script_for_admin( $url_to ) {
	if ( ! is_admin() ) return;
	wp_enqueue_script( 'st-post-thumbnail', $url_to.'/asset/post-thumbnail.js' );
	wp_enqueue_style( 'st-post-thumbnail', $url_to.'/asset/post-thumbnail.css' );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
}

function output_html( $key ) {
	$item = get_item( $key );
	if ( ! empty( $item['id'] ) ) {
		$src = wp_get_attachment_image_src( $item['id'], 'medium' )[0];
		$style = "background-image: url('" . esc_url( $src ) . "'); padding-bottom: 66.66%;";
	} else {
		$style = "padding-bottom: 0;";
	}
?>
	<div id="<?= $key ?>_body">
		<input type="hidden" id="<?php echo $key . '_id' ?>" name="<?php echo $key . '_id' ?>" value="<?php echo esc_attr( $item['id'] ) ?>" />
		<div class="<?= NS ?>_image" id="<?php echo $key . '_image' ?>" style="<?php echo $style ?>"></div>
		<div class="<?= NS ?>_edit_row">
			<a href="javascript:void(0);" class="<?= NS ?>_delete widget-control-remove"><?php _e( 'Remove', 'default' ); ?></a>
			<a href="javascript:void(0);" class="<?= NS ?>_select button"><?php _e( 'Select', 'default' ); ?></a>
		</div>
		<script>st_post_thumbnail_init('<?php echo $key ?>');</script>
	</div>
<?php
}

function get_item( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );
	$id = get_post_meta( $post->ID, $key . '_id', TRUE );
	return [ 'id' => $id ];
}

function save_post( $key, $post_id ) {
	update_post_meta( $post_id, $key . '_id', $_POST[$key . '_id'] );
}
