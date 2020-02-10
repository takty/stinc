<?php
namespace st;
/**
 *
 * Multi-Home Site with Single Site
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-02-10
 *
 */


require_once __DIR__ . '/../util/url.php';
require_once __DIR__ . '/mh-core.php';
require_once __DIR__ . '/mh-tag.php';
require_once __DIR__ . '/mh-title.php';


class Multihome {

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new Multihome();
		return self::$_instance;
	}

	private $_ml = null;

	private $_core;
	private $_tag   = null;
	private $_title = null;


	private function __construct( $query_var = Multihome_Core::DEFAULT_QUERY_VAR ) {
		if ( class_exists( '\st\Multilang' ) ) {
			$this->_ml = \st\Multilang::get_instance();
			$this->_ml->set_blog_title_filter_suppressed( true );
		}
		$this->_core = new Multihome_Core( $query_var, $this->_ml );
		$this->_title = new Multihome_Title( $this->_core, $this->_ml );
	}


	// Core --------------------------------------------------------------------


	public function initialize_core() {
		$this->_core->initialize();
	}

	public function add_home( $id, $slug, $title, $is_default = false ) {
		$this->_core->add_home( $id, $slug, $title, $is_default );
	}

	public function set_root_default_home( $enabled ) {
		$this->_core->set_root_default_home( $enabled );
	}

	public function get_site_home( $url = false ) {
		return $this->_core->get_site_home( $url );
	}

	public function is_front_page() {
		return $this->_core->is_front_page();
	}

	public function get_site_homes() {
		return $this->_core->get_site_homes();
	}

	public function home_url( $path = '', $scheme = null, $site_lang = false ) {
		return $this->_core->home_url( $path, $scheme, $site_lang );
	}

	public function get_site_slug( $home = false ) {
		return $this->_core->get_site_slug( $home );
	}


	// Title -------------------------------------------------------------------


	public function get_site_title( $raw = false ) {
		return $this->_title->get_site_title( $raw );
	}

	public function get_bloginfo( $show, $filter = 'raw', $lang = false, $home = false ) {
		return $this->_title->get_bloginfo( $show, $filter, $lang, $home );
	}

	public function get_site_name( $lang = false, $home = false ) {
		return $this->_title->get_site_name( $lang, $home );
	}

	public function get_site_description( $lang = false, $home = false ) {
		return $this->_title->get_site_description( $lang, $home );
	}


	// Tag ---------------------------------------------------------------------


	public function initialize_tag( $taxonomy = Multihome_Tag::DEFAULT_TAXONOMY ) {
		$this->_tag = new Multihome_Tag( $this, $taxonomy );
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

}
