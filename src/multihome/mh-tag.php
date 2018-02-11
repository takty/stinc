<?php
namespace st;

/**
 *
 * Multi-Home Site with Single Site (Tag)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-03
 *
 */


class Multihome_Tag {

	const DEFAULT_TAXONOMY = 'post_home';

	private $_core;
	private $_taxonomy;

	private $_post_types = [];
	private $_taxonomies = [];
	private $_suppress_get_terms_filter = false;

	// public function __construct( $core, $home_to_names, $taxonomy_name, $taxonomy = self::DEFAULT_TAXONOMY ) {
	public function __construct( $core, $taxonomy = self::DEFAULT_TAXONOMY ) {
		$this->_core = $core;
		$this->_taxonomy = $taxonomy;
		// \st\taxonomy\register_without_post_type( $taxonomy, $taxonomy_name );
		// \st\taxonomy\set_terms( $taxonomy, $home_to_names );

		add_filter( 'get_next_post_join',      [ $this, '_cb_get_adjacent_post_join' ], 10, 5 );
		add_filter( 'get_previous_post_join',  [ $this, '_cb_get_adjacent_post_join' ], 10, 5 );
		add_filter( 'get_next_post_where',     [ $this, '_cb_get_adjacent_post_where' ], 10, 5 );
		add_filter( 'get_previous_post_where', [ $this, '_cb_get_adjacent_post_where' ], 10, 5 );
		add_action( 'posts_join',              [ $this, '_cb_posts_join' ],  10, 2 );
		add_action( 'posts_where',             [ $this, '_cb_posts_where' ], 10, 2 );
		add_filter( 'getarchives_join',        [ $this, '_cb_getarchives_join' ],  10, 2 );
		add_filter( 'getarchives_where',       [ $this, '_cb_getarchives_where' ], 10, 2 );
		add_filter( 'get_terms',               [ $this, '_cb_get_terms' ], 10, 4 );
	}

	public function add_tagged_post_type( $post_type_s ) {
		if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];
		foreach ( $post_type_s as $post_type ) {
			register_taxonomy_for_object_type( $this->_taxonomy, $post_type );
		}
		$this->_post_types += $post_type_s;
	}

	public function add_tagged_taxonomy( $taxonomy_s ) {
		if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
		$this->_taxonomies += $taxonomy_s;
	}

	public function get_taxonomy() {
		return $this->_taxonomy;
	}

	public function get_tax_query() {
		$home = $this->_core->get_site_home();
		return [ 'taxonomy' => $this->_taxonomy, 'field' => 'slug', 'terms' => $home ];
	}


	// Private Functions -------------------------------------------------------

	private function _get_tag_id() {
		$sh = $this->_core->get_site_home();
		$sh_term = get_term_by( 'slug', $sh, $this->_taxonomy );
		return ( $sh_term === false ) ? 0 : $sh_term->term_id;
	}

	public function _cb_get_adjacent_post_join( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) {  // Private
		if ( ! $in_same_term || ! in_array( $post->post_type, $this->_post_types ) ) return $join;

		global $wpdb;
		$join .= " LEFT JOIN $wpdb->term_relationships AS tr_mh ON (p.ID = tr_mh.object_id)";
		$join .= " LEFT JOIN $wpdb->term_taxonomy AS tt_mh ON (tr_mh.term_taxonomy_id = tt_mh.term_taxonomy_id)";
		return $join;
	}

	public function _cb_get_adjacent_post_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {  // Private
		if ( ! $in_same_term || ! in_array( $post->post_type, $this->_post_types ) ) return $where;

		global $wpdb;
		$where .= $wpdb->prepare( " AND tt_mh.taxonomy = %s", $this->_taxonomy );
		$where .= $wpdb->prepare( " AND tt_mh.term_id = %d", $this->_get_tag_id() );
		return $where;
	}

	public function _cb_posts_join( $join, $query ) {  // Private
		if ( is_admin() || ! $query->is_main_query() ) return $join;

		global $wpdb;
		if ( in_array( $query->query_vars['post_type'], $this->_post_types ) ) {
			$join .= " LEFT JOIN $wpdb->term_relationships AS tr_mh ON ($wpdb->posts.ID = tr_mh.object_id)";
			$join .= " LEFT JOIN $wpdb->term_taxonomy AS tt_mh ON (tr_mh.term_taxonomy_id = tt_mh.term_taxonomy_id)";
		} else if ( is_search() ) {
			$join .= " LEFT JOIN $wpdb->term_relationships AS tr_mh ON ($wpdb->posts.ID = tr_mh.object_id)";
			$join .= $wpdb->prepare( " LEFT JOIN $wpdb->term_taxonomy AS tt_mh ON (tr_mh.term_taxonomy_id = tt_mh.term_taxonomy_id AND tt_mh.taxonomy = %s)", $this->_taxonomy );
		}
		return $join;
	}

	public function _cb_posts_where( $where, $query ) {  // Private
		if ( is_admin() || ! $query->is_main_query() ) return $where;

		global $wpdb;
		if ( in_array( $query->query_vars['post_type'], $this->_post_types ) ) {
			$where .= $wpdb->prepare( " AND tt_mh.taxonomy = %s", $this->_taxonomy );
			$where .= $wpdb->prepare( " AND tt_mh.term_id = %d", $this->_get_tag_id() );
		} else if ( is_search() ) {
			$ps = "'" . implode( "', '", $this->_post_types ) . "'";
			$where .= $wpdb->prepare( " AND ($wpdb->posts.post_type NOT IN ($ps) OR tt_mh.term_id = %d)", $this->_get_tag_id() );
		}
		return $where;
	}

	public function _cb_getarchives_join( $join, $r ) {  // Private
		if ( is_admin() || ! is_main_query() ) return $join;
		if ( ! in_array( $r['post_type'], $this->_post_types ) ) return $join;

		global $wpdb;
		$join .= " LEFT JOIN $wpdb->term_relationships AS tr_mh ON ($wpdb->posts.ID = tr_mh.object_id)";
		$join .= " LEFT JOIN $wpdb->term_taxonomy AS tt_mh ON (tr_mh.term_taxonomy_id = tt_mh.term_taxonomy_id)";
		return $join;
	}

	public function _cb_getarchives_where( $where, $r ) {  // Private
		if ( is_admin() || ! is_main_query() ) return $where;
		if ( ! in_array( $r['post_type'], $this->_post_types ) ) return $where;

		global $wpdb;
		$where .= $wpdb->prepare( " AND tt_mh.taxonomy = %s", $this->_taxonomy );
		$where .= $wpdb->prepare( " AND tt_mh.term_id = %d", $this->_get_tag_id() );
		return $where;
	}

	public function _cb_get_terms( $terms, $taxonomies, $args, $term_query ) {  // Private
		if ( $this->_suppress_get_terms_filter || is_admin() ) return $terms;

		if ( $args['fields'] === 'all' ) {
			$this->_suppress_get_terms_filter = true;
			$tid = $this->_get_tag_id();
			$ret = [];
			foreach ( $terms as $term ) {
				if ( in_array( $term->taxonomy, $this->_taxonomies ) ) {
					$ps = get_posts( [
						'post_type' => $this->_post_types,
						'tax_query' => [
							'relation' => 'AND',
							[ 'taxonomy' => $term->taxonomy, 'terms' => $term->term_id ],
							[ 'taxonomy' => $this->_taxonomy, 'terms' => $tid ],
						],
					] );
					if ( empty( $ps ) ) continue;
					$term = new \WP_Term( $term );
					$term->count = count( $ps );
				}
				$ret[] = $term;
			}
			$this->_suppress_get_terms_filter = false;
			return $ret;
		}
		return $terms;
	}

}
