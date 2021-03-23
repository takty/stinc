<?php
/**
 * Post Term Meta
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st;

class PostTermMeta {

	const PMK_BASE_DEFAULT = '_ptm';

	static private $_instance = null;

	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new PostTermMeta();
		}
		return self::$_instance;
	}


	// -------------------------------------------------------------------------

	private $_pmk_base = self::PMK_BASE_DEFAULT;

	private function __construct() {}

	public function set_base_meta_key( $key ) {
		$this->_pmk_base = $key;
	}

	public function get_key( $term_id, $meta_key ) {
		if ( '_' === $meta_key[0] ) {
			$meta_key = substr( $meta_key, 1 );
		}
		return "{$this->_pmk_base}_{$term_id}_{$meta_key}";
	}

	public function add_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $unique = false ) {
		return add_post_meta( $post_id, $this->get_key( $term_id, $meta_key ), $meta_value, $unique );
	}

	public function delete_post_term_meta( $post_id, $term_id, $meta_key, $meta_value = '' ) {
		return delete_post_meta( $post_id, $this->get_key( $term_id, $meta_key ), $meta_value );
	}

	public function get_post_term_meta( $post_id, $term_id, $meta_key, $single = false ) {
		return get_post_meta( $post_id, $this->get_key( $term_id, $meta_key ), $single );
	}

	public function update_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_post_meta( $post_id, $this->get_key( $term_id, $meta_key ), $meta_value, $prev_value );
	}

	public function _cb_wp_insert_post_data( $data, $postarr ) {
		$post_id  = $postarr['ID'];
		$pt_keys  = $this->_get_related_keys( $post_id );
		$term_ids = $this->_get_term_ids( $postarr );

		foreach ( $pt_keys as $key ) {
			$to_be_deleted = true;
			foreach ( $term_ids as $id ) {
				if ( strpos( $key, "{$this->_pmk_base}_{$id}_" ) === 0 ) {
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

	private function _get_related_keys( $post_id ) {
		$pms = get_post_meta( $post_id );
		if ( empty( $pms ) ) {
			return array();
		}
		$ret = array();
		foreach ( $pms as $key => $val ) {
			if ( strpos( $key, "{$this->_pmk_base}_" ) === 0 ) {
				$ret[] = $key;
			}
		}
		return $ret;
	}

	private function _get_term_ids( $postarr ) {
		if ( ! isset( $postarr['tax_input'] ) ) {
			return array();
		}
		$ret = array();
		foreach ( $postarr['tax_input'] as $tax => $ids ) {
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

}

add_filter( 'wp_insert_post_data', [ \st\PostTermMeta::get_instance(), '_cb_wp_insert_post_data' ], 99, 2 );

function add_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $unique = false ) {
	return \st\PostTermMeta::get_instance()->add_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $unique );
}

function delete_post_term_meta( $post_id, $term_id, $meta_key, $meta_value = '' ) {
	return \st\PostTermMeta::get_instance()->delete_post_term_meta( $post_id, $term_id, $meta_key, $meta_value );
}

function get_post_term_meta( $post_id, $term_id, $meta_key, $single = false ) {
	return \st\PostTermMeta::get_instance()->get_post_term_meta( $post_id, $term_id, $meta_key, $single );
}

function update_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $prev_value = '' ) {
	return \st\PostTermMeta::get_instance()->update_post_term_meta( $post_id, $term_id, $meta_key, $meta_value, $prev_value );
}
