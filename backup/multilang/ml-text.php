<?php
namespace st;

/**
 *
 * Multi-Language Site with Single Site (Text)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-05-22
 *
 */


class Multilang_Text {

	private $_core;
	private $_text_to_lang = [];
	private $_text_to_context_to_lang = [];

	private $_date_format_dic = [];
	private $_is_date_format_added = false;

	public function __construct( $core ) {
		$this->_core = $core;
		add_filter( 'gettext', [ $this, '_cb_gettext' ], 10, 3 );
		add_filter( 'gettext_with_context', [ $this, '_cb_gettext_with_context' ], 10, 4 );
	}

	public function add_text_translation( $text, $lang, $trans ) {
		if ( ! isset( $this->_text_to_lang[ $text ] ) ) $this->_text_to_lang[ $text ] = [];
		$this->_text_to_lang[ $text ][ $lang ] = $trans;
	}

	public function add_text_translation_with_context( $text, $context, $lang, $trans ) {
		if ( ! isset( $this->_text_to_context_to_lang[ $text ] ) ) $this->_text_to_context_to_lang[ $text ] = [];
		if ( ! isset( $this->_text_to_context_to_lang[ $text ][ $context ] ) ) $this->_text_to_context_to_lang[ $text ][ $context ] = [];
		$this->_text_to_context_to_lang[ $text ][ $context ][ $lang ] = $trans;
	}

	public function add_date_format_translation( $lang_s, $year = 'Y', $month = 'Y-m', $day = 'Y-m-d' ) {
		if ( ! is_array( $lang_s )) $lang_s = [ $lang_s ];
		foreach ( $lang_s as $lang ) {
			if ( ! isset( $this->_date_format_dic[ $lang ] ) ) $this->_date_format_dic[ $lang ] = [];
			$lang_dic = &$this->_date_format_dic[ $lang ];
			$lang_dic = [ $year, $month, $day ];
		}
		$this->_is_date_format_added = true;
	}

	public function translate_text( $text, $lang = false ) {
		if ( isset( $this->_text_to_lang[ $text ] ) ) {
			$dict = &$this->_text_to_lang[ $text ];
			if ( $lang === false ) $lang = $this->_core->get_site_lang();
			if ( isset( $dict[ $lang ] ) ) return $dict[ $lang ];
		}
		return $text;
	}

	public function translate_text_with_context( $text, $context, $lang = false ) {
		if ( isset( $this->_text_to_context_to_lang[ $text ] ) ) {
			if ( isset( $this->_text_to_context_to_lang[ $text ][ $context ] ) ) {
				$dict = &$this->_text_to_context_to_lang[ $text ][ $context ];
				if ( $lang === false ) $lang = $this->_core->get_site_lang();
				if ( isset( $dict[ $lang ] ) ) return $dict[ $lang ];
			}
		}
		return $text;
	}

	public function is_date_format_added() {
		return $this->_is_date_format_added;
	}

	public function has_date_format( $lang ) {
		return isset( $this->_date_format_dic[ $lang ] );
	}

	public function get_date_format( $type = 'day', $lang = false ) {
		if ( $lang === false ) $lang = $this->_core->get_site_lang();
		$def  = $this->_core->get_default_site_lang();

		$ret = $this->_get_date_format_lang( $type, $lang );
		if ( $ret === false && $lang !== $def ) {
			$ret = $this->_get_date_format_lang( $type, $def );
		}
		return $ret;
	}

	public function format_date( $date ) {
		$date = date_create_from_format( 'Y-m-d', $date );
		if ( $date === false ) return '';
		return date_format( $date, $this->get_date_format() );
	}


	// Callback Functions ------------------------------------------------------

	public function _cb_gettext( $translation, $text, $domain ) {
		if ( isset( $this->_text_to_lang[ $text ] ) ) {
			$dict = &$this->_text_to_lang[ $text ];
			$lang = $this->_get_lang();
			if ( isset( $dict[ $lang ] ) ) return $dict[ $lang ];
			return $text;
		}
		return $translation;
	}

	public function _cb_gettext_with_context( $translation, $text, $context, $domain ) {
		if ( isset( $this->_text_to_context_to_lang[ $text ] ) ) {
			if ( isset( $this->_text_to_context_to_lang[ $text ][ $context ] ) ) {
				$dict = &$this->_text_to_context_to_lang[ $text ][ $context ];
				$lang = $this->_get_lang();
				if ( isset( $dict[ $lang ] ) ) return $dict[ $lang ];
				return $text;
			}
		}
		return $translation;
	}


	// Private Functions -------------------------------------------------------

	private function _get_lang() {
		if ( is_admin() ) {
			$lang_c = explode( '_', get_user_locale() );
			$lang = $lang_c[0];
		} else {
			$lang = $this->_core->get_site_lang();
		}
		return $lang;
	}

	private function _get_date_format_lang( $type, $lang ) {
		if ( ! isset( $this->_date_format_dic[ $lang ] ) ) return false;
		$dic = $this->_date_format_dic[ $lang ];
		if ( $type === 'year' )  return $dic[0];
		if ( $type === 'month' ) return $dic[1];
		if ( $type === 'day' )   return $dic[2];
		return false;
	}

}
