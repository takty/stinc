<?php
namespace st;

/**
 *
 * Multi-Home Site with Single Site (Title)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-02-08
 *
 */


require_once __DIR__ . '/../util/text.php';


class Multihome_Title {

	private $_core;
	// private $_default_home;

	public function __construct( $core ) {
		$this->_core = $core;

		add_filter( 'document_title_parts', [ $this, '_cb_document_title_parts' ] );

		if ( is_admin() ) {
			add_action( 'admin_init',     [ $this, '_cb_admin_init_add_site_names' ] );
		}
	}

	public function get_site_title( $raw = false ) {
		$ret = $this->_core->_ml->get_site_title( $raw );
		foreach ( $this->_core->_ml->get_site_langs() as $lang ) {
			foreach ( $this->_core->get_site_homes() as $home ) {
				$bn = htmlspecialchars_decode( $this->get_site_name( $lang, $home ) );
				$bd = htmlspecialchars_decode( $this->get_site_description( $lang, $home ) );
				$ret[ "name_{$lang}_$home" ]        = $raw ? $bn : \st\separate_line( $bn, 'segment' );
				$ret[ "description_{$lang}_$home" ] = $raw ? $bd : \st\separate_line( $bd, 'segment' );
			}
		}
		$curl = $this->_core->_ml->get_site_lang();
		$curh = $this->_core->get_site_home();
		$has_ml_mh_name = ! empty( strip_tags( $ret[ "name_{$curl}_$curh" ] ) );
		$has_ml_mh_desc = ! empty( strip_tags( $ret[ "name_{$curl}_$curh" ] ) );
		$ret['name']        = $has_ml_mh_name ? $ret[ "name_{$curl}_$curh" ]        : $ret[ "name_{$curl}" ];
		$ret['description'] = $has_ml_mh_desc ? $ret[ "description_{$curl}_$curh" ] : $ret[ "description_{$curl}" ];
		$sls = $this->_core->_ml->get_site_langs( false );
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
		return \get_bloginfo( $show, $filter );
	}

	public function get_site_name( $lang = false, $home = false ) {
		if ( $lang === false ) $lang = $this->_core->_ml->get_site_lang();
		if ( $home === false ) $home = $this->_core->get_site_home();

		$ret = get_option( "blogname_{$lang}_$home" );
		if ( empty( $ret ) ) return $this->_core->_ml->get_site_name( $lang );
		return $ret;
	}

	public function get_site_description( $lang = false, $home = false ) {
		if ( $lang === false ) $lang = $this->_core->_ml->get_site_lang();
		if ( $home === false ) $home = $this->_core->get_site_home();

		$ret = get_option( "blogdescription_{$lang}_$home" );
		if ( empty( $ret ) ) return $this->_core->_ml->get_site_description( $lang );
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

}
