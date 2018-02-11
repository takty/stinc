<?php
namespace st\single_media_picker;

/**
 *
 * Single Media Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-07-13
 *
 */


const NS = 'st_single_media_picker';


function get_item( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );

	$id       = get_post_meta( $post->ID, $key . '_id',       TRUE );
	$url      = get_post_meta( $post->ID, $key . '_url',      TRUE );
	$title    = get_post_meta( $post->ID, $key . '_title',    TRUE );
	$filename = get_post_meta( $post->ID, $key . '_filename', TRUE );
	return compact('id', 'url', 'title', 'filename');
}


// -----------------------------------------------------------------------------

function admin_enqueue_script( $url_to ) {
	wp_enqueue_style(  'st-single-media-picker', $url_to . '/single-media-picker.css' );
	wp_enqueue_script( 'st-single-media-picker', $url_to . '/single-media-picker.js' );
}

function add_meta_box( $key, $label, $screen, $context = 'side', $title_editable = true ) {
	\add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key ) {
			wp_nonce_field( $key, $key . '_nonce' );
			output_html( $key, $title_editable );
		},
		$screen, $context
	);
}

function output_html( $key, $title_editable = true ) {
	$item = get_item( $key );
?>
	<div class="<?php echo NS ?>">
		<div id="<?php echo $key ?>_item">
			<div class="<?php echo NS ?>_item_row">
				<div>
					<a href="javascript:void(0);" class="<?php echo NS ?>_delete widget-control-remove"><?php _e( 'Remove', 'default' ); ?></a>
				</div>
				<div>
					<div class="<?php echo NS ?>_row">
						<span class="<?php echo NS ?>_title_handle post-attributes-label"><?php echo __( 'Title', 'default' ) ?>:</span>
						<input <?php if (!$title_editable) echo 'readonly="readonly"' ?> type="text" id="<?php echo $key ?>_title" name="<?php echo $key ?>_title" value="<?php echo esc_attr( $item['title'] ) ?>" />
					</div>
					<div class="<?php echo NS ?>_row">
						<span><a href="<?php echo esc_url( $item['url'] ) ?>" target="_blank"><?php echo __( 'File name:', 'default' ) ?></a></span>
						<span class="<?php echo NS ?>_name"><?php echo esc_html( $item['filename'] ) ?></span>
						<a href="javascript:void(0);" class="button <?php echo NS ?>_select"><?php echo __( 'Select', 'default' ) ?></a>
					</div>
				</div>
			</div>
			<div class="<?php echo NS ?>_new_select_row">
				<a href="javascript:void(0);" class="<?php echo NS ?>_select button"><?php _e( 'Add Media', 'default' ); ?></a>
			</div>
			<?php output_hidden_fields( $key, $item, ['id', 'url', 'filename'] ) ?>
			<script>singleMediaPickerInit('<?php echo $key ?>', '<?php echo NS ?>');</script>
		</div>
	</div>
<?php
}

function output_hidden_fields( $base_key, $item, $keys ) {
	foreach ( $keys as $key ) {
		$val = $item[$key];
		$k = $base_key . '_' . $key;
?>
		<input type="hidden" id="<?php echo $k ?>" name="<?php echo $k ?>" value="<?php echo esc_attr( $val ) ?>" />
<?php
	}
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;
	save_post( $post_id, $key );
}

function save_post( $post_id, $key ) {
	update_post_meta( $post_id, $key . '_id',       $_POST[$key . '_id'] );
	update_post_meta( $post_id, $key . '_url',      $_POST[$key . '_url'] );
	update_post_meta( $post_id, $key . '_title',    $_POST[$key . '_title'] );
	update_post_meta( $post_id, $key . '_filename', $_POST[$key . '_filename'] );
}
