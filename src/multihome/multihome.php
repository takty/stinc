<?php
namespace st;

/**
 *
 * Multi-Home Site with Single Site
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-05-01
 *
 */


require_once __DIR__ . '/../tag/url.php';
require_once __DIR__ . '/mh-tag.php';


class Multihome {

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new Multihome();
		return self::$_instance;
	}

	const DEFAULT_QUERY_VAR = 'site_home';
	const ADMIN_QUERY_VAR = 'sub_tree';
	const BODY_CLASS_BASE = 'site-home-';

	private $_tag = null;

	private $_query_var;

	private $_ml            = null;
	private $_default_home  = '';
	private $_home_to_title = [];
	private $_home_to_slug  = [];
	private $_slug_to_home  = [];

	private $_request_home = '';

	private function __construct() {
		if ( class_exists( '\st\Multilang' ) ) {
			$this->_ml = \st\Multilang::get_instance();
			$this->_ml->set_blog_title_filter_suppressed( true );
		}
		$this->_query_var = self::DEFAULT_QUERY_VAR;


		add_filter( 'query_vars',           [ $this, '_cb_query_vars' ] );
		add_action( 'template_redirect',    [ $this, '_cb_template_redirect' ] );

		add_filter( 'document_title_parts', [ $this, '_cb_document_title_parts' ] );

		if ( is_admin() ) {
			global $wp;
			$wp->add_query_var( self::ADMIN_QUERY_VAR );

			add_action( 'admin_menu',     [ $this, '_cb_admin_menu' ] );
			add_action( 'admin_init',     [ $this, '_cb_admin_init_add_site_names' ] );
		} else {
			add_filter( 'body_class',     [ $this, '_cb_body_class' ] );

			add_filter( 'do_parse_request',       [ $this, '_cb_do_parse_request' ], 10, 3 );
			add_filter( 'request',                [ $this, '_cb_request' ] );

			add_filter( 'post_link',              [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'post_type_link',         [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'post_type_archive_link', [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'paginate_links',         [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'term_link',              [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'year_link',              [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'month_link',             [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'day_link',               [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'search_link',            [ $this, '_cb_insert_lang_to_url' ] );
			add_filter( 'feed_link',              [ $this, '_cb_insert_lang_to_url' ] );
		}
		if ( is_admin_bar_showing() ) {
			add_action( 'admin_bar_menu', [ $this, '_cb_admin_bar_menu' ] );
		}
	}


	// Core ====================================================================

	public function add_home( $id, $slug, $title, $is_default = false ) {
		$this->_home_to_title[ $id ]  = $title;
		$this->_home_to_slug[ $id ]   = $slug;
		$this->_slug_to_home[ $slug ] = $id;

		if ( $is_default ) $this->_default_home = $id;
	}

	public function get_site_home( $url = false ) {
		global $wp_query;
		if ( ! empty( $wp_query->query_vars[ $this->_query_var ] ) ) {
			return $wp_query->query_vars[ $this->_query_var ];
		}
		return $this->_default_home;
	}

	public function is_front_page() {
		return trailingslashit( $this->home_url() ) === trailingslashit( \st\get_current_uri() );
	}

	public function get_site_homes() {
		return array_keys( $this->_home_to_slug );
	}

	public function home_url( $path = '', $scheme = null, $site_lang = false ) {
		$home = $this->get_site_home();
		$slug = $this->_home_to_slug[ $home ];
		return $this->_ml->home_url( "/$slug/" . ltrim( $path, '/' ), $scheme, $site_lang );
	}

	public function get_site_slug( $home = false ) {
		if ( $home === false ) $home = $this->get_site_home();
		return $this->_home_to_slug[ $home ];
	}


	// Private Functions -------------------------------------------------------

	public function _cb_query_vars( $vars ) {  // Private
		$vars[] = $this->_query_var;
		return $vars;
	}

	public function _cb_template_redirect() {  // Private
		global $wp_query;
		$home = '';
		if ( ! empty( $wp_query->query_vars[ $this->_query_var ] ) ) {
			$home = $wp_query->query_vars[ $this->_query_var ];
		}
		if ( empty( $home ) && ! empty( $this->_default_home ) ) {
			$host = \st\get_server_host();
			$url = ( is_ssl() ? 'https' : 'http' ) . '://' . $host . $_SERVER['REQUEST_URI'];
			$home_url = $this->_ml->home_url();
			$cur_url = str_replace( $home_url , $home_url . '/' . $this->_default_home, $url );
			if ( $url !== $cur_url ) exit( wp_redirect( $cur_url ) );
		}
	}

	public function _cb_insert_lang_to_url( $link ) {  // Private
		$fs = \st\get_first_slug( $link );
		if ( ! empty( $fs ) && in_array( $fs, $this->get_site_homes(), true ) ) {
			$link = str_replace( "$fs/", '', $link );
		}
		$lang = $this->get_site_home();
		$home = $this->_ml->home_url();
		$link = str_replace( $home, "$home/$lang", $link );
		return $link;
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

		if ( ! empty( $home_slug ) ) {
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

		$pathinfo = isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : '';
		list( $pathinfo ) = explode( '?', $pathinfo );
		$pathinfo = str_replace( "%", "%25", $pathinfo );

		list( $req_uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
		$home_path = trim( parse_url( home_url(), PHP_URL_PATH ), '/' );
		$home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );

		$req_uri = str_replace( $pathinfo, '', $req_uri );
		$req_uri = trim( $req_uri, '/' );
		$req_uri = preg_replace( $home_path_regex, '', $req_uri );
		$req_uri = trim( $req_uri, '/' );
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
		}
		return $query_vars;
	}

	public function _cb_admin_menu() {  // Private
		$menu_slug = 'edit.php?post_type=page&' . self::ADMIN_QUERY_VAR . '=';
		$site_langs = $this->_ml->get_site_langs();

		foreach ( $this->_home_to_title as $home => $title ) {
			$slug = $this->_home_to_slug[ $home ];
			foreach ( $site_langs as $sl ) {
				$page = get_page_by_path( "$sl/$slug" );
				if ( $page === null ) continue;

				add_pages_page( '', "$title [$sl]", 'edit_pages', $menu_slug . $page->ID );
			}
		}
	}

	public function _cb_admin_bar_menu( $wp_admin_bar ) {  // Private
		$site_langs = $this->_ml->get_site_langs();

		foreach ( $this->_home_to_title as $home => $title ) {
			$slug = $this->_home_to_slug[ $home ];
			foreach ( $site_langs as $sl ) {
				$page = get_page_by_path( "$sl/$slug" );
				if ( $page === null ) continue;

				$wp_admin_bar->add_menu( [
					'id'     => "view-site-$sl-$slug",
					'parent' => 'site-name',
					'title'  => "$title [$sl]",
					'href'   => home_url( "$sl/$slug" )
				] );
			}
		}
	}

	public function _cb_body_class( $classes ) {  // Private
		$classes[] = self::BODY_CLASS_BASE . $this->get_site_home();
		return $classes;
	}


	// Title ===================================================================

	public function get_site_title( $raw = false ) {
		$ret = $this->_ml->get_site_title( $raw );
		foreach ( $this->_ml->get_site_langs() as $lang ) {
			foreach ( $this->get_site_homes() as $home ) {
				$bn = htmlspecialchars_decode( $this->get_site_name( $lang, $home ) );
				$bd = htmlspecialchars_decode( $this->get_site_description( $lang, $home ) );
				$ret[ "name_{$lang}_$home" ]        = $raw ? $bn : \st\separate_line( $bn, 'segment' );
				$ret[ "description_{$lang}_$home" ] = $raw ? $bd : \st\separate_line( $bd, 'segment' );
			}
		}
		$curl = $this->_ml->get_site_lang();
		$curh = $this->get_site_home();
		$has_ml_mh_name = ! empty( strip_tags( $ret[ "name_{$curl}_$curh" ] ) );
		$has_ml_mh_desc = ! empty( strip_tags( $ret[ "name_{$curl}_$curh" ] ) );
		$ret['name']        = $has_ml_mh_name ? $ret[ "name_{$curl}_$curh" ]        : $ret[ "name_{$curl}" ];
		$ret['description'] = $has_ml_mh_desc ? $ret[ "description_{$curl}_$curh" ] : $ret[ "description_{$curl}" ];
		$sls = $this->_ml->get_site_langs( false );
		if ( ! empty( $sls ) ) {
			$ret['name_sub']        = $ret[ "name_$sls[0]_$curh" ];
			$ret['description_sub'] = $ret[ "description_$sls[0]_$curh" ];
		}
		return $ret;
	}

	public function get_bloginfo( $show, $filter = 'raw', $lang = false, $home = false ) {
		if ( $show === 'name' ) {
			$output = $this->get_site_name( $lang, $home );
			if ( 'display' === $filter ) return apply_filters( 'bloginfo', $output, $show );
			return $output;
		}
		if ( $show === 'description' ) {
			$output = $this->get_site_description( $lang, $home );
			if ( 'display' === $filter ) return apply_filters( 'bloginfo', $output, $show );
			return $output;
		}
		return get_bloginfo( $show, $filter );
	}

	public function get_site_name( $lang = false, $home = false ) {
		if ( $lang === false ) $lang = $this->_ml->get_site_lang();
		if ( $home === false ) $home = $this->get_site_home();

		$ret = get_option( "blogname_{$lang}_$home" );
		if ( empty( $ret ) ) return $this->_ml->get_site_name( $lang );
		return $ret;
	}

	public function get_site_description( $lang = false, $home = false ) {
		if ( $lang === false ) $lang = $this->_ml->get_site_lang();
		if ( $home === false ) $home = $this->get_site_home();

		$ret = get_option( "blogdescription_{$lang}_$home" );
		if ( empty( $ret ) ) return $this->_ml->get_site_description( $lang );
		return $ret;
	}


	// Private Functions -------------------------------------------------------

	public function _cb_document_title_parts( $title ) {  // Private
		global $page, $paged;
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title['page'] = max( $paged, $page );
		}
		if ( $this->is_front_page() ) {
			$title['tagline'] = '';
			$title['site'] = '';
			$title['title'] = $this->get_bloginfo( 'name', 'display' );
		} else {
			$title['site'] = $this->get_bloginfo( 'name', 'display' );
			if ( is_404() ) {
				$title['title'] = __( 'Page not found' );
			} else if ( is_search() ) {
				$title['title'] = get_search_query();
			} else if ( $this->_ml->is_date_format_added() && ( is_year() || is_month() || is_day() ) ) {
				$ret = $this->_ml->get_title_date();
				if ( $ret !== false ) $title['title'] = $ret;
			}
		}
		return $title;
	}

	public function _cb_admin_init_add_site_names() {  // Private
		$langs = $this->_ml->get_site_langs();
		if ( empty( $langs ) ) $langs = [ '' ];

		$homes = $this->get_site_homes();
		if ( empty( $homes ) ) return;

		add_settings_section( 'st-multihome-section', __('Sites'), function () {}, 'general' );

		foreach ( $homes as $home ) {
			$title = $this->_home_to_title[ $home ];
			foreach ( $langs as $lang ) {
				$lang_key = empty( $lang ) ? '' : "_$lang";
				$lang_str = empty( $lang ) ? '' : " [$lang]";
				$key_bn = "blogname{$lang_key}_$home";
				$key_bd = "blogdescription{$lang_key}_$home";
				register_setting( 'general', $key_bn );
				register_setting( 'general', $key_bd );
				add_settings_field( $key_bn, "$title<br>" . __('Site Title') . $lang_str, function () use ( $key_bn ) { Multihome::output_input( $key_bn ); }, 'general', 'st-multihome-section' );
				add_settings_field( $key_bd, "$title<br>" . __('Tagline') . $lang_str,    function () use ( $key_bd ) { Multihome::output_input( $key_bd ); }, 'general', 'st-multihome-section' );
			}
		}
	}

	static function output_input( $id ) {  // Private
?>
		<input name="<?php echo $id ?>" type="text" id="<?php echo $id ?>" value="<?php form_option( $id ); ?>" class="regular-text">
<?php
	}


	// Tag ---------------------------------------------------------------------

	public function initialize_tag( $taxonomy = Multihome_Tag::DEFAULT_TAXONOMY ) {
		$this->_tag = new Multihome_Tag( $this, $taxonomy );
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
