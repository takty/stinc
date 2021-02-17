<?php
namespace st;
/**
 *
 * Multi-Language Site with Single Site (Tag)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-02-17
 *
 */


require_once __DIR__ . '/../system/taxonomy.php';


class Multilang_Tag {

	const DEFAULT_TAXONOMY = 'post_lang';

	private $_core;
	private $_taxonomy;

	private $_post_types = [];
	private $_taxonomies = [];
	private $_suppress_get_terms_filter = false;

	public function __construct( $core, $lang_to_names, $taxonomy_name, $taxonomy = self::DEFAULT_TAXONOMY ) {
		$this->_core = $core;
		$this->_taxonomy = $taxonomy;
		register_taxonomy( $taxonomy, null, [
			'hierarchical'      => true,
			'label'             => $taxonomy_name,
			'public'            => true,
			'show_ui'           => true,
			'rewrite'           => false,
			'sort'              => true,
			'show_admin_column' => true
		] );
		\st\taxonomy\set_terms( $taxonomy, $lang_to_names );

		add_filter( 'get_next_post_where',     [ $this, '_cb_get_adjacent_post_where' ], 10, 5 );
		add_filter( 'get_previous_post_where', [ $this, '_cb_get_adjacent_post_where' ], 10, 5 );
		add_action( 'posts_join',              [ $this, '_cb_posts_join' ],  10, 2 );
		add_action( 'posts_where',             [ $this, '_cb_posts_where' ], 10, 2 );
		add_action( 'posts_groupby',           [ $this, '_cb_posts_groupby' ], 10, 2 );
		add_filter( 'getarchives_join',        [ $this, '_cb_getarchives_join' ],  10, 2 );
		add_filter( 'getarchives_where',       [ $this, '_cb_getarchives_where' ], 10, 2 );
		add_filter( 'get_terms',               [ $this, '_cb_get_terms' ], 10, 4 );
	}

	public function add_tagged_post_type( $post_type_s ) {
		if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];
		foreach ( $post_type_s as $post_type ) {
			register_taxonomy_for_object_type( $this->_taxonomy, $post_type );
		}
		\st\taxonomy\set_taxonomy_default_term( $post_type_s, $this->_taxonomy, $this->_core->get_default_site_lang() );
		$this->_post_types = array_merge( $this->_post_types, $post_type_s );
	}

	public function add_tagged_taxonomy( $taxonomy_s ) {
		if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
		$this->_taxonomies = array_merge( $this->_taxonomies, $taxonomy_s );
	}

	public function get_taxonomy() {
		return $this->_taxonomy;
	}

	public function get_tax_query() {
		$lang = $this->_core->get_site_lang();
		return [ 'taxonomy' => $this->_taxonomy, 'field' => 'slug', 'terms' => $lang ];
	}

	public function has_tag( $post_type ) {
		return in_array( $post_type, $this->_post_types, true );
	}


	// Private Functions -------------------------------------------------------

	private function _get_tag_id() {
		$sl = $this->_core->get_site_lang();
		$sl_term = get_term_by( 'slug', $sl, $this->_taxonomy );
		return $sl_term->term_id;
	}

	private function _get_tag_tt_id() {
		$sl = $this->_core->get_site_lang();
		$sl_term = get_term_by( 'slug', $sl, $this->_taxonomy );
		return $sl_term->term_taxonomy_id;
	}

	public function _cb_get_adjacent_post_where( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) {  // Private
		if ( ! $in_same_term || ! in_array( $post->post_type, $this->_post_types, true ) ) return $where;

		global $wpdb;
		$where .= $wpdb->prepare( " AND tt.taxonomy = %s", $taxonomy );
		$where .= $wpdb->prepare( " AND tt.term_id = %d", $this->_get_tag_id() );
		return $where;
	}

	public function _cb_posts_join( $join, $query ) {  // Private
		if ( is_admin() || ! $query->is_main_query() || empty( $this->_post_types ) ) return $join;

		global $wpdb;
		if ( in_array( $query->query_vars['post_type'], $this->_post_types, true ) ) {
			$join .= " LEFT JOIN $wpdb->term_relationships AS tr ON ($wpdb->posts.ID = tr.object_id)";
			$join .= " LEFT JOIN $wpdb->term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)";
		} else if ( is_search() ) {
			$terms = get_terms( $this->_taxonomy );
			if ( is_array( $terms ) && 0 < count( $terms ) ) {
				$tt_ids = array_map( function ( $t ) { return $t->term_taxonomy_id; }, $terms );
				$in = 'IN (' . implode( ', ', $tt_ids ) . ')';
				$join .= " LEFT JOIN $wpdb->term_relationships AS tr ON ($wpdb->posts.ID = tr.object_id) AND (tr.term_taxonomy_id $in)";
			}
		}
		return $join;
	}

	public function _cb_posts_where( $where, $query ) {  // Private
		if ( is_admin() || ! $query->is_main_query() || empty( $this->_post_types ) ) return $where;

		global $wpdb;
		if ( in_array( $query->query_vars['post_type'], $this->_post_types, true ) ) {
			$where .= $wpdb->prepare( " AND tt.taxonomy = %s", $this->_taxonomy );
			$where .= $wpdb->prepare( " AND tt.term_id = %d", $this->_get_tag_id() );
		} else if ( is_search() ) {
			$ps = "'" . implode( "', '", $this->_post_types ) . "'";
			$terms = get_terms( $this->_taxonomy );
			if ( is_array( $terms ) && 0 < count( $terms ) ) {
				$where .= $wpdb->prepare( " AND ($wpdb->posts.post_type NOT IN ($ps) OR tr.term_taxonomy_id = %d)", $this->_get_tag_tt_id() );
			} else {
				$where .= " AND ($wpdb->posts.post_type NOT IN ($ps))";
			}
		}
		return $where;
	}

	public function _cb_posts_groupby( $groupby, $query ) {  // Private
		if ( is_admin() || ! $query->is_main_query() || empty( $this->_post_types ) ) return $groupby;
		if ( ! is_search() ) return $groupby;

		global $wpdb;
		$g = "{$wpdb->posts}.ID";

		if ( preg_match( "/$g/", $groupby ) ) return $groupby;
		if ( empty( trim( $groupby ) ) ) return $g;
		return "$groupby, $g";
	}

	public function _cb_getarchives_join( $join, $r ) {  // Private
		if ( is_admin() || ! is_main_query() || empty( $this->_post_types ) ) return $join;
		if ( ! in_array( $r['post_type'], $this->_post_types, true ) ) return $join;

		global $wpdb;
		$join .= " LEFT JOIN $wpdb->term_relationships AS tr ON ($wpdb->posts.ID = tr.object_id)";
		$join .= " LEFT JOIN $wpdb->term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)";
		return $join;
	}

	public function _cb_getarchives_where( $where, $r ) {  // Private
		if ( is_admin() || ! is_main_query() || empty( $this->_post_types ) ) return $where;
		if ( ! in_array( $r['post_type'], $this->_post_types, true ) ) return $where;

		global $wpdb;
		$where .= $wpdb->prepare( " AND tt.taxonomy = %s", $this->_taxonomy );
		$where .= $wpdb->prepare( " AND tt.term_id = %d", $this->_get_tag_id() );
		return $where;
	}

	public function _cb_get_terms( $terms, $taxonomies, $args, $term_query ) {  // Private
		if ( $this->_suppress_get_terms_filter || is_admin() ) return $terms;

		if ( $args['fields'] === 'all' ) {
			$this->_suppress_get_terms_filter = true;
			$tid = $this->_get_tag_id();
			$ret = [];
			foreach ( $terms as $term ) {
				if ( in_array( $term->taxonomy, $this->_taxonomies, true ) ) {
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
