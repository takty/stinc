<?php
namespace st\video_picker;

/**
 *
 * Video Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-12-11
 *
 */


const NS = 'st_video_picker';

function enqueue_script_for_admin( $url_to ) {
	wp_enqueue_script( 'st-video-picker', $url_to.'/asset/video-picker.js', array( 'jquery-ui-sortable' ) );
	wp_enqueue_style( 'st-video-picker', $url_to.'/asset/video-picker.css' );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
}

function add_meta_box( $key, $label, $screen, $context = 'side' ) {
	\add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key ) {
			wp_nonce_field( $key, $key . '_nonce' );
			output_html( $key );
		},
		$screen, $context
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;
	save_post( $post_id, $key );
}

function output_html( $key ) {
	$item = get_item( $key );
?>
	<div id="<?php echo $key ?>_body">
		<input type="hidden" id="<?php echo $key . '_url' ?>" name="<?php echo $key . '_url' ?>" value="<?php echo esc_attr( $item['url'] ) ?>" />
		<input type="hidden" id="<?php echo $key . '_title' ?>" name="<?php echo $key . '_title' ?>" value="<?php echo esc_attr( $item['title'] ) ?>" />
		<div class="<?php echo NS ?>_title">
			<?php echo $item['title'] ?>
		</div>
		<div class="<?php echo NS ?>_edit_row">
			<a href="javascript:void(0);" class="<?php echo NS ?>_delete widget-control-remove"><?php _e( 'Remove', 'default' ); ?></a>
			<a href="javascript:void(0);" class="<?php echo NS ?>_select button"><?php _e( 'Select', 'default' ); ?></a>
		</div>
		<script>st_video_picker_init('<?php echo $key ?>');</script>
	</div>
<?php
}

function get_item( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );
	if ( $post ) {
		$url = get_post_meta( $post->ID, $key . '_url', TRUE );
		$title = get_post_meta( $post->ID, $key . '_title', TRUE );
		return ['url' => $url, 'title' => $title];
	}
	return false;
}

function save_post( $post_id, $key ) {
	update_post_meta( $post_id, $key . '_url', $_POST[$key . '_url'] );
	update_post_meta( $post_id, $key . '_title', $_POST[$key . '_title'] );
}
