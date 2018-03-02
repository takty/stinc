<?php
namespace st\media_picker;

/**
 *
 * Media Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-02
 *
 */


const NS = 'st_media_picker';


function get_items( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );

	$items = \st\field\get_multiple_post_meta( $post->ID, $key, ['id', 'url', 'title', 'filename'] );
	return $items;
}


// -----------------------------------------------------------------------------

function enqueue_script_for_admin( $url_to ) {
	wp_enqueue_style(  'st-media-picker', $url_to . '/asset/media-picker.min.css' );
	wp_enqueue_script( 'st-media-picker', $url_to . '/asset/media-picker.min.js', [ 'jquery-ui-sortable' ] );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
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
?>
	<div class="<?php echo NS ?>">
		<input type="hidden" id="<?php echo $key ?>" name="<?php echo $key ?>" value="" />
		<table class="<?php echo NS ?>_table">
			<tbody id="<?php echo $key ?>_item_set">
<?php
output_row( ['id' => '', 'url' => '', 'title' => '', 'filename' => ''], NS . '_item_template', $title_editable );
foreach ( get_items( $key ) as $it ) {
	output_row( $it, NS . '_item', $title_editable );
}
?>
				<tr class="<?php echo NS ?>_ins_target"><td></td><td><a href="javascript:void(0);" class="<?php echo NS ?>_add button"><?php _e( 'Add Media', 'default' ); ?></a></td></tr>
			</tbody>
		</table>
		<script>mediaPickerInit('<?php echo $key ?>', '<?php echo NS ?>');</script>
	</div>
<?php
}

function output_row( $item, $class, $title_editable ) {
?>
	<tr class="<?php echo $class ?>">
		<td>
			<label class="widget-control-remove <?php echo NS ?>_delete_label"><input type="checkbox" class="<?php echo NS ?>_delete"></input><br /><?php _e( 'Remove', 'default' ); ?></label>
		</td>
		<td>
			<div>
				<span class="<?php echo NS ?>_cap <?php echo NS ?>_title_handle post-attributes-label"><?php echo __( 'Title', 'default' ) ?>:</span>
				<input <?php if (!$title_editable) echo 'readonly="readonly"' ?> type="text" class="<?php echo NS ?>_title" value="<?php echo esc_attr( $item['title'] ) ?>" />
			</div>
			<div>
				<span class="<?php echo NS ?>_cap"><a href="<?php echo esc_url( $item['url'] ) ?>" target="_blank"><?php echo __( 'File name:', 'default' ) ?></a></span>
				<span class="<?php echo NS ?>_name"><?php echo esc_html( $item['filename'] ) ?></span>
				<a href="javascript:void(0);" class="button <?php echo NS ?>_select"><?php echo __( 'Select', 'default' ) ?></a>
			</div>
			<input type="hidden" class="<?php echo NS ?>_id" value="<?php echo esc_attr( $item['id'] ) ?>" />
			<input type="hidden" class="<?php echo NS ?>_url" value="<?php echo esc_attr( $item['url'] ) ?>" />
			<input type="hidden" class="<?php echo NS ?>_filename" value="<?php echo esc_attr( $item['filename'] ) ?>" />
		</td>
	</tr>
<?php
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;
	save_post( $post_id, $key );
}

function save_post( $post_id, $key ) {
	$items = \st\field\get_multiple_post_meta_from_post( $key, ['id', 'url', 'title', 'filename', 'delete'] );
	$items = array_values( array_filter( $items, function ( $it ) {
		return ! $it['delete'] && ! empty( $it['url'] );
	} ) );
	\st\field\update_multiple_post_meta( $post_id, $key, $items, ['id', 'url', 'title', 'filename'] );
}
