<?php
/**
 * Fields
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-25
 */

namespace st\meta;

require_once __DIR__ . '/meta/field.php';
require_once __DIR__ . '/meta/key-postfix.php';
require_once __DIR__ . '/meta/multiple.php';
require_once __DIR__ . '/meta/picker.php';
require_once __DIR__ . '/meta/post-term-meta.php';
require_once __DIR__ . '/meta/utility.php';

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
	\wpinc\meta\initialize( $args );
}

/**
 * Adds a separator.
 */
function add_separator() {
	\wpinc\meta\add_separator();
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
	\wpinc\meta\add_input( $post_id, $key, $label, $type );
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
	\wpinc\meta\add_textarea( $post_id, $key, $label, $rows );
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
	\wpinc\meta\add_rich_editor( $post_id, $key, $label, $settings );
}

/**
 * Adds a checkbox.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param string $label   Label.
 */
function add_checkbox( int $post_id, string $key, string $label ): void {
	\wpinc\meta\add_checkbox( $post_id, $key, $label );
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
	\wpinc\meta\add_term_select( $post_id, $key, $label, $taxonomy, $field );
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
	\wpinc\meta\add_related_term_select( $post_id, $key, $label, $taxonomy, $field );
}


/**
 * Outputs a separator.
 */
function output_separator(): void {
	\wpinc\meta\output_separator();
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
	\wpinc\meta\output_input_row( $label, $key, $val, $type );
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
	\wpinc\meta\output_textarea_row( $label, $key, $val, $rows );
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
	\wpinc\meta\output_rich_editor_row( $label, $key, $val, $settings );
}

/**
 * Outputs a checkbox row.
 *
 * @param string $label   Label.
 * @param string $key     Meta key.
 * @param bool   $checked Current value. Default false.
 */
function output_checkbox_row( string $label, string $key, bool $checked = false ): void {
	\wpinc\meta\output_checkbox_row( $label, $key, $checked );
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
	\wpinc\meta\output_term_select_row( $label, $key, $taxonomy_or_terms, $val, $field );
}


// -----------------------------------------------------------------------------


/**
 * Retrieve post meta values of keys with postfixes.
 *
 * @param int    $post_id   Post ID.
 * @param string $key       Meta key base.
 * @param array  $postfixes Meta key postfixes.
 * @return array Values.
 */
function get_post_meta_postfix( int $post_id, string $key, array $postfixes ): array {
	return \wpinc\meta\get_post_meta_postfix( $post_id, $key, $postfixes );
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
	\wpinc\meta\set_post_meta_postfix( $post_id, $key, $postfixes, $filter );
}

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
	\wpinc\meta\add_input_postfix( $post_id, $key, $postfixes, $label, $type );
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
	\wpinc\meta\add_textarea_postfix( $post_id, $key, $postfixes, $label, $rows );
}

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
	\wpinc\meta\output_input_row_postfix( $label, $key, $postfixes, $vals, $type );
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
	\wpinc\meta\output_textarea_row_postfix( $label, $key, $postfixes, $vals, $rows );
}


// -----------------------------------------------------------------------------


/**
 * Retrieve multiple post meta from environ variable $_POST.
 *
 * @param string $base_key Base key of variable names.
 * @param array  $keys     Keys of variable names.
 * @return array The meta values.
 */
function get_multiple_post_meta_from_env( string $base_key, array $keys ): array {
	return \wpinc\meta\get_multiple_post_meta_from_env( $base_key, $keys );
}

/**
 * Retrieve multiple post meta values.
 *
 * @param int         $post_id     Post ID.
 * @param string      $base_key    Base key of variable names.
 * @param array       $keys        Keys of variable names.
 * @param string|null $special_key (Optional) Special key.
 * @return array The meta values.
 */
function get_multiple_post_meta( int $post_id, string $base_key, array $keys, ?string $special_key = null ): array {
	return \wpinc\meta\get_multiple_post_meta( $post_id, $base_key, $keys, $special_key );
}

/**
 * Stores multiple post meta values.
 *
 * @param int         $post_id     Post ID.
 * @param string      $base_key    Base key of variable names.
 * @param array       $vals        Values.
 * @param array       $keys        Keys of variable names.
 * @param string|null $special_key (Optional) Special key.
 */
function set_multiple_post_meta( int $post_id, string $base_key, array $vals, ?array $keys = null, ?string $special_key = null ): void {
	\wpinc\meta\set_multiple_post_meta( $post_id, $base_key, $vals, $keys, $special_key );
}


// -----------------------------------------------------------------------------


/**
 * Adds media picker for post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Key.
 * @param string $label   Label.
 */
function add_media_picker( int $post_id, string $key, string $label ): void {
	\wpinc\meta\add_media_picker( $post_id, $key, $label );
}

/**
 * Outputs media picker row.
 *
 * @param string $label    Label.
 * @param string $key      Key.
 * @param int    $media_id Media ID.
 */
function output_media_picker_row( string $label, string $key, int $media_id = 0 ): void {
	\wpinc\meta\output_media_picker_row( $label, $key, $media_id );
}

/**
 * Adds date picker for post meta.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Key.
 * @param string $label   Label.
 */
function add_date_picker( int $post_id, string $key, string $label ): void {
	\wpinc\meta\add_date_picker( $post_id, $key, $label );
}

/**
 * Outputs date picker row.
 *
 * @param string $label Label.
 * @param string $key   Key.
 * @param mixed  $val   Value.
 */
function output_date_picker_row( string $label, string $key, $val ): void {
	\wpinc\meta\output_date_picker_row( $label, $key, $val );
}


// -----------------------------------------------------------------------------


/**
 * Initialize post term meta.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string 'base_meta_key' Base meta key. Default '_pmk'.
 * }
 */
function initialize_post_term_meta( array $args = array() ): void {
	\wpinc\meta\post_term_meta\initialize_post_term_meta( $args );
}

/**
 * Makes post term meta key.
 *
 * @param int    $term_id  Term ID.
 * @param string $meta_key Meta key.
 * @return string Post term meta key.
 */
function get_post_term_meta_key( int $term_id, string $meta_key ): string {
	return \wpinc\meta\post_term_meta\get_post_term_meta_key( $term_id, $meta_key );
}

/**
 * Adds a post term meta field.
 *
 * @param int    $post_id    Post ID.
 * @param int    $term_id    Term ID.
 * @param string $meta_key   Meta key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     (Optional) Whether the same key should not be added.
 * @return int|false Meta ID on success, false on failure.
 */
function add_post_term_meta( int $post_id, int $term_id, string $meta_key, $meta_value, bool $unique = false ) {
	return \wpinc\meta\post_term_meta\add_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $unique );
}

/**
 * Deletes a post term meta field.
 *
 * @param int    $post_id    Post ID.
 * @param int    $term_id    Term ID.
 * @param string $meta_key   Meta key.
 * @param string $meta_value (Optional) Metadata value. Must be serializable if non-scalar.
 * @return bool True on success, false on failure.
 */
function delete_post_term_meta( int $post_id, int $term_id, string $meta_key, $meta_value = '' ): bool {
	return \wpinc\meta\post_term_meta\delete_post_term_meta( $post_id, $term_id, $meta_key, $meta_value );
}

/**
 * Retrieves a post term meta field.
 *
 * @param int    $post_id  Post ID.
 * @param int    $term_id  Term ID.
 * @param string $meta_key Meta key.
 * @param bool   $single   (Optional) Whether to return a single value.
 * @return mixed An array of values if $single is false. The value of the meta field if $single is true. False for an invalid $post_id (non-numeric, zero, or negative value). An empty string if a valid but non-existing post ID is passed.
 */
function get_post_term_meta( int $post_id, int $term_id, string $meta_key, bool $single = false ) {
	return \wpinc\meta\post_term_meta\get_post_term_meta( $post_id, $term_id, $meta_key, $single );
}

/**
 * Updates a post term meta field.
 *
 * @param int    $post_id    Post ID.
 * @param int    $term_id    Term ID.
 * @param string $meta_key   Meta key.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param mixed  $prev_value (Optional) Previous value to check before updating.
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure or if the value passed to the function is the same as the one that is already in the database.
 */
function update_post_term_meta( int $post_id, int $term_id, string $meta_key, $meta_value, $prev_value = '' ) {
	return \wpinc\meta\post_term_meta\update_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $prev_value );
}


// -----------------------------------------------------------------------------


/**
 * Retrieves a post meta field as date.
 *
 * @param int         $post_id Post ID.
 * @param string      $key     The meta key to retrieve.
 * @param string|null $format  Date format.
 * @return string Date string.
 */
function get_post_meta_date( int $post_id, string $key, string $format = null ) {
	return \wpinc\meta\get_post_meta_date( $post_id, $key, $format );
}

/**
 * Retrieves a post meta field as multiple lines.
 *
 * @param int    $post_id Post ID.
 * @param string $key     The meta key to retrieve.
 * @return string[] Lines.
 */
function get_post_meta_lines( int $post_id, string $key ): array {
	return \wpinc\meta\get_post_meta_lines( $post_id, $key );
}

/**
 * Stores a post meta field.
 *
 * @param int           $post_id Post ID.
 * @param string        $key     Metadata key.
 * @param callable|null $filter  Filter.
 * @param mixed|null    $default Default value.
 */
function set_post_meta( int $post_id, string $key, ?callable $filter = null, $default = null ): void {
	\wpinc\meta\set_post_meta( $post_id, $key, $filter, $default );
}

/**
 * Stores a post meta field after applying filters.
 *
 * @param int         $post_id     Post ID.
 * @param string      $key         Metadata key.
 * @param string|null $filter_name Filter name.
 * @param mixed|null  $default     Default value.
 */
function set_post_meta_with_wp_filter( int $post_id, string $key, ?string $filter_name = null, $default = null ): void {
	\wpinc\meta\set_post_meta_with_wp_filter( $post_id, $key, $filter_name, $default );
}

/**
 * Trims string.
 *
 * @param string $str String.
 * @return string Trimmed string.
 */
function mb_trim( string $str ): string {
	return \wpinc\meta\mb_trim( $str );
}

/**
 * Outputs name and id attributes.
 *
 * @param string $key Key.
 */
function name_id( string $key ): void {
	\wpinc\meta\name_id( $key );
}

/**
 * Normalizes date string.
 *
 * @param string $str String representing date.
 * @return string Normalized string.
 */
function normalize_date( string $str ): string {
	return \wpinc\meta\normalize_date( $str );
}
