<?php
/**
 * Post Term Meta
 *
 * @package Wpinc Meta
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\meta\post_term_meta;

/**
 * Initialize post term meta.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string 'base_meta_key' Base meta key. Default '_pmk'.
 * }
 */
function initialize( array $args = array() ): void {
	$inst  = _get_instance();
	$args += array(
		'base_meta_key' => '_ptm',
	);

	$inst->pmk_base = $args['base_meta_key'];
	add_filter( 'wp_insert_post_data', '\wpinc\meta\post_term_meta\_cb_wp_insert_post_data', 99, 2 );
}

/**
 * Makes post term meta key.
 *
 * @param int    $term_id  Term ID.
 * @param string $meta_key Meta key.
 * @return string Post term meta key.
 */
function get_key( int $term_id, string $meta_key ): string {
	$inst = _get_instance();
	if ( '_' === $meta_key[0] ) {
		$meta_key = substr( $meta_key, 1 );
	}
	return "{$inst->pmk_base}_{$term_id}_{$meta_key}";
}


// -----------------------------------------------------------------------------


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
	return add_post_meta( $post_id, get_key( $term_id, $meta_key ), $meta_value, $unique );
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
	return delete_post_meta( $post_id, get_key( $term_id, $meta_key ), $meta_value );
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
	return get_post_meta( $post_id, get_key( $term_id, $meta_key ), $single );
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
	return update_post_meta( $post_id, get_key( $term_id, $meta_key ), $meta_value, $prev_value );
}


// -----------------------------------------------------------------------------


/**
 * Callback function for 'wp_insert_post_data' filter.
 *
 * @access private
 *
 * @param array $data    An array of slashed, sanitized, and processed post data.
 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
 * @return array Post data.
 */
function _cb_wp_insert_post_data( array $data, array $postarr ): array {
	$inst = _get_instance();

	$post_id  = $postarr['ID'];
	$pt_keys  = _get_related_keys( $post_id );
	$term_ids = _get_term_ids( $postarr );

	foreach ( $pt_keys as $key ) {
		$to_be_deleted = true;
		foreach ( $term_ids as $id ) {
			if ( 0 === strpos( $key, "{$inst->pmk_base}_{$id}_" ) ) {
				$to_be_deleted = false;
				break;
			}
		}
		if ( $to_be_deleted ) {
			delete_post_meta( $post_id, $key );
		}
	}
	return $data;
}

/**
 * Retrieves related meta keys.
 *
 * @access private
 *
 * @param int $post_id Post ID.
 * @return array Related meta keys.
 */
function _get_related_keys( int $post_id ): array {
	$inst = _get_instance();

	$pms = get_post_meta( $post_id );
	if ( empty( $pms ) ) {
		return array();
	}
	$ret = array();
	foreach ( $pms as $key => $val ) {
		if ( 0 === strpos( $key, "{$inst->pmk_base}_" ) ) {
			$ret[] = $key;
		}
	}
	return $ret;
}

/**
 * Retrieves term IDs.
 *
 * @access private
 *
 * @param array $postarr An array of sanitized (and slashed) but otherwise unmodified post data.
 * @return array Term IDs.
 */
function _get_term_ids( array $postarr ): array {
	if ( ! isset( $postarr['tax_input'] ) ) {
		return array();
	}
	$ret = array();
	foreach ( $postarr['tax_input'] as $tx => $ids ) {
		if ( count( $ids ) <= 1 ) {
			continue;
		}
		$ids = array_slice( $ids, 1 );
		foreach ( $ids as $id ) {
			$ret[] = $id;
		}
	}
	return $ret;
}


// -----------------------------------------------------------------------------


/**
 * Gets instance.
 *
 * @access private
 *
 * @return object Instance.
 */
function _get_instance(): object {
	static $values = null;
	if ( $values ) {
		return $values;
	}
	$values = new class() {
		/**
		 * The key prefix of post term meta.
		 *
		 * @var string
		 */
		public $pmk_base;
	};
	return $values;
}
