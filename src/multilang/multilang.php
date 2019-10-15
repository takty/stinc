<?php
namespace st;

/**
 *
 * Multi-Language Site with Single Site
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-15
 *
 */


require_once __DIR__ . '/../util/url.php';
require_once __DIR__ . '/ml-core.php';
require_once __DIR__ . '/ml-post.php';
require_once __DIR__ . '/ml-tag.php';
require_once __DIR__ . '/ml-taxonomy.php';
require_once __DIR__ . '/ml-text.php';
require_once __DIR__ . '/ml-title.php';


class Multilang {

	static private $_instance = null;
	static public function initialize( $site_langs, $default_lang = false, $query_var = Multilang_Core::DEFAULT_QUERY_VAR ) {
		if ( self::$_instance !== null ) throw new \Exception( 'Multilang has been already initialized.' );
		self::$_instance = new Multilang( $site_langs, $default_lang, $query_var );
	}
	static public function get_instance() {
		if ( self::$_instance === null ) throw new \Exception( 'Call Multilang::initialize before get_instance.' );
		return self::$_instance;
	}

	private $_core;
	private $_post     = null;
	private $_tag      = null;
	private $_taxonomy = null;
	private $_text     = null;
	private $_title    = null;

	private function __construct( $site_langs, $default_lang = false, $query_var = Multilang_Core::DEFAULT_QUERY_VAR ) {
		$this->_core = new Multilang_Core( $site_langs, $default_lang, $query_var );
	}

	public function initialize_core() {
		$this->_core->initialize();
	}

	public function get_site_lang() {
		return $this->_core->get_site_lang();
	}

	public function is_site_lang( $lang ) {
		return $this->_core->is_site_lang( $lang );
	}

	public function get_site_langs( $with_default_site_lang = true ) {
		return $this->_core->get_site_langs( $with_default_site_lang );
	}

	public function get_default_site_lang() {
		return $this->_core->get_default_site_lang();
	}

	public function is_default_site_lang() {
		return $this->_core->is_default_site_lang();
	}

	public function home_url( $path = '', $scheme = null, $lang = false ) {
		return $this->_core->home_url( $path, $scheme, $lang );
	}

	public function home_urls( $path = '', $scheme = null ) {
		return $this->_core->home_urls( $path, $scheme );
	}

	public function is_front_page() {
		return $this->_core->is_front_page();
	}

	public function get_site_lang_list( $site_langs_to_name, $before = '', $sep = '', $after = '', $additional_path = '' ) {
		return $this->_core->get_site_lang_list( $site_langs_to_name, $before, $sep, $after, $additional_path );
	}

	public function lang_home_url( $path = '', $scheme = null, $lang = false ) { return $this->_core->home_url( $path, $scheme, $lang ); }
	public function lang_home_urls( $path = '', $scheme = null ) { return $this->_core->home_urls( $path, $scheme ); }
	public function is_lang_home() { return $this->_core->is_front_page(); }


	// Post --------------------------------------------------------------------

	public function initialize_post( $key_prefix = '_' ) {
		$this->_post = new Multilang_Post( $this, $key_prefix );
	}

	public function add_post_type( $post_type_s ) {
		if ( $this->_post === null ) $this->initialize_post();
		$this->_post->add_post_type( $post_type_s );
	}


	// Tag ---------------------------------------------------------------------

	public function initialize_tag( $lang_to_names, $taxonomy_name, $taxonomy = Multilang_Tag::DEFAULT_TAXONOMY ) {
		$this->_tag = new Multilang_Tag( $this, $lang_to_names, $taxonomy_name, $taxonomy );
		$this->_core->set_tag( $this->_tag );
	}

	public function add_tagged_post_type( $post_type_s ) {
		$this->_tag->add_tagged_post_type( $post_type_s );
	}

	public function add_tagged_taxonomy( $taxonomy_s ) {
		$this->_tag->add_tagged_taxonomy( $taxonomy_s );
	}

	public function get_taxonomy() {
		return $this->_tag->get_taxonomy();
	}

	public function get_tax_query() {
		if ( $this->_tag === null ) return [];
		return $this->_tag->get_tax_query();
	}

	public function has_tag( $post_type = false ) {
		if ( $this->_tag === null || $post_type === false ) return $this->_tag !== null;
		return $this->_tag->has_tag( $post_type );
	}


	// Taxonomy ----------------------------------------------------------------

	public function initialize_taxonomy( $key_prefix = '_' ) {
		$this->_taxonomy = new Multilang_Taxonomy( $this, $key_prefix );
	}

	public function add_taxonomy( $taxonomy_s, $with_description = false ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		$this->_taxonomy->add_taxonomy( $taxonomy_s, $with_description );
	}

	public function get_term_name( $term, $singular = false, $lang = false ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		return $this->_taxonomy->get_term_name( $term, $singular, $lang );
	}

	public function term_description( $term_id = 0, $taxonomy, $lang = false ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		return $this->_taxonomy->term_description( $term_id, $taxonomy, $lang );
	}

	public function get_term_list( $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		return $this->_taxonomy->get_term_list( $taxonomy, $before, $sep, $after, $add_link, $args );
	}

	public function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		return $this->_taxonomy->get_the_term_list( $post_id, $taxonomy, $before, $sep, $after, $add_link );
	}

	public function get_the_term_names( $post_id = 0, $taxonomy, $singular = false, $lang = false ) {
		if ( $this->_taxonomy === null ) $this->initialize_taxonomy();
		return $this->_taxonomy->get_the_term_names( $post_id, $taxonomy, $singular, $lang );
	}


	// Text --------------------------------------------------------------------

	public function initialize_text() {
		$this->_text = new Multilang_Text( $this );
	}

	public function add_text_translation( $text, $lang, $trans ) {
		if ( $this->_text === null ) $this->initialize_text();
		$this->_text->add_text_translation( $text, $lang, $trans );
	}

	public function add_date_format_translation( $lang_s, $year = 'Y', $month = 'Y-m', $day = 'Y-m-d' ) {
		if ( $this->_text === null ) $this->initialize_text();
		$this->_text->add_date_format_translation( $lang_s, $year, $month, $day );
	}

	public function translate_text( $text, $lang = false ) {
		if ( $this->_text === null ) $this->initialize_text();
		return $this->_text->translate_text( $text, $lang );
	}

	public function is_date_format_added() {
		if ( $this->_text === null ) return false;
		return $this->_text->is_date_format_added();
	}

	public function has_date_format( $lang ) {
		if ( $this->_text === null ) return false;
		return $this->_text->has_date_format( $lang );
	}

	public function get_date_format( $type = 'day', $lang = false ) {
		if ( $this->_text === null ) $this->initialize_text();
		return $this->_text->get_date_format( $type, $lang );
	}

	public function format_date( $date ) {
		if ( $this->_text === null ) $this->initialize_text();
		return $this->_text->format_date( $date );
	}


	// Title -------------------------------------------------------------------

	public function initialize_title( $default_lang = false ) {
		if ( $this->_text === null ) $this->initialize_text();
		$this->_title = new Multilang_Title( $this, $this->_text, $default_lang );
	}

	public function set_blog_title_filter_suppressed( $suppressed ) {  // for Multihome
		if ( $this->_text === null ) $this->initialize_text();
		$this->_title->set_blog_title_filter_suppressed( $suppressed );
	}

	public function add_post_type_name_translation( $post_type, $lang, $name, $singular_name = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		$this->_title->add_post_type_name_translation( $post_type, $lang, $name, $singular_name );
	}

	public function add_taxonomy_name_translation( $taxonomy, $lang, $name, $singular_name = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		$this->_title->add_taxonomy_name_translation( $taxonomy, $lang, $name, $singular_name );
	}

	public function get_post_type_name( $post_type, $lang = false, $singular_name = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_post_type_name( $post_type, $lang, $singular_name );
	}

	public function get_site_title( $raw = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_site_title( $raw );
	}

	public function get_bloginfo( $show, $filter = 'raw', $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_bloginfo( $show, $filter, $lang );
	}

	public function get_site_name( $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_site_name( $lang );
	}

	public function get_site_description( $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_site_description( $lang );
	}

	public function get_title_date() {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_title_date();
	}

}
