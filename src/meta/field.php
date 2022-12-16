<?php
/**
 * Fields
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-10-27
 */

namespace wpinc\meta;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Initializes fields.
 *
 * @param array $args {
 *     (Optional) Array of arguments.
 *
 *     @type string 'url_to' URL to this script.
 * }
 */
function initialize( array $args = array() ): void {
	$url_to = untrailingslashit( $args['url_to'] ?? \wpinc\get_file_uri( __DIR__ ) );

	add_action(
		'admin_enqueue_scripts',
		function () use ( $url_to ) {
			wp_register_script( 'picker-link', \wpinc\abs_url( $url_to, './assets/lib/picker-link.min.js' ), array(), 1.0, false );
			wp_register_script( 'picker-media', \wpinc\abs_url( $url_to, './assets/lib/picker-media.min.js' ), array(), 1.0, false );
			wp_register_script( 'flatpickr', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.min.js' ), array(), 1.0, false );
			wp_register_script( 'flatpickr.l10n.ja', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.l10n.ja.min.js' ), array(), 1.0, false );
			wp_register_style( 'flatpickr', \wpinc\abs_url( $url_to, './assets/lib/flatpickr.min.css' ), array(), 1.0 );

			wp_register_script( 'wpinc-meta-field', \wpinc\abs_url( $url_to, './assets/js/field.min.js' ), array( 'picker-media' ), 1.0, false );
			wp_register_style( 'wpinc-meta-field', \wpinc\abs_url( $url_to, './assets/css/field.min.css' ), array(), 1.0 );
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Adds a separator.
 */
function add_separator() {
	output_separator();
}

/**
 * Adds an input.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param string $label   Label.
 * @param string $type    Input type. Default 'text'.
 */
function add_input( int $post_id, string $key, string $label, string $type = 'text' ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_input_row( $label, $key, $val, $type );
}

/**
 * Adds a textarea.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param string $label   Label.
 * @param int    $rows    Rows attribute. Default 2.
 */
function add_textarea( int $post_id, string $key, string $label, int $rows = 2 ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_textarea_row( $label, $key, $val, $rows );
}

/**
 * Adds a rich editor.
 *
 * @param int    $post_id  Post ID.
 * @param string $key      Meta key.
 * @param string $label    Label.
 * @param array  $settings Settings for wp_editor.
 */
function add_rich_editor( int $post_id, string $key, string $label, array $settings = array() ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_rich_editor_row( $label, $key, $val, $settings );
}

/**
 * Adds a checkbox.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param string $label   Label.
 */
function add_checkbox( int $post_id, string $key, string $label ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_checkbox_row( $label, $key, 'on' === $val );
}

/**
 * Adds a term select.
 *
 * @param int    $post_id  Post ID.
 * @param string $key      Meta key.
 * @param string $label    Label.
 * @param string $taxonomy Taxonomy slug.
 * @param string $field    Term field.
 */
function add_term_select( int $post_id, string $key, string $label, string $taxonomy, string $field = 'slug' ): void {
	$val = get_post_meta( $post_id, $key, true );
	output_term_select_row( $label, $key, $taxonomy, $val, $field );
}

/**
 * Adds a related term select.
 *
 * @param int    $post_id  Post ID.
 * @param string $key      Meta key.
 * @param string $label    Label.
 * @param string $taxonomy Taxonomy slug.
 * @param string $field    Term field.
 */
function add_related_term_select( int $post_id, string $key, string $label, string $taxonomy, string $field = 'slug' ): void {
	$val   = get_post_meta( $post_id, $key, true );
	$terms = get_the_terms( $post_id, $taxonomy );
	output_term_select_row( $label, $key, $terms, $val, $field );
}


// -----------------------------------------------------------------------------


/**
 * Outputs a separator.
 */
function output_separator(): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	?>
	<hr class="wpinc-meta-field-separator">
	<?php
}

/**
 * Outputs an input row.
 *
 * @param string $label Label.
 * @param string $key   Meta key.
 * @param mixed  $val   Current value.
 * @param string $type  Input type. Default 'text'.
 */
function output_input_row( string $label, string $key, $val, string $type = 'text' ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	$val = $val ?? '';
	?>
	<div class="wpinc-meta-field-single <?php echo esc_attr( $type ); ?>">
		<label>
			<span><?php echo esc_html( $label ); ?></span>
			<input <?php name_id( $key ); ?> type="<?php echo esc_attr( $type ); ?>" value="<?php echo esc_attr( $val ); ?>" size="64">
		</label>
	</div>
	<?php
}

/**
 * Outputs a textarea row.
 *
 * @param string $label Label.
 * @param string $key   Meta key.
 * @param mixed  $val   Current value.
 * @param int    $rows  Rows attribute. Default 2.
 */
function output_textarea_row( string $label, string $key, $val, int $rows = 2 ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	$val = $val ?? '';
	?>
	<div class="wpinc-meta-field-single textarea">
		<label>
			<span><?php echo esc_html( $label ); ?></span>
			<textarea <?php name_id( $key ); ?> cols="64" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_attr( $val ); ?></textarea>
		</label>
	</div>
	<?php
}

/**
 * Outputs a rich editor row.
 *
 * @param string $label    Label.
 * @param string $key      Meta key.
 * @param mixed  $val      Current value.
 * @param array  $settings Settings for wp_editor.
 */
function output_rich_editor_row( string $label, string $key, $val, array $settings = array() ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	$cls = '';
	if ( isset( $settings['media_buttons'] ) && false === $settings['media_buttons'] ) {
		$cls = ' no-media-button';
	}
	?>
	<div class="wpinc-meta-field-rich-editor<?php echo esc_attr( $cls ); ?>">
		<label><?php echo esc_html( $label ); ?></label>
		<?php wp_editor( $val, $key, $settings ); ?>
	</div>
	<?php
}

/**
 * Outputs a checkbox row.
 *
 * @param string $label   Label.
 * @param string $key     Meta key.
 * @param bool   $checked Current value. Default false.
 */
function output_checkbox_row( string $label, string $key, bool $checked = false ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	?>
	<div class="wpinc-meta-field-single checkbox">
		<label>
			<span class="checkbox"><input <?php name_id( $key ); ?> type="checkbox" <?php echo esc_attr( $checked ? 'checked' : '' ); ?>></span>
			<span><?php echo esc_html( $label ); ?></span>
		</label>
	</div>
	<?php
}

/**
 * Outputs a term select row.
 *
 * @param string            $label             Label.
 * @param string            $key               Meta key.
 * @param string|\WP_Term[] $taxonomy_or_terms Taxonomy slug or term objects.
 * @param string            $val               Current value.
 * @param string            $field             Term field.
 */
function output_term_select_row( string $label, string $key, $taxonomy_or_terms, $val, string $field = 'slug' ): void {
	wp_enqueue_style( 'wpinc-meta-field' );
	$terms = is_array( $taxonomy_or_terms ) ? $taxonomy_or_terms : get_terms( $taxonomy_or_terms );
	if ( ! is_array( $terms ) ) {
		$terms = array();
	}
	?>
	<div class="wpinc-meta-field-single select">
		<label>
			<span><?php echo esc_html( $label ); ?></span>
			<select name="<?php echo esc_attr( $key ); ?>">
	<?php
	foreach ( $terms as $t ) {
		$name = $t->name;
		$tf   = get_term_field( $field, $t, '', 'raw' );
		echo '<option value="' . esc_attr( $val ) . '"' . selected( $tf, $val, false ) . '>' . esc_html( $name ) . '</option>';
	}
	?>
			</select>
		</label>
	</div>
	<?php
}
