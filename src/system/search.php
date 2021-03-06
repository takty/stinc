<?php
namespace st;

/**
 *
 * Search Function for Custom Fields
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-05-16
 *
 */


class Search {

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new Search();
		return self::$_instance;
	}




	// -------------------------------------------------------------------------




	private $_is_slash_in_query_enabled = false;

	private $_meta_keys   = [];
	private $_post_types  = [];
	private $_slug_to_pts = [];
	private $_stop_words;

	private $_search_rewrite_rules_func = null;
	private $_request_func = null;
	private $_template_redirect_func = null;
	private $_pre_get_posts_func = null;

	private $_posts_search_filter_added = false;

	private function __construct() {}

	public function set_slash_in_query_enabled( $enabled ) {
		$this->_is_slash_in_query_enabled = $enabled;
		$this->ensure_request_filter_added();
	}

	public function set_blank_search_page_enabled( $enabled ) {
		if ( $enabled ) {
			$this->ensure_search_rewrite_rules_filter_added();
			$this->ensure_template_redirect_filter_added();
		} else {
			$this->ensure_search_rewrite_rules_filter_removed();
			$this->ensure_template_redirect_filter_removed();
		}
	}

	public function set_custom_search_page_enabled( $enabled ) {
		if ( is_admin() ) return;
		if ( $enabled ) {
			$this->ensure_request_filter_added();
			$this->ensure_template_redirect_filter_added();
		} else {
			$this->ensure_request_filter_removed();
			$this->ensure_template_redirect_filter_removed();
		}
	}

	public function add_post_type( $str_or_array ) {
		if ( is_admin() ) return;
		$this->ensure_pre_get_posts_filter();

		if ( ! is_array( $str_or_array ) ) $str_or_array = [ $str_or_array ];
		$this->_post_types = array_merge( $this->_post_types, $str_or_array );
	}

	public function add_post_type_specific_search_page( $slug, $post_type_s ) {
		$this->ensure_search_rewrite_rules_filter_added();
		$this->ensure_template_redirect_filter_added();

		if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];
		$this->_slug_to_pts[ trim( $slug, '/' ) ] = $post_type_s;
	}

	public function set_post_meta_search_enabled( $enabled ) {
		$this->ensure_posts_search_filter();
	}

	public function add_meta_key( $str_or_array ) {
		$this->ensure_posts_search_filter();

		if ( ! is_array( $str_or_array ) ) $str_or_array = [ $str_or_array ];
		$this->_meta_keys = array_merge( $this->_meta_keys, $str_or_array );
	}




	// Private Functions -------------------------------------------------------




	private function ensure_search_rewrite_rules_filter_added() {
		if ( $this->_search_rewrite_rules_func ) return;
		$this->_search_rewrite_rules_func = [ $this, '_cb_add_rewrite_rules' ];
		add_filter( 'search_rewrite_rules', $this->_search_rewrite_rules_func );
	}

	private function ensure_search_rewrite_rules_filter_removed() {
		if ( ! $this->_search_rewrite_rules_func ) return;
		remove_filter( 'search_rewrite_rules', $this->_search_rewrite_rules_func );
		$this->_search_rewrite_rules_func = null;
	}

	private function ensure_request_filter_added() {
		if ( $this->_request_func ) return;
		$this->_request_func = [ $this, '_cb_request' ];
		add_filter( 'request', $this->_request_func, 20, 1 );
	}

	private function ensure_request_filter_removed() {
		if ( ! $this->_request_func ) return;
		remove_filter( 'request', $this->_request_func, 20 );
		$this->_request_func = null;
	}

	private function ensure_template_redirect_filter_added() {
		if ( $this->_template_redirect_func ) return;
		$this->_template_redirect_func = [ $this, '_cb_template_redirect' ];
		add_filter( 'template_redirect', $this->_template_redirect_func );
	}

	private function ensure_template_redirect_filter_removed() {
		if ( ! $this->_template_redirect_func ) return;
		remove_filter( 'template_redirect', $this->_template_redirect_func );
		$this->_template_redirect_func = null;
	}

	private function ensure_pre_get_posts_filter() {
		if ( $this->_pre_get_posts_func ) return;
		$this->_pre_get_posts_func = [ $this, '_cb_pre_get_posts' ];
		add_action( 'pre_get_posts', $this->_pre_get_posts_func );
	}

	private function ensure_posts_search_filter() {
		if ( $this->_posts_search_filter_added ) return;
		add_filter( 'posts_search', [ $this, '_cb_posts_search' ], 10, 2 );
		add_filter( 'posts_join', [ $this, '_cb_posts_join' ], 10, 2 );
		add_filter( 'posts_groupby', [ $this, '_cb_posts_groupby' ], 10, 2 );
		$this->_posts_search_filter_added = true;
	}




	// Callback Functions ------------------------------------------------------




	public function _cb_add_rewrite_rules( $rewrite_rules ) {
		global $wp_rewrite;
		if ( ! $wp_rewrite->using_permalinks() ) return;

		$search_base = $wp_rewrite->search_base;
		$rewrite_rules[ "$search_base/?$" ] = 'index.php?s=';

		foreach ( $this->_slug_to_pts as $slug => $pts ) {
			$pts_str = implode( ',', $pts );
			$rewrite_rules[ "$slug/$search_base/(.+)/?$" ] = 'index.php?post_type=' . $pts_str . '&s=$matches[1]';
			$rewrite_rules[ "$slug/$search_base/?$" ]      = 'index.php?post_type=' . $pts_str . '&s=';
		}
		return $rewrite_rules;
	}

	public function _cb_template_redirect() {
		global $wp_rewrite;
		if ( ! $wp_rewrite->using_permalinks() ) return;

		$search_base = $wp_rewrite->search_base;
		if ( is_search() && ! is_admin() && isset( $_GET['s'] ) ) {
			$home_url = $this->home_url( "/$search_base/" );
			if ( ! empty( $_GET['post_type'] ) ) {
				$pts = explode( ',', $_GET['post_type'] );
				$slug = $this->get_matched_slug( $pts );
				if ( $slug !== false ) $home_url = $this->home_url( "/$slug/$search_base/" );
			}
			wp_redirect( $home_url . $this->urlencode( get_query_var( 's' ) ) );
			exit();
		}
	}

	private function get_matched_slug( $post_types ) {
		foreach ( $this->_slug_to_pts as $slug => $pts ) {
			foreach ( $post_types as $t ) {
				if ( in_array( $t, $pts, true ) ) {
					return $slug;
				}
			}
		}
		return false;
	}

	private function home_url( $slug ) {
		if ( class_exists( '\st\Multihome' ) ) {
			return \st\Multihome::get_instance()->home_url( $slug );
		}
		if ( class_exists( '\st\Multilang' ) ) {
			return \st\Multilang::get_instance()->home_url( $slug );
		}
		return home_url( $slug );
	}

	public function _cb_request( $query_vars ) {
		if ( isset( $query_vars['s'] ) && ! empty( $query_vars['pagename'] ) ) {
			$query_vars['pagename'] = '';
		}
		if ( isset( $query_vars['s'] ) && $this->_is_slash_in_query_enabled ) {
			$query_vars['s'] = str_replace( [ '%1f', '%1F' ], [ '%2f', '%2F' ], $query_vars['s'] );
		}
		return $query_vars;
	}

	public function _cb_pre_get_posts( $query ) {
		if ( $query->is_search ) {
			$val = $query->get( 'post_type' );
			if ( empty( $val ) && ! empty( $this->_post_types ) ) {
				$query->set( 'post_type', $this->_post_types );
			}
		}
	}

	public function _cb_posts_search( $search, $query ) {
		if ( ! $query->is_search() || ! $query->is_main_query() || empty( $search ) ) return $search;

		$q = $query->query_vars;
		global $wpdb;
		$search = '';

		$n = ! empty( $q['exact'] ) ? '' : '%';
		$searchand = '';
		$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );

		foreach ( $q['search_terms'] as $term ) {
			$exclude = $exclusion_prefix && ( $exclusion_prefix === substr( $term, 0, 1 ) );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			}
			$like = $n . $wpdb->esc_like( $term ) . $n;
			// Add post_meta
			$search .= $wpdb->prepare( "{$searchand}(($wpdb->posts.post_title $like_op %s) $andor_op ($wpdb->posts.post_content $like_op %s) $andor_op (stinc_search.meta_value $like_op %s))", $like, $like, $like );
			$searchand = ' AND ';
		}
		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) $search .= " AND ($wpdb->posts.post_password = '') ";
		}
		return $search;
	}

	public function _cb_posts_join( $join, $query ) {
		if ( ! $query->is_search() || ! $query->is_main_query() ) return $join;
		$sql_mks = '';
		if ( ! empty( $this->_meta_keys ) ) {
			$_mks = [];
			foreach ( $this->_meta_keys as $mk ) $_mks[] = "'" . esc_sql( $mk ) . "'";
			$sql_mks = implode( ', ', $_mks );
		}
		global $wpdb;
		$join .= " INNER JOIN ( SELECT post_id, meta_value FROM $wpdb->postmeta";
		if ( ! empty( $sql_mks ) ) {
			$join .= " WHERE meta_key IN ( $sql_mks )";
		}
		$join .= " ) AS stinc_search ON ($wpdb->posts.ID = stinc_search.post_id) ";
		return $join;
	}

	public function _cb_posts_groupby( $groupby, $query ) {
		global $wpdb;
		if ( $query->is_search() && $query->is_main_query() ) {
			$groupby = "{$wpdb->posts}.ID";
		}
		return $groupby;
	}




	// Private Functions -------------------------------------------------------




	private function urlencode( $str ) {
		if ( $this->_is_slash_in_query_enabled ) {
			$ret = rawurlencode( $str );
			return str_replace( [ '%2f', '%2F' ], [ '%1f', '%1F' ], $ret );
		} else {
			return rawurlencode( $str );
		}
	}

	private function urldecode( $str ) {
		if ( $this->_is_slash_in_query_enabled ) {
			$ret = str_replace( [ '%1f', '%1F' ], [ '%2f', '%2F' ], $str );
			return rawurldecode( $ret );
		} else {
			return rawurldecode( $str );
		}
	}

}
