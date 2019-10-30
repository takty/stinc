<?php
namespace st;

/**
 *
 * Mock of Multi-Language Site with Single Site
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-30
 *
 */


require_once __DIR__ . '/ml-text.php';
require_once __DIR__ . '/ml-title.php';


class Multilang {

	static private $_instance = null;
	static public function initialize( $site_langs, $default_lang = false, $query_var = '' ) {
		if ( self::$_instance !== null ) throw new \Exception( 'Multilang has been already initialized.' );
		self::$_instance = new Multilang( $site_langs, $default_lang, $query_var );
		return self::$_instance;
	}
	static public function get_instance() {
		if ( self::$_instance === null ) throw new \Exception( 'Call Multilang::initialize before get_instance.' );
		return self::$_instance;
	}

	private $_default_site_lang = null;
	private $_text              = null;
	private $_title             = null;

	private function __construct( $site_langs, $default_lang = false, $query_var = '' ) {
		$this->_default_site_lang = $site_langs[0];
	}

	public function initialize_core() {
	}

	public function get_site_lang() {
		return $this->_default_site_lang;
	}

	public function is_site_lang( $lang ) {
		return $this->_default_site_lang === $lang;
	}

	public function get_site_langs( $with_default_site_lang = true ) {
		if ( $with_default_site_lang ) return [ $this->_default_site_lang ];
		return [];
	}

	public function get_default_site_lang() {
		return $this->_default_site_lang;
	}

	public function is_default_site_lang() {
		return true;
	}

	public function home_url( $path = '', $scheme = null, $lang = false ) {
		return home_url( $path, $scheme );
	}

	public function home_urls( $path = '', $scheme = null ) {
		return [ home_url( $path, $scheme ) ];
	}

	public function is_front_page() {
		return is_front_page();
	}

	public function get_site_lang_list( $site_langs_to_name, $before = '', $sep = '', $after = '', $additional_slug = '' ) {
		return $before . '<a href="' . esc_url( home_url( $additional_slug ) ) . '" rel="tag" class="current">' . $name . '</a>' . $after;
	}

	public function lang_home_url( $path = '', $scheme = null, $lang = false ) { return home_url( $path, $scheme ); }
	public function lang_home_urls( $path = '', $scheme = null ) { return [ home_url( $path, $scheme ) ]; }
	public function is_lang_home() { return is_front_page(); }


	// Post --------------------------------------------------------------------

	public function initialize_post( $key_prefix = '_' ) {
	}

	public function add_post_type( $post_type_s ) {
	}


	// Tag ---------------------------------------------------------------------

	public function initialize_tag( $lang_to_names, $taxonomy_name, $taxonomy = Multilang_Tag::DEFAULT_TAXONOMY ) {
	}

	public function add_tagged_post_type( $post_type_s ) {
	}

	public function add_tagged_taxonomy( $taxonomy_s ) {
	}

	public function get_taxonomy() {
		return '';
	}

	public function get_tax_query() {
		return null;
	}

	public function has_tag( $post_type = false ) {
		return false;
	}


	// Taxonomy ----------------------------------------------------------------

	public function initialize_taxonomy( $key_prefix = '_' ) {
	}

	public function add_taxonomy( $taxonomy_s, $with_description = false ) {
	}

	public function get_term_name( $term, $singular = false, $lang = false ) {
		return $term->name;
	}

	public function term_description( $term_id = 0, $taxonomy, $lang = false ) {
		return \st\taxonomy\term_description( $term_id, $taxonomy );
	}

	public function get_term_list( $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
		return \st\taxonomy\get_term_list( $taxonomy, $before, $sep, $after, $add_link, $args );
	}

	public function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
		return \st\taxonomy\get_the_term_list( $post_id, $taxonomy, $before, $sep, $after, $add_link, $args );
	}

	public function get_the_term_names( $post_id, $taxonomy, $lang ) {
		return \st\taxonomy\get_the_term_names( $post_id, $taxonomy, $lang );
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
	}

	public function add_post_type_name_translation( $post_type, $lang, $name, $singular_name = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		$this->_title->add_post_type_name_translation( $post_type, $lang, $name, $singular_name );
	}

	public function add_taxonomy_name_translation( $taxonomy, $lang, $name, $singular_name = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		$this->_title->add_taxonomy_name_translation( $taxonomy, $lang, $name, $singular_name );
	}

	public function get_post_type_name( $post_type, $singular = false, $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_post_type_name( $post_type, $singular, $lang );
	}

	public function get_site_title( $raw = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_site_title( $raw );
	}

	public function get_bloginfo( $show, $filter = 'raw', $lang = false ) {
		return get_bloginfo( $show, $filter );
	}

	public function get_site_name( $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return get_option( 'blogname' );
	}

	public function get_site_description( $lang = false ) {
		if ( $this->_title === null ) $this->initialize_title();
		return get_option( 'blogdescription' );
	}

	public function get_title_date() {
		if ( $this->_title === null ) $this->initialize_title();
		return $this->_title->get_title_date();
	}

}
