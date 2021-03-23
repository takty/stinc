<?php
/**
 *
 * Custom Post Thumbnail (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 *
 */

namespace st\post_thumbnail;

require_once __DIR__ . '/../system/field.php';


const NS = 'st-post-thumbnail';

function enqueue_script( $url_to = false ) {
	if ( is_admin() ) {
		if ( false === $url_to ) {
			$url_to = \st\get_file_uri( __DIR__ );
		}
		$url_to = untrailingslashit( $url_to );
		wp_enqueue_script( 'picker-media', $url_to . '/asset/lib/picker-media.min.js', array(), 1.0, true );
		wp_enqueue_script( NS, $url_to . '/asset/post-thumbnail.min.js', array( 'picker-media' ) );
		wp_enqueue_style(  NS, $url_to . '/asset/post-thumbnail.min.css' );
	}
}

function get_item( $key, $post_id = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}
	$media = get_post_meta( $post_id, "{$key}_media", true );

	// For Backward Compatibility.
	if ( empty( $media ) ) {
		$media = get_post_meta( $post_id, "{$key}_id", true );
		if ( ! empty( $media ) ) {
			update_post_meta( $post_id, "{$key}_media", $media );
		}
	}
	$id = $media;

	return compact( 'media', 'id' );
}


// -----------------------------------------------------------------------------


function add_meta_box( $key, $label, $screen, $context = 'side' ) {
	\add_meta_box(
		"{$key}_mb",
		$label,
		function ( $post ) use ( $key ) {
			_cb_output_html( $key, $post );
		},
		$screen,
		$context
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[ "{$key}_nonce" ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST[ "{$key}_nonce" ], $key ) ) {
		return;
	}
	_save_item( $key, $post_id );
}


// -----------------------------------------------------------------------------


function _cb_output_html( $key, $post ) {
	wp_nonce_field( $key, "{$key}_nonce" );
	$it = get_item( $key, $post->ID );

	$_media   = $it['media'];
	$id_media = "{$key}_media";

	if ( empty( $it['media'] ) ) {
		$style = "padding-bottom: 0;";
	} else {
		$src   = wp_get_attachment_image_src( $it['media'], 'medium' )[0];
		$style = "background-image: url('" . esc_url( $src ) . "'); padding-bottom: 66.66%;";
	}
?>
	<div id="<?php echo esc_attr( $key ); ?>">
		<div class="<?php echo esc_attr( NS ); ?>-img" style="<?php echo $style ?>"></div>
		<div class="<?php echo esc_attr( NS ); ?>-row">
			<a href="javascript:void(0);" class="<?php echo esc_attr( NS ); ?>-delete widget-control-remove"><?php esc_html_e( 'Remove', 'default' ); ?></a>
			<a href="javascript:void(0);" class="<?php echo esc_attr( NS ); ?>-select button"><?php esc_html_e( 'Select', 'default' ); ?></a>
		</div>
		<input type="hidden" <?php \st\field\name_id( $id_media ); ?> value="<?php echo esc_attr( $media ); ?>" />
		<script>window.addEventListener('load', function () {
			st_post_thumbnail_init('<?php echo esc_attr( $key ); ?>');
		});</script>
	</div>
<?php
}

function _save_item( $key, $post_id ) {
	update_post_meta( $post_id, "{$key}_media", $_POST[ "{$key}_media" ] );
}
