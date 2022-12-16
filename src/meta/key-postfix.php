<?php
/**
 * Key with Postfix
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-01-17
 */

namespace wpinc\meta;

/**
 * Retrieve post meta values of keys with postfixes.
 *
 * @param int    $post_id   Post ID.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @return array Values.
 */
function get_post_meta_postfix( int $post_id, string $key, array $postfixes ): array {
	$vals = array();
	foreach ( $postfixes as $pf ) {
		$vals[ $pf ] = get_post_meta( $post_id, "{$key}_$pf", true );
	}
	return $vals;
}

/**
 * Stores post meta values of keys with postfixes.
 *
 * @param int      $post_id   Post ID.
 * @param string   $key       Meta key base.
 * @param array    $postfixes Meta key postfixes.
 * @param callable $filter    Filter function.
 */
function set_post_meta_postfix( int $post_id, string $key, array $postfixes, callable $filter = null ): void {
	foreach ( $postfixes as $pf ) {
		\wpinc\meta\set_post_meta( $post_id, "{$key}_$pf", $filter );
	}
}


// -----------------------------------------------------------------------------


/**
 * Adds multiple input.
 *
 * @param int    $post_id   Post ID.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @param string $label     Label.
 * @param string $type      Input type. Default 'text'.
 */
function add_input_postfix( int $post_id, string $key, array $postfixes, string $label, string $type = 'text' ): void {
	$vals = get_post_meta_postfix( $post_id, $key, $postfixes );
	output_input_row_postfix( $label, $key, $postfixes, $vals, $type );
}

/**
 * Adds multiple textarea.
 *
 * @param int    $post_id   Post ID.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @param string $label     Label.
 * @param int    $rows      Rows attribute. Default 2.
 */
function add_textarea_postfix( int $post_id, string $key, array $postfixes, string $label, int $rows = 2 ): void {
	$vals = get_post_meta_postfix( $post_id, $key, $postfixes );
	output_textarea_row_postfix( $label, $key, $postfixes, $vals, $rows );
}


// -----------------------------------------------------------------------------


/**
 * Outputs input rows.
 *
 * @param string $label     Label.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @param array  $vals      Current values.
 * @param string $type      Input type. Default 'text'.
 */
function output_input_row_postfix( string $label, string $key, array $postfixes, array $vals, string $type = 'text' ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	?>
	<div class="wpinc-meta-field-group">
	<?php
	foreach ( $postfixes as $pf ) {
		$val = $vals[ $pf ] ?? '';
		$ni  = "{$key}_$pf";
		?>
		<div class="wpinc-meta-field-single">
			<label>
				<span><?php echo esc_html( "$label [$pf]" ); ?></span>
				<input <?php name_id( $ni ); ?> type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $val ); ?>" size="64">
			</label>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}

/**
 * Outputs textarea rows.
 *
 * @param string $label     Label.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @param array  $vals      Current values.
 * @param int    $rows      Rows attribute. Default 2.
 */
function output_textarea_row_postfix( string $label, string $key, array $postfixes, array $vals, int $rows = 2 ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	?>
	<div class="wpinc-meta-field-group">
	<?php
	foreach ( $postfixes as $pf ) {
		$val = $vals[ $pf ] ?? '';
		$ni  = "{$key}_$pf";
		?>
		<div class="wpinc-meta-field-single">
			<label>
				<span><?php echo esc_html( "$label [$pf]" ); ?></span>
				<textarea <?php name_id( $ni ); ?> cols="64" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
			</label>
		</div>
		<?php
	}
	?>
	</div>
	<?php
}
