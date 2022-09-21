<?php
/**
 * Picker
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-09-21
 */

namespace wpinc\meta;

/**
 * Adds media picker for post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Key.
 * @param string $label   Label.
 */
function add_media_picker( int $post_id, string $key, string $label ): void {
	$val = get_post_meta( $post_id, $key, true );
	$val = is_numeric( $val ) ? (int) $val : 0;
	output_media_picker_row( $label, $key, $val );
}

/**
 * Outputs media picker row.
 *
 * @param string $label    Label.
 * @param string $key      Key.
 * @param int    $media_id Media ID.
 */
function output_media_picker_row( string $label, string $key, int $media_id = 0 ): void {
	wp_enqueue_script( 'picker-media' );
	wp_enqueue_script( 'wpinc-meta-field' );
	wp_enqueue_style( 'wpinc-meta-field' );

	$src   = '';
	$title = '';
	if ( $media_id ) {
		$ais = wp_get_attachment_image_src( $media_id, 'small' );
		$src = ( false !== $ais ) ? $ais[0] : '';
		$p   = get_post( $media_id );
		if ( $p ) {
			$title = esc_html( $p->post_title );
		}
	}
	?>
	<div id="<?php echo esc_attr( "{$key}-body" ); ?>" class="wpinc-meta-media-picker">
		<label><?php echo esc_html( $label ); ?></label>
		<div>
			<div>
				<a href="javascript:void(0);" style="background-image:url('<?php echo esc_attr( $src ); ?>');" <?php name_id( "{$key}_src" ); ?> class="button wpinc-meta-media-picker-select"></a>
			</div>
			<div>
				<input type="text" disabled <?php name_id( "{$key}_title" ); ?> value="<?php echo esc_html( $title ); ?>">
				<a href="javascript:void(0);" class="wpinc-meta-media-picker-delete"><?php esc_html_e( 'Remove' ); ?></a>
			</div>
		</div>
		<input type="hidden" <?php name_id( $key ); ?> value="<?php echo esc_attr( $media_id ); ?>" />
		<script>window.addEventListener('load', function () { wpinc_meta_media_picker_initialize('<?php echo esc_attr( $key ); ?>'); });</script>
	</div>
	<?php
}


// -----------------------------------------------------------------------------


/**
 * Adds date picker for post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Key.
 * @param string $label   Label.
 */
function add_date_picker( int $post_id, string $key, string $label ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_date_picker_row( $label, $key, $val );
}

/**
 * Outputs date picker row.
 *
 * @param string $label Label.
 * @param string $key   Key.
 * @param mixed  $val   Value.
 */
function output_date_picker_row( string $label, string $key, $val ): void {
	wp_enqueue_script( 'flatpickr' );
	wp_enqueue_script( 'flatpickr.l10n.ja' );
	wp_enqueue_style( 'flatpickr' );
	wp_enqueue_style( 'wpinc-meta-field' );

	$val = $val ?? '';
	$loc = strtolower( str_replace( '_', '-', $args['locale'] ) );
	?>
	<div class="wpinc-meta-field-single">
		<label>
			<span><?php echo esc_html( $label ); ?></span>
			<span class="flatpickr input-group" id="<?php echo esc_attr( $key ); ?>_row">
				<input type="text" <?php name_id( $key ); ?> size="12" value="<?php echo esc_attr( $val ); ?>" data-input>
				<a class="button" title="clear" data-clear>X</a>
			</span>
		</label>
		<script>flatpickr('#<?php echo esc_attr( $key ); ?>_row', { locale: '<?php echo esc_html( $loc ); ?>', wrap: true });</script>
	</div>
	<?php
}
