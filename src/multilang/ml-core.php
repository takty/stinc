<?php
namespace st;

/**
 *
 * Multi-Language Site with Single Site (Core)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-04-20
 *
 */


class Multilang_Core {

	const DEFAULT_QUERY_VAR = 'site_lang';
	const ADMIN_QUERY_VAR   = 'sub_tree';
	const BODY_CLASS_BASE   = 'site-lang-';

	private $_query_var;
	private $_site_langs;
	private $_default_site_lang;

	private $_tag;

	public function __construct( $site_langs, $default_lang = false, $query_var = self::DEFAULT_QUERY_VAR ) {
		$this->_site_langs        = $site_langs;
		$this->_default_site_lang = ( $default_lang === false ) ? $site_langs[0] : $default_lang;
		$this->_query_var         = $query_var;
	}

	public function set_tag( $tag ) {
		$this->_tag = $tag;
	}

	public function initialize() {
		add_filter( 'query_vars',        [ $this, '_query_vars' ] );
		add_action( 'template_redirect', [ $this, '_template_redirect' ] );

		// Page
		add_filter( 'request',       [ $this, '_request' ] );
		add_filter( 'url_to_postid', [ $this, '_url_to_postid' ] );
		add_filter( 'page_link',     [ $this, '_page_link' ] );

		// Post
		add_filter( 'post_rewrite_rules', [ $this, '_post_rewrite_rules' ] );

		// Custom Post Type and Custom Taxonomy
		global $wp_rewrite;
		$embeddedNames = [ 'category', 'post_tag', 'post_format' ];
		foreach ( $wp_rewrite->extra_permastructs as $permastructname => $struct ) {
			if ( in_array( $permastructname, $embeddedNames, true ) === false ) {
				add_filter( "{$permastructname}_rewrite_rules", [ $this, '_add_lang_rewrite_rules' ] );
			}
		}
		foreach ( $wp_rewrite->extra_rules_top as $key => $val ) {
			if ( $key[0] === '^' ) continue;
			$key = '([a-z]{2})/' . $key;
			$val = $this->_shift_matches( $val ) . '&' . $this->_query_var . '=$matches[1]';
			add_rewrite_rule( $key, $val, 'top' );
		}

		add_filter( 'post_tag_rewrite_rules', [ $this, '_add_lang_rewrite_rules' ] );
		add_filter( 'category_rewrite_rules', [ $this, '_add_lang_rewrite_rules' ] );
		add_filter( 'date_rewrite_rules',     [ $this, '_add_lang_rewrite_rules' ] );
		add_filter( 'search_rewrite_rules',   [ $this, '_add_lang_rewrite_rules' ] );
		add_filter( 'root_rewrite_rules',     [ $this, '_add_lang_rewrite_rules' ] );

		add_filter( 'post_link',              [ $this, '_insert_lang_to_url' ], 10, 2 );
		add_filter( 'post_type_link',         [ $this, '_insert_lang_to_url' ], 10, 2 );
		add_filter( 'post_type_archive_link', [ $this, '_insert_lang_to_url' ] );
		add_filter( 'term_link',              [ $this, '_insert_lang_to_url' ] );
		add_filter( 'year_link',              [ $this, '_insert_lang_to_url' ] );
		add_filter( 'month_link',             [ $this, '_insert_lang_to_url' ] );
		add_filter( 'day_link',               [ $this, '_insert_lang_to_url' ] );
		add_filter( 'search_link',            [ $this, '_insert_lang_to_url' ] );
		add_filter( 'feed_link',              [ $this, '_insert_lang_to_url' ] );

		add_filter( 'language_attributes',    [ $this, '_replace_html_lang_attribute' ], 10, 2 );
		if ( ! is_admin() ) {
			add_filter( 'body_class', [ $this, '_cb_body_class' ] );
		}
		$this->_initialize_page_edit_menu();
	}

	public function get_site_lang() {
		global $wp_query;
		if ( ! empty( $wp_query->query_vars[ $this->_query_var ] ) ) {
			return $wp_query->query_vars[ $this->_query_var ];
		}
		return $this->_default_site_lang;
	}

	public function is_site_lang( $lang ) {
		return $this->get_site_lang() === $lang;
	}

	public function get_site_langs( $with_default_site_lang = true ) {
		if ( $with_default_site_lang ) {
			return $this->_site_langs;
		}
		$temp = array_diff( $this->_site_langs, [ $this->_default_site_lang ] );
		return array_values( $temp );
	}

	public function get_default_site_lang() {
		return $this->_default_site_lang;
	}

	public function is_default_site_lang() {
		return $this->get_site_lang() === $this->_default_site_lang;
	}

	public function home_url( $path = '', $scheme = null, $lang = false ) {
		if ( $lang === false ) $lang = $this->get_site_lang();
		if ( $path !== '' ) $path = '/' . ltrim( $path, '/' );
		return ( $lang === $this->_default_site_lang ) ? home_url( $path, $scheme ) : home_url( $lang . $path, $scheme );
	}

	public function home_urls( $path = '', $scheme = null ) {
		$urls = [];
		foreach ( $this->_site_langs as $lang ) {
			$urls[ $lang ] = ( $lang === $this->_default_site_lang ) ? home_url( $path, $scheme ) : home_url( $lang . '/' . ltrim( $path, '/' ), $scheme );
		}
		return $urls;
	}

	public function is_front_page() {
		return is_page( $this->get_site_lang() );
	}

	public function get_site_lang_list( $site_langs_to_name, $before = '', $sep = '', $after = '', $additional_path = '' ) {
		if ( empty( $site_langs_to_name ) ) return false;
		$links = [];
		$site_lang = $this->get_site_lang();
		foreach ( $site_langs_to_name as $lang => $name ) {
			$current = ( $lang === $site_lang );
			if ( $lang === $this->_default_site_lang ) {
				$link = home_url( $additional_path );
			} else {
				$path = empty( $additional_path ) ? $lang : ( $lang . '/' . ltrim( $additional_path, '/' ) );
				$link = home_url( $path );
			}
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag"' . ($current ? ' class="current"' : '') . '>' . $name . '</a>';
		}
		return $before . join( $sep, $links ) . $after;
	}


	// Private Functions -------------------------------------------------------

	private function _initialize_page_edit_menu() {
		if ( is_admin() ) {
			global $wp;
			$wp->add_query_var( self::ADMIN_QUERY_VAR );
		}
		add_action( 'admin_menu', [$this, '_admin_menu_page_edit_menu'] );
		add_action( 'parse_query', function ( $query ) {
			if ( isset( $_GET[ self::ADMIN_QUERY_VAR ] ) ) {
				global $pagenow;
				$qv = $query->query_vars;
				if ( $pagenow === 'edit.php' && ( ( isset( $qv['post_type'] ) && $qv['post_type'] === 'page' ) || ( isset( $_GET['post_type'] ) && $_GET['post_type'] ) ) ) {
					$root = intval( $_GET[ self::ADMIN_QUERY_VAR ] );
					$ids = array_reverse( get_post_ancestors( $root ) );  // Must contains the posts with (parent_id === 0) because of the algorithmn of WP_Posts_List_Table->_display_rows_hierarchical()
					$ids[] = $root;

					$ps = get_pages( [ 'child_of' => $root, 'sort_column' => 'menu_order', 'post_status' => 'publish,future,draft,pending,private' ] );
					foreach ( $ps as $p ) $ids[] = $p->ID;

					$query->set( 'post__in', $ids );
					$query->set( 'orderby', 'post__in' );
				}
			}
		} );
	}

	public function _query_vars( $vars ) {
		$vars[] = $this->_query_var;
		return $vars;
	}

	public function _template_redirect() {
		$host = \st\get_server_host();
		$url = ( is_ssl() ? 'https' : 'http' ) . '://' . $host . $_SERVER['REQUEST_URI'];
		$home = get_option( 'home' );
		$cur_url = str_replace( $home . '/' . $this->_default_site_lang, $home, $url );
		if ( $url !== $cur_url ) exit( wp_redirect( $cur_url ) );
	}

	public function _request( $query_vars ) {
		if ( ! isset( $query_vars['pagename'] ) ) {
			if ( isset( $query_vars[ $this->_query_var ] ) ) {
				$lang = $query_vars[ $this->_query_var ];
				if ( ! in_array( $lang, $this->_site_langs, true ) ) {
					$query_vars[ $this->_query_var ] = $this->_default_site_lang;
				}
			}
			return $query_vars;
		}
		$pn = $query_vars['pagename'];
		$lang = '';
		if ( strlen( $pn ) === 2 && 1 === preg_match( '/^([a-z]{2})/', $pn ) ) {
			$lang = $pn;
		} else if (1 === preg_match( '/^([a-z]{2})\//', $pn, $matches ) ) {
			$lang = $matches[1];
		}
		if ( in_array( $lang, $this->_site_langs, true ) ) $query_vars[ $this->_query_var ] = $lang;
		if ( ! isset( $query_vars[ $this->_query_var ] ) ) {
			$pn = $this->_default_site_lang . '/' . $pn;
			if ( get_page_by_path( $pn ) !== null ) $query_vars['pagename'] = $pn;
		}
		return $query_vars;
	}

	public function _url_to_postid( $url ) {
		$home = get_option( 'home' );
		$pn = str_replace( trailingslashit( $home ), '', $url );
		$lang = '';
		if ( strlen( $pn ) === 2 && 1 === preg_match( '/^([a-z]{2})/', $pn ) ) {
			$lang = $pn;
		} else if (1 === preg_match( '/^([a-z]{2})\//', $pn, $matches ) ) {
			$lang = $matches[1];
		}
		if ( in_array( $lang, $this->_site_langs, true ) ) return $url;
		$url = str_replace( trailingslashit( $home ), trailingslashit( $home ) . $this->_default_site_lang . '/', $url );
		return $url;
	}

	public function _page_link( $link ) {
		$home = get_option( 'home' );
		return str_replace( $home . '/' . $this->_default_site_lang, $home, $link );
	}

	public function _post_rewrite_rules( $rewrite_rules ) {
		$new_rewrite_rules = [];
		foreach ( $rewrite_rules as $key => $val ) {
			$key = '([a-z]{2})/' . $key;
			$val = $this->_shift_matches( $val );
			$new_rewrite_rules[$key] = $val . '&' . $this->_query_var . '=$matches[1]';
		}
		foreach ( $rewrite_rules as $key => $val ) {
			$new_rewrite_rules[$key] = $val;
		}
		return $new_rewrite_rules;
	}

	public function _add_lang_rewrite_rules( $rewrite_rules ) {
		foreach ( $rewrite_rules as $key => $val ) {
			$key = '([a-z]{2})/' . $key;
			$val = $this->_shift_matches( $val );
			$rewrite_rules[$key] = $val . '&' . $this->_query_var . '=$matches[1]';
		}
		return $rewrite_rules;
	}

	private function _shift_matches( $val ) {
		for ( $i = 10; $i > 0; $i -= 1 ) {
			$val = str_replace( '$matches[' . $i . ']', '$matches[' . ($i + 1) . ']', $val );
		}
		return $val;
	}

	public function _insert_lang_to_url( $link, $post = false ) {
		if ( is_admin() && is_a( $post, 'WP_Post' ) ) {
			if ( ! $this->_tag || ! $this->_tag->has_tag( $post->post_type ) ) return $link;
			$ts = get_the_terms( $post->ID, $this->_tag->get_taxonomy() );
			if ( is_array( $ts ) ) {
				$lang = $ts[0]->slug;
			} else {
				$lang = $this->_default_site_lang;
			}
		} else {
			$lang = $this->get_site_lang();
		}
		if ( $lang !== $this->_default_site_lang ) {
			$home = get_option( 'home' );
			$link = str_replace( $home, $home . '/' . $lang, $link );
		}
		return $link;
	}

	public function _replace_html_lang_attribute( $output, $doctype ) {
		$lang = $this->get_site_lang();
		return preg_replace( '/lang=\"[a-z]{2}\"/', "lang=\"$lang\"", $output );
	}

	public function _admin_menu_page_edit_menu() {
		$menu_slug = 'edit.php?post_type=page&' . self::ADMIN_QUERY_VAR . '=';
		$site_langs = $this->_site_langs;
		$title = __( 'All Pages', 'default' );

		foreach ( $site_langs as $sl ) {
			$page = get_page_by_path( $sl );
			if ( $page !== null ) {
				add_pages_page( '', "$title [$sl]", 'edit_pages', $menu_slug . $page->ID );
			}
		}
	}

	public function _cb_body_class( $classes ) {  // Private
		$classes[] = self::BODY_CLASS_BASE . $this->get_site_lang();
		return $classes;
	}

}
