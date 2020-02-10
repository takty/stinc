<?php
namespace st;
/**
 *
 * Multi-Home Site with Single Site (Core)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-02-10
 *
 */


class Multihome_Core {

	const DEFAULT_QUERY_VAR = 'site_home';
	const ADMIN_QUERY_VAR   = 'sub_tree';
	const BODY_CLASS_BASE   = 'site-home-';

	private $_ml  = null;
	private $_tag = null;
	
	private $_query_var;
	private $_site_homes = [];
	private $_default_home  = '';
	
	private $_home_to_title = [];
	private $_is_root_default_home = false;

	private $_request_home = '';

	public function __construct( $query_var = self::DEFAULT_QUERY_VAR, $ml ) {
		$this->_query_var = $query_var;
		$this->_ml = $ml;
	}

	public function set_tag( $tag ) {
		$this->_tag = $tag;
	}

	public function get_home_to_title() {
		return $this->_home_to_title;
	}

	public function add_home( $slug, $title, $is_default = false ) {
		$this->_home_to_title[ $slug ] = $title;
		$this->_site_homes[]           = $slug;

		if ( $is_default ) $this->_default_home = $slug;
	}

	public function set_root_default_home( $enabled ) {
		$this->_is_root_default_home = $enabled;
	}

	public function initialize() {
		if ( is_admin() ) {
			global $wp;
			$wp->add_query_var( self::ADMIN_QUERY_VAR );

			add_filter( 'post_link',      [ $this, '_cb_insert_home_to_url' ], 10, 2 );
			add_filter( 'post_type_link', [ $this, '_cb_insert_home_to_url' ], 10, 2 );

			add_action( 'admin_menu',     [ $this, '_cb_admin_menu' ] );
		} else {
			add_filter( 'query_vars',        [ $this, '_cb_query_vars' ] );
			add_filter( 'do_parse_request',  [ $this, '_cb_do_parse_request' ], 10, 3 );
			add_filter( 'request',           [ $this, '_cb_request' ] );
			add_action( 'template_redirect', [ $this, '_cb_template_redirect' ] );

			add_filter( 'post_link',              [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'post_type_link',         [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'post_type_archive_link', [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'paginate_links',         [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'term_link',              [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'year_link',              [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'month_link',             [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'day_link',               [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'search_link',            [ $this, '_cb_insert_home_to_url' ] );
			add_filter( 'feed_link',              [ $this, '_cb_insert_home_to_url' ] );

			add_filter( 'body_class', [ $this, '_cb_body_class' ] );
		}
		if ( is_admin_bar_showing() ) {
			add_action( 'admin_bar_menu', [ $this, '_cb_admin_bar_menu' ] );
		}
	}

	public function get_site_home() {
		global $wp_query;
		if ( ! empty( $wp_query->query_vars[ $this->_query_var ] ) ) {
			return $wp_query->query_vars[ $this->_query_var ];
		}
		return $this->_default_home;
	}

	public function get_site_homes( $with_default = true ) {
		if ( $with_default || empty( $this->_default_home ) ) {
			return $this->_site_homes;
		}
		$temp = array_diff( $this->_site_homes, [ $this->_default_home ] );
		return array_values( $temp );
	}

	public function home_url( $path = '', $scheme = null, $site_lang = false ) {
		$home = $this->get_site_home();
		if ( $path !== '' ) $path = '/' . ltrim( $path, '/' );
		if ( $this->_is_root_default_home && $home === $this->_default_home ) {
			return $this->_ml->home_url( $path, $scheme, $site_lang );
		} else {
			return $this->_ml->home_url( $home . $path, $scheme, $site_lang );
		}
	}

	public function is_front_page() {
		return trailingslashit( $this->home_url() ) === trailingslashit( \st\get_current_uri() );
	}

	public function get_site_slug( $home = false ) {
		if ( $home === false ) $home = $this->get_site_home();
		return $home;
	}


	// Private Functions -------------------------------------------------------


	public function _cb_query_vars( $vars ) {  // Private
		$vars[] = $this->_query_var;
		return $vars;
	}

	public function _cb_do_parse_request( $bool, $wp, $extra_query_vars ) {  // Private
		$req = $this->_get_request();
		extract( $req );  // $requested_path, $requested_file

		$ps = explode( '/', $requested_path );
		$langs = $this->_ml->get_site_langs();
		$homes = $this->get_site_homes();

		$home_slug = '';
		if ( 1 < count( $ps ) && in_array( $ps[0], $langs, true ) && in_array( $ps[1], $homes, true ) ) {
			$home_slug = $ps[1];
		} else if ( 0 < count( $ps ) && in_array( $ps[0], $homes, true ) ) {
			$home_slug = $ps[0];
		}

		if ( empty( $home_slug ) ) {
			if ( $this->_is_root_default_home ) $this->_request_home = $this->_default_home;
		} else {
			$this->_request_home = $home_slug;

			// Here, $requested_path is trimed by '/' in _get_request().
			$new_path = trim( str_replace( "/$home_slug/", '/', "/$requested_path/" ), '/' );

			if ( $this->_is_page_request( $requested_path, $requested_file ) ) {
				if ( ! $this->_is_page_request( $new_path, $requested_file ) ) {
					$_SERVER['REQUEST_URI_ORIG'] = $_SERVER['REQUEST_URI'];
					$_SERVER['REQUEST_URI'] = rtrim( str_replace( $requested_path, $new_path, $_SERVER['REQUEST_URI'] ), '/' );
				}
			} else {
				$_SERVER['REQUEST_URI_ORIG'] = $_SERVER['REQUEST_URI'];
				$_SERVER['REQUEST_URI'] = rtrim( str_replace( $requested_path, $new_path, $_SERVER['REQUEST_URI'] ), '/' );
			}
		}
		return $bool;
	}

	private function _is_page_request( $requested_path, $requested_file ) {
		if ( empty( $requested_path ) ) return true;

		global $wp_rewrite;
		$rewrite = $wp_rewrite->wp_rewrite_rules();
		if ( empty( $rewrite ) ) return false;

		$request_match = $requested_path;
		foreach ( (array) $rewrite as $match => $query ) {
			// if ( preg_match( '/site_lang=\$matches\[([0-9]+)\]/', $query ) ) continue;  // tentative

			if ( ! empty( $requested_file ) && strpos( $match, $requested_file ) === 0 && $requested_file != $requested_path ) {
				$request_match = $requested_file . '/' . $requested_path;
			}
			if ( preg_match( "#^$match#", $request_match, $matches ) || preg_match( "#^$match#", urldecode( $request_match ), $matches ) ) {
				if ( preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query ) ) {
					return true;  // Request is a page!
				}
				break;
			}
		}
		return false;
	}

	private function _get_request() {
		global $wp_rewrite;
		$rewrite = $wp_rewrite->wp_rewrite_rules();
		if ( empty( $rewrite ) ) return '';

		$pathinfo         = isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : '';
		list( $pathinfo ) = explode( '?', $pathinfo );
		$pathinfo         = str_replace( "%", "%25", $pathinfo );

		list( $req_uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
		$home_path       = trim( parse_url( home_url(), PHP_URL_PATH ), '/' );
		$home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );

		$req_uri  = str_replace( $pathinfo, '', $req_uri );
		$req_uri  = trim( $req_uri, '/' );
		$req_uri  = preg_replace( $home_path_regex, '', $req_uri );
		$req_uri  = trim( $req_uri, '/' );
		$pathinfo = trim( $pathinfo, '/' );
		$pathinfo = preg_replace( $home_path_regex, '', $pathinfo );
		$pathinfo = trim( $pathinfo, '/' );

		if ( ! empty( $pathinfo ) && ! preg_match( '|^.*' . $wp_rewrite->index . '$|', $pathinfo ) ) {
			$requested_path = $pathinfo;
		} else {
			if ( $req_uri === $wp_rewrite->index ) $req_uri = '';
			$requested_path = $req_uri;
		}
		$requested_file = $req_uri;
		return compact( 'requested_path', 'requested_file' );
	}

	public function _cb_request( $query_vars ) {  // Private
		if ( ! empty( $this->_request_home ) ) {
			$query_vars[ $this->_query_var ] = $this->_request_home;

			if ( $this->_is_root_default_home && $this->_request_home === $this->_default_home ) {
				$pagename = isset( $query_vars['pagename'] ) ? $query_vars['pagename'] : '';

				$lang = $this->_ml->get_default_site_lang();
				if ( ! empty( $pagename ) ) {
					$ps = explode( '/', $pagename );
					$langs = $this->_ml->get_site_langs();
					if ( 0 < count( $ps ) && in_array( $ps[0], $langs, true ) ) $lang = $ps[0];
					$pagename = str_replace( "$lang/", '', "$pagename/" );
					$pagename = rtrim( $pagename, '/' );
				}
				$pn = $lang . '/' . $this->_default_home;
				if ( ! empty( $pagename ) ) {
					$pn .= '/' . $pagename;
				}
				if ( get_page_by_path( $pn ) !== null ) $query_vars['pagename'] = $pn;
			}
		}
		return $query_vars;
	}

	public function _cb_template_redirect() {  // Private
		if ( empty( $this->_default_home ) ) return;

		global $wp_query;
		$home = '';
		if ( ! empty( $wp_query->query_vars[ $this->_query_var ] ) ) {
			$home = $wp_query->query_vars[ $this->_query_var ];
		}

		$url      = \st\get_current_uri();
		$new_url  = $url;
		$home_url = $this->_ml->home_url();

		if ( $this->_is_root_default_home ) {
			$new_url = str_replace( $home_url . '/' . $this->_default_home, $home_url, $url );
		} else if ( empty( $home ) ) {
			$new_url = str_replace( $home_url , $home_url . '/' . $this->_default_home, $url );
		}
		if ( $url !== $new_url ) exit( wp_redirect( $new_url ) );
	}


	// -------------------------------------------------------------------------


	public function _cb_insert_home_to_url( $link, $post = false ) {  // Private
		$fs = \st\get_first_slug( $link );
		$lang = false;
		if ( ! empty( $fs ) && in_array( $fs, $this->get_site_homes(), true ) ) {
			$link = str_replace( "$fs/", '', $link );
		} else if ( in_array( $fs, $this->_ml->get_site_langs(), true ) ) {
			$lang = $fs;
		}
		if ( is_admin() && is_a( $post, 'WP_Post' ) && $this->_tag && $this->_tag->has_tag( $post->post_type ) ) {
			$ts = get_the_terms( $post->ID, $this->_tag->get_taxonomy() );
			if ( is_array( $ts ) ) {
				$sh = $ts[0]->slug;
			} else {
				$sh = $this->_default_home;
			}
		} else {
			$sh = $this->get_site_home();
		}
		if ( $this->_is_root_default_home && $sh === $this->_default_home ) {
			$home_url = $this->_ml->home_url();
			if ( $lang ) {
				$link = str_replace( "$home_url/$lang", "$home_url/$lang/$sh", $link );
			} else {
				$link = str_replace( $home_url, "$home_url/$sh", $link );
			}
		}
		return $link;
	}

	public function _cb_body_class( $classes ) {  // Private
		$classes[] = self::BODY_CLASS_BASE . $this->get_site_home();
		return $classes;
	}


	// -------------------------------------------------------------------------


	public function _cb_admin_menu() {  // Private
		$menu_slug = 'edit.php?post_type=page&' . self::ADMIN_QUERY_VAR . '=';
		$site_langs = $this->_ml->get_site_langs();

		foreach ( $this->_home_to_title as $home => $title ) {
			foreach ( $site_langs as $sl ) {
				$page = get_page_by_path( "$sl/$home" );
				if ( $page === null ) continue;

				add_pages_page( '', "$title [$sl]", 'edit_pages', $menu_slug . $page->ID );
			}
		}
	}

	public function _cb_admin_bar_menu( $wp_admin_bar ) {  // Private
		$site_langs = $this->_ml->get_site_langs();

		foreach ( $this->_home_to_title as $home => $title ) {
			foreach ( $site_langs as $sl ) {
				$page = get_page_by_path( "$sl/$home" );
				if ( $page === null ) continue;

				$wp_admin_bar->add_menu( [
					'id'     => "view-site-$sl-$home",
					'parent' => 'site-name',
					'title'  => "$title [$sl]",
					'href'   => home_url( "$sl/$home" )
				] );
			}
		}
	}

}
