<?php
namespace st;

/**
 *
 * Search Function for Custom Fields
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-29
 *
 */


class Search {

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new Search();
		return self::$_instance;
	}


	// -------------------------------------------------------------------------


	private $_meta_keys  = [];
	private $_post_types = [];
	private $_stop_words;

	private $_search_rewrite_rules_func = null;
	private $_request_func = null;
	private $_posts_clauses_filter_added = false;
	private $_template_redirect_func = null;
	private $_pre_get_posts_func = null;

	private function __construct() {}

	public function set_blank_search_page_enabled( $enabled ) {
		if ( is_admin() ) return;
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

	public function add_meta_key( $str_or_array ) {
		if ( is_admin() ) return;
		$this->ensure_posts_clauses_filter();

		if ( ! is_array( $str_or_array ) ) $str_or_array = [ $str_or_array ];
		$this->_meta_keys += $str_or_array;
	}

	public function add_post_type( $str_or_array ) {
		if ( is_admin() ) return;
		$this->ensure_pre_get_posts_filter();

		if ( ! is_array( $str_or_array ) ) $str_or_array = [ $str_or_array ];
		$this->_post_types[] = $str_or_array;
	}


	// Private Functions -------------------------------------------------------


	private function ensure_search_rewrite_rules_filter_added() {
		if ( $this->_search_rewrite_rules_func ) return;
		$this->_search_rewrite_rules_func = [ $this, '_cb_request' ];
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

	private function ensure_posts_clauses_filter() {
		if ( $this->_posts_clauses_filter_added ) return;
		$this->_stop_words = $this->_get_search_stopwords();
		add_filter( 'posts_clauses', [ $this, '_cb_posts_clauses' ], 20, 1 );
		$this->_posts_clauses_filter_added = true;
	}

	private function ensure_pre_get_posts_filter() {
		if ( $this->_pre_get_posts_func ) return;
		$this->_pre_get_posts_func = [ $this, '_cb_pre_get_posts' ];
		add_filter( 'pre_get_posts', $this->_pre_get_posts_func );
	}


	// Callback Functions ------------------------------------------------------


	public function _cb_add_rewrite_rules( $rewrite_rules ) {  // for set_blank_search_page_enabled
		global $wp_rewrite;
		if ( ! $wp_rewrite->using_permalinks() ) return;

		$search_base = $wp_rewrite->search_base;
		$rewrite_rules[ "$search_base/?$" ] = 'index.php?s=';
		return $rewrite_rules;
	}

	public function _cb_template_redirect() {  // for set_blank_search_page_enabled & set_custom_search_page_enabled
		global $wp_rewrite;
		if ( ! $wp_rewrite->using_permalinks() ) return;

		$search_base = $wp_rewrite->search_base;
		if ( is_search() && ! is_admin() && ! empty( $_GET['s'] ) ) {
			if ( class_exists( '\st\Multihome' ) ) {
				$home_url = \st\Multihome::get_instance()->home_url( "/$search_base/" );
			} else if ( class_exists( '\st\Multilang' ) ) {
				$home_url = \st\Multilang::get_instance()->home_url( "/$search_base/" );
			} else {
				$home_url = home_url( "/$search_base/" );
			}
			wp_redirect( $home_url . rawurlencode( get_query_var( 's' ) ) );
			exit();
		}
	}

	public function _cb_request( $query_vars ) {  // for set_custom_search_page_enabled
		if ( isset( $query_vars['s'] ) && ! empty( $query_vars['pagename'] ) ) {
			$query_vars['pagename'] = '';
		}
		return $query_vars;
	}

	public function _cb_posts_clauses( $pieces ) {  // for add_meta_key
		if ( ! is_search() || is_admin() || empty( $this->_meta_keys ) ) return $pieces;

		$q_s    = get_query_var( 's' );
		$q_sent = get_query_var( 'sentence' );

		$terms = $this->_parse_search( $q_s, $q_sent );
		$query = '';

		foreach ( $this->_meta_keys as $key ) {
			foreach ( $terms as $term ) {
				$query .= "((stspm.meta_key = '{$key}') AND (stspm.meta_value LIKE '%{$term}%')) OR ";
			}
		}
		if ( ! empty( $query ) ) {
			global $wpdb;
			$pieces['distinct'] = 'DISTINCT';
			$pieces['where'] = ' AND ' . $query . '1=1' . $pieces['where'];
			$pieces['join'] = $pieces['join'] . " INNER JOIN {$wpdb->postmeta} AS stspm ON ({$wpdb->posts}.ID = stspm.post_id)";
		}
		return $pieces;
	}

	public function _cb_pre_get_posts( $query ) {  // for add_post_type
		if ( $query->is_search ) {
			$val = $query->get( 'post_type' );
			if ( empty( $val ) ) $query->set( 'post_type', $this->_post_types );
		}
		return $query;
	}


	// Private Functions -------------------------------------------------------


	private function _parse_search( $q_s, $q_sent ) {
		global $wp_the_query;

		$q_s = stripslashes( $q_s );
		if ( empty( $_GET['s'] ) && $wp_the_query->is_main_query() ) $q_s = urldecode( $q_s );
		$q_s = str_replace( ["\r", "\n"], '', $q_s );

		$terms = [];
		if ( ! empty( $q_sent ) ) {
			$terms = [$q_s];
		} else {
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q_s, $matches ) ) {
				$terms = $this->_parse_search_terms( $matches[0] );
				if ( empty( $terms ) || count( $terms ) > 9 ) $terms = [$q_s];
			} else {
				$terms = [$q_s];
			}
		}
		return $terms;
	}

	private function _parse_search_terms( $terms ) {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked = [];

		foreach ( $terms as $term ) {
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) continue;
			if ( in_array( call_user_func( $strtolower, $term ), $this->_stop_words, true ) ) continue;
			$checked[] = $term;
		}
		return $checked;
	}

	private function _get_search_stopwords() {
		$stopwords = explode( ',', 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www' );
		return $stopwords;
	}

}
