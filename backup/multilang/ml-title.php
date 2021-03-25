<?php
namespace st;

/**
 *
 * Multi-Language Site with Single Site (Title)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-09-15
 *
 */


require_once __DIR__ . '/../util/text.php';


class Multilang_Title {

	private $_core;
	private $_text;
	private $_default_trans_lang;
	private $_post_type_name_dic = [];
	private $_taxonomy_name_dic = [];

	private $_is_archive_title_filtered = false;
	private $_is_post_type_name_filtered = false;
	private $_is_taxonomy_name_filtered = false;

	private $_is_blog_title_filter_suppressed = false;  // for Multihome

	public function __construct( $core, $text, $default_lang = false ) {
		$this->_core = $core;
		$this->_text = $text;
		$this->_default_trans_lang = ( $default_lang === false ) ? $core->get_default_site_lang() : $default_lang;

		add_filter( 'document_title_parts',    [ $this, '_cb_document_title_parts' ] );
		add_filter( 'get_the_archive_title',   [ $this, '_cb_get_the_archive_title' ], 10 );
		add_filter( 'post_type_archive_title', [ $this, '_cb_post_type_archive_title' ], 10, 2 );

		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, '_cb_admin_init_add_site_names' ] );
		}
	}

	public function set_blog_title_filter_suppressed( $suppressed ) {  // for Multihome
		$this->_is_blog_title_filter_suppressed = $suppressed;
	}

	public function add_post_type_name_translation( $post_type, $lang, $name, $singular_name = false ) {
		if ( $singular_name === false ) {
			$singular_name = $name;
		}
		if ( ! isset( $this->_post_type_name_dic[ $lang ] ) ) $this->_post_type_name_dic[ $lang ] = [];
		$lang_dic = &$this->_post_type_name_dic[ $lang ];
		$lang_dic[ $post_type ] = [ $name, $singular_name ];

		if ( $post_type === 'post' ) {
			$this->_is_archive_title_filtered = true;
		} else {
			$this->_is_post_type_name_filtered = true;
		}
		if ( is_admin() ) {
			$lang_c = explode( '_', get_user_locale() );
			if ( $lang_c[0] === $lang ) {
				global $wp_post_types;
				$labels = $wp_post_types[ $post_type ]->labels;
				$labels->name           = $name;
				$labels->all_items      = $name;
				$labels->menu_name      = $name;
				$labels->archives       = $name;
				$labels->singular_name  = $singular_name;
				$labels->name_admin_bar = $singular_name;
			}
		}
	}

	public function add_taxonomy_name_translation( $taxonomy, $lang, $name, $singular_name = false ) {
		if ( $singular_name === false ) {
			$singular_name = $name;
		}
		if ( ! isset( $this->_taxonomy_name_dic[ $lang ] ) ) $this->_taxonomy_name_dic[ $lang ] = [];
		$lang_dic = &$this->_taxonomy_name_dic[ $lang ];
		$lang_dic[ $taxonomy ] = [ $name, $singular_name ];

		$this->_is_taxonomy_name_filtered = true;
	}

	public function get_post_type_name( $post_type, $singular = false, $lang = false ) {
		if ( $lang === false ) {
			$lang = $this->_core->get_site_lang();
		}
		$name = $this->_get_post_type_name( $post_type, $lang, $singular );
		if ( $name !== false ) return $name;

		if ( $lang === $this->_default_trans_lang ) {
			$obj = get_post_type_object( $post_type );
			if ( $obj === null ) return '';
			return $singular ? $obj->labels->singular_name : $obj->labels->name;
		}
		$lang = $this->_default_trans_lang;
		$name = $this->_get_post_type_name( $post_type, $lang, $singular );
		if ( $name !== false ) return $name;

		$obj = get_post_type_object( $post_type );
		if ( $obj === null ) return '';
		return $singular ? $obj->labels->singular_name : $obj->labels->name;
	}

	public function get_site_title( $raw = false ) {
		$ret = [];
		foreach ( $this->_core->get_site_langs() as $lang ) {
			$bn = htmlspecialchars_decode( $this->get_site_name( $lang ) );
			$bd = htmlspecialchars_decode( $this->get_site_description( $lang ) );
			$ret[ "name_$lang" ]        = $raw ? $bn : \st\separate_line( $bn, 'segment' );
			$ret[ "description_$lang" ] = $raw ? $bd : \st\separate_line( $bd, 'segment' );
		}
		$cur = $this->_core->get_site_lang();
		$ret['name']        = $ret[ "name_$cur" ];
		$ret['description'] = $ret[ "description_$cur" ];
		$def = $this->_core->get_default_site_lang();
		$ret['name_def']        = $ret[ "name_$def" ];
		$ret['description_def'] = $ret[ "description_$def" ];
		$sls = $this->_core->get_site_langs( false );
		if ( ! empty( $sls ) ) {
			$ret['name_sub']        = $ret[ "name_$sls[0]" ];
			$ret['description_sub'] = $ret[ "description_$sls[0]" ];
		}
		return $ret;
	}

	public function get_bloginfo( $show, $filter = 'raw', $lang = false ) {
		if ( $show === 'name' ) {
			$output = $this->get_site_name( $lang );
			if ( 'display' === $filter ) return apply_filters( 'bloginfo', $output, $show );
			return $output;
		}
		if ( $show === 'description' ) {
			$output = $this->get_site_description( $lang );
			if ( 'display' === $filter ) return apply_filters( 'bloginfo', $output, $show );
			return $output;
		}
		return get_bloginfo( $show, $filter );
	}

	public function get_site_name( $lang = false ) {
		if ( $lang === false ) $lang = $this->_core->get_site_lang();
		$is_def = $this->_core->get_default_site_lang() === $lang;

		if ( $is_def ) return get_option( 'blogname' );
		$ret = get_option( "blogname_$lang" );
		if ( $ret === false ) return get_option( 'blogname' );
		return $ret;
	}

	public function get_site_description( $lang = false ) {
		if ( $lang === false ) $lang = $this->_core->get_site_lang();
		$is_def = $this->_core->get_default_site_lang() === $lang;

		if ( $is_def ) return get_option( 'blogdescription' );
		$ret = get_option( "blogdescription_$lang" );
		if ( $ret === false ) return get_option( 'blogdescription' );
		return $ret;
	}

	public function get_title_date() {
		if ( is_year()  ) return get_the_date( $this->_text->get_date_format( 'year' ) );
		if ( is_month() ) return get_the_date( $this->_text->get_date_format( 'month' ) );
		if ( is_day()   ) return get_the_date( $this->_text->get_date_format( 'day' ) );
		return false;
	}


	// Private Functions -------------------------------------------------------

	public function _cb_document_title_parts( $title ) {  // Private
		if ( $this->_is_blog_title_filter_suppressed ) return $title;

		global $page, $paged;
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title['page'] = max( $paged, $page );
		}
		if ( $this->_core->is_front_page() ) {
			$title['tagline'] = '';
			$title['site'] = '';
			$title['title'] = $this->get_bloginfo( 'name', 'display' );
		} else {
			$title['site'] = $this->get_bloginfo( 'name', 'display' );
			if ( is_404() ) {
				$title['title'] = __( 'Page not found' );
			} else if ( is_search() ) {
				$title['title'] = get_search_query();
			} else if ( $this->_text->is_date_format_added() && ( is_year() || is_month() || is_day() ) ) {
				$ret = $this->get_title_date();
				if ( $ret !== false ) $title['title'] = $ret;
			}
		}
		return $title;
	}

	public function _cb_get_the_archive_title( $title ) {  // Private
		if ( $this->_is_archive_title_filtered ) {
			if ( $title === __( 'Archives' ) ) return $this->_cb_post_type_archive_title( $title, 'post' );
		}
		if ( $this->_is_taxonomy_name_filtered && is_tax() ) {
			$lang = $this->_core->get_site_lang();

			$tax = get_taxonomy( get_queried_object()->taxonomy );
			$replaced = $tax->labels->singular_name . ': ';
			$tn = $tax->name;

			if ( isset( $this->_taxonomy_name_dic[ $lang ] ) ) {
				$dic = $this->_taxonomy_name_dic[ $lang ];
				if ( isset( $dic[ $tn ] ) ) {
					return str_replace( $replaced, $dic[ $tn ][1] . ': ', $title );
				}
			}
			if ( $lang === $this->_default_trans_lang ) return $title;
			$lang = $this->_default_trans_lang;
			if ( isset( $this->_taxonomy_name_dic[ $lang ] ) ) {
				$dic = $this->_taxonomy_name_dic[ $lang ];
				if ( isset( $dic[ $tn ] ) ) {
					return str_replace( $replaced, $dic[ $tn ][1] . ': ', $title );
				}
			}
		}
		if ( $this->_text->is_date_format_added() && ( is_year() || is_month() || is_day() ) ) {
			$ret = $this->get_title_date();
			if ( $ret !== false ) return $ret;
		}
		return $title;
	}

	public function _cb_post_type_archive_title( $title, $post_type ) {  // Private
		if ( $this->_is_post_type_name_filtered ) {
			$lang = $this->_core->get_site_lang();
			$name = $this->_get_post_type_name( $post_type, $lang );
			if ( $name !== false ) return $name;
		}
		return $title;
	}

	private function _get_post_type_name( $post_type, $lang, $singular = false ) {
		if ( isset( $this->_post_type_name_dic[ $lang ] ) ) {
			$dic = $this->_post_type_name_dic[ $lang ];
			if ( isset( $dic[ $post_type ] ) ) return $dic[ $post_type ][ $singular ? 1 : 0 ];
		}
		return false;
	}

	public function _cb_admin_init_add_site_names() {  // Private
		$langs = $this->_core->get_site_langs( false );
		if ( empty( $langs ) ) return;

		add_settings_section( 'st-multilang-section', __('Language'), function () {}, 'general' );

		foreach ( $langs as $lang ) {
			$key_bn = "blogname_$lang";
			$key_bd = "blogdescription_$lang";
			register_setting( 'general', $key_bn );
			register_setting( 'general', $key_bd );
			add_settings_field( $key_bn, __('Site Title') . " [$lang]", function () use ( $key_bn ) { Multilang_Title::output_input( $key_bn ); }, 'general', 'st-multilang-section' );
			add_settings_field( $key_bd, __('Tagline') . " [$lang]",    function () use ( $key_bd ) { Multilang_Title::output_input( $key_bd ); }, 'general', 'st-multilang-section' );
		}
	}

	static function output_input( $id ) {
?>
		<input name="<?php echo $id ?>" type="text" id="<?php echo $id ?>" value="<?php form_option( $id ); ?>" class="regular-text">
<?php
	}

}
