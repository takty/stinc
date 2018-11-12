<?php
namespace st\single_media_picker;

/**
 *
 * Single Media Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-12
 *
 */


const NS = 'st-single-media-picker';

const CLS_BODY    = NS . '-body';
const CLS_ITEM    = NS . '-item';
const CLS_ITEM_IR = NS . '-item-inside-row';
const CLS_DEL     = NS . '-delete';
const CLS_SEL_ROW = NS . '-select-row';
const CLS_SEL     = NS . '-select';
const CLS_TITLE   = NS . '-title';
const CLS_NAME    = NS . '-name';


function get_item( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );

	$media    = get_post_meta( $post->ID, "{$key}_media",    true );
	$url      = get_post_meta( $post->ID, "{$key}_url",      true );
	$title    = get_post_meta( $post->ID, "{$key}_title",    true );
	$filename = get_post_meta( $post->ID, "{$key}_filename", true );

	// For compatibility
	if ( empty( $media ) ) $media = get_post_meta( $post->ID, "{$key}_id", true );
	$id = $media;

	return compact( 'media', 'url', 'title', 'filename', 'id' );
}


// -----------------------------------------------------------------------------


function enqueue_script( $url_to ) {
	$url_to = untrailingslashit( $url_to );
	if ( is_admin() ) {
		wp_enqueue_script( NS, $url_to . '/asset/single-media-picker.min.js' );
		wp_enqueue_style(  NS, $url_to . '/asset/single-media-picker.min.css' );
	}
}

function add_meta_box( $key, $label, $screen, $context = 'side', $title_editable = true ) {
	\add_meta_box(
		"{$key}_mb", $label,
		function ( $post ) use ( $key, $title_editable ) { _output_html( $key, $title_editable ); },
		$screen, $context
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;
	_save_item( $post_id, $key );
}

function _output_html( $key, $title_editable = true ) {
	wp_nonce_field( $key, "{$key}_nonce" );
	$item = get_item( $key );

	$_url   = isset( $item['url'] )      ? esc_attr( $item['url'] )      : '';
	$_name  = isset( $item['filename'] ) ? esc_html( $item['filename'] ) : '';
	$_title = isset( $item['title'] )    ? esc_attr( $item['title'] )    : '';
?>
	<div id="<?php echo $key ?>"></div>
	<div class="<?php echo CLS_BODY ?>">
		<div class="<?php echo CLS_ITEM ?>">
			<div>
				<a href="javascript:void(0);" class="<?php echo CLS_DEL ?> widget-control-remove"><?php _e( 'Remove', 'default' ); ?></a>
			</div>
			<div>
				<div class="<?php echo CLS_ITEM_IR ?>">
					<span class="post-attributes-label"><?php _e( 'Title', 'default' ) ?>:</span>
					<input <?php if ( ! $title_editable ) echo 'readonly="readonly"' ?> type="text" <?php \st\field\esc_key_e( "{$$key}_title" ) ?> value="<?php echo $_title ?>" />
				</div>
				<div class="<?php echo CLS_ITEM_IR ?>">
					<span><a href="<?php echo $_url ?>" target="_blank"><?php _e( 'File name:', 'default' ) ?></a></span>
					<span class="<?php echo CLS_NAME ?>"><?php echo $_name ?></span>
					<a href="javascript:void(0);" class="button <?php echo CLS_SEL ?>"><?php _e( 'Select', 'default' ) ?></a>
				</div>
			</div>
		</div>
		<div class="<?php echo CLS_SEL_ROW ?>"><a href="javascript:void(0);" class="<?php echo CLS_SEL ?> button"><?php _e( 'Add Media', 'default' ); ?></a></div>
		<script>st_single_media_picker_initialize_admin('<?php echo $key ?>');</script>
		<?php _output_hidden_fields( $key, $item, [ 'media', 'url', 'filename' ] ) ?>
	</div>
<?php
}

function _output_hidden_fields( $base_key, $item, $keys ) {
	foreach ( $keys as $key ) {
		$_val = esc_attr( $item[$key] );
?>
		<input type="hidden" <?php \st\field\esc_key_e( "{$base_key}_$key" ) ?> value="<?php echo $_val ?>" />
<?php
	}
}

function _save_item( $post_id, $key ) {
	update_post_meta( $post_id, $key . '_media',    $_POST[$key . '_media'] );
	update_post_meta( $post_id, $key . '_url',      $_POST[$key . '_url'] );
	update_post_meta( $post_id, $key . '_title',    $_POST[$key . '_title'] );
	update_post_meta( $post_id, $key . '_filename', $_POST[$key . '_filename'] );
}


// -----------------------------------------------------------------------------


/**
 * @deprecated Deprecated. Use 'enqueue_script' instead.
 */
function admin_enqueue_script( $url_to ) {
	wp_enqueue_style(  'st-single-media-picker', $url_to . '/single-media-picker.min.css' );
	wp_enqueue_script( 'st-single-media-picker', $url_to . '/single-media-picker.min.js' );
}
