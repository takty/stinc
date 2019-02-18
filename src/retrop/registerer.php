<?php
namespace st;
use \st\retrop as R;

/**
 *
 * Retrop Registerer
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-18
 *
 */


require_once __DIR__ . '/util.php';
require_once __DIR__ . '/simple_html_dom.php';


class Registerer {

	const PMK_DIGEST      = '_digest';
	const PMK_IMPORT_FROM = '_import_from';

	private $_post_type;
	private $_type2structs;
	private $_required_cols;
	private $_digest_cols;
	private $_labels;
	private $_debug = '';

	public function __construct( $post_type, $structs, $labels = [], $target_url_base = '' ) {
		$this->_post_type       = $post_type;
		$this->_type2structs    = $this->extract_type_struct( $structs );
		$this->_required_cols   = $this->extract_columns( $structs, R\FS_REQUIRED );
		$this->_digest_cols     = $this->extract_columns( $structs, R\FS_FOR_DIGEST );
		$this->_labels          = $labels;
		$this->_target_url_base = $target_url_base;
	}

	private function extract_type_struct( $structs ) {
		$t2ss = [];
		foreach ( R\FS_TYPES as $t ) $t2ss[ $t ] = [];

		foreach ( $structs as $col => $s ) {
			if ( ! isset( $s[ R\FS_TYPE ] ) ) continue;
			$type = $s[ R\FS_TYPE ];
			$t2ss[ $type ][ $col ] = $s;
		}
		return $t2ss;
	}

	private function extract_columns( $structs, $skey ) {
		$cols = [];
		foreach ( $structs as $col => $s ) {
			if ( ! isset( $s[ $skey ] ) || $s[ $skey ] !== true ) continue;
			$cols[] = $col;
		}
		return $cols;
	}

	private function is_required_field_filled( $item ) {
		foreach ( $this->_required_cols as $col ) {
			if ( ! isset( $item[ $col ] ) ) return false;
			if ( is_array( $item[ $col ] ) ) {
				if ( empty( $item[ $col ] ) ) return false;
			} else {
				if ( empty( trim( $item[ $col ] ) ) ) return false;
			}
		}
		return true;
	}

	private function get_digested_text( $item ) {
		$text = '';
		foreach ( $this->_digest_cols as $col ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$text .= trim( $item[ $col ] );
		}
		return $text;
	}


	// -------------------------------------------------------------------------


	public function process_item( $item, $file_name, $is_term_inserted = false ) {
		if ( ! $this->is_required_field_filled( $item ) ) return false;
		$digested_text = $this->get_digested_text( $item );
		$digest = \st\retrop\make_digest( $digested_text );

		$olds = get_posts( [
			'post_type' => $this->_post_type,
			'meta_query' => [ [ 'key' => self::PMK_DIGEST, 'value' => $digest ] ],
		] );
		$old_id = empty( $olds ) ? false : $olds[0]->ID;

		$args = [
			'post_type'     => $this->_post_type,
			'post_title'    => $this->get_post_title( $item ),
			'post_content'  => $this->get_post_content( $item ),
			'post_date'     => $this->get_post_date( $item ),
			'post_date_gmt' => $this->get_post_date_gmt( $item ),
			'post_status'   => 'publish',
		];
		if ( $old_id !== false ) $args['ID'] = $old_id;
		$post_id = wp_insert_post( $args );
		if ( $post_id === 0 ) return false;

		update_post_meta( $post_id, self::PMK_IMPORT_FROM, $file_name );
		update_post_meta( $post_id, self::PMK_DIGEST,      $digest );

		$this->update_post_metas( $item, $post_id );
		$this->add_terms( $item, $post_id, $is_term_inserted );
		$msg = $this->update_post_thumbnail( $item, $post_id );

		$msg .= '<p>' . ( $old_id === false ? $this->_labels['new'] : $this->_labels['updated'] ) . ': ';
		$msg .= wp_kses_post( $digested_text ) . '</p>';
		$msg .= $this->_debug;
		return $msg;
	}

	private function filter( $val, $filter ) {
		if ( $filter ) {
			switch ( $filter ) {
			case R\FS_FILTER_CONTENT:
				$val = str_replace( '\n', PHP_EOL, $val );  // '\n' is '\' + 'n', but not \n.
				$val = wp_kses_post( $val );
				break;
			case R\FS_FILTER_CONTENT_MEDIA:
				$val = str_replace( '\n', PHP_EOL, $val );  // '\n' is '\' + 'n', but not \n.
				$val = wp_kses_post( $val );
				$val = $this->_filter_content_media( $val );
				break;
			case R\FS_FILTER_NORM_DATE:
				$val = str_replace( '\n', PHP_EOL, $val );  // '\n' is '\' + 'n', but not \n.
				$val = \st\field\normalize_date( $val );
				break;
			case R\FS_FILTER_NL2BR:
				// Do not add "\n" because WP recognizes "\n" as a paragraph separator.
				$val = str_replace( ['\n\n', '\n\n'], '\n&nbsp;\n', $val );
				$val = str_replace( '\n', '<br />', $val );
				break;
			default:
				$val = str_replace( '\n', PHP_EOL, $val );  // '\n' is '\' + 'n', but not \n.
				if ( is_callable( $filter ) ) $val = call_user_func( $filter, $val );
				break;
			}
		} else {
			$val = str_replace( '\n', PHP_EOL, $val );  // '\n' is '\' + 'n', but not \n.
		}
		return $val;
	}


	// ---- POST TITLE


	private function get_post_title( $item ) {
		$title = '';
		foreach ( $this->_type2structs[ R\FS_TYPE_TITLE ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$title .= $item[ $col ];
		}
		return $title;
	}


	// ---- POST CONTENT


	private function get_post_content( $item ) {
		$content = '';
		foreach ( $this->_type2structs[ R\FS_TYPE_CONTENT ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$val = trim( $item[ $col ] );
			if ( empty( $val ) ) continue;

			$filter = isset( $s[ R\FS_FILTER ] ) ? $s[ R\FS_FILTER ] : false;
			$content .= $this->filter( $val, $filter );
		}
		return $content;
	}


	// ---- POST META


	private function update_post_metas( $item, $post_id ) {
		foreach ( $this->_type2structs[ R\FS_TYPE_META ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$val = trim( $item[ $col ] );
			if ( empty( $val ) ) continue;

			if ( ! isset( $s[ R\FS_KEY ] ) ) continue;
			$key = $s[ R\FS_KEY ];
			$filter = isset( $s[ R\FS_FILTER ] ) ? $s[ R\FS_FILTER ] : false;
			update_post_meta( $post_id, $key, $this->filter( $val, $filter ) );
		}
		return true;
	}


	// ---- POST DATE & DATE GMT


	private function get_post_date( $item ) {
		$date = '';
		foreach ( $this->_type2structs[ R\FS_TYPE_DATE ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$date .= $item[ $col ];
		}
		return $date;
	}

	private function get_post_date_gmt( $item ) {
		$date = '';
		foreach ( $this->_type2structs[ R\FS_TYPE_DATE_GMT ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$date .= $item[ $col ];
		}
		return $date;
	}


	// ---- TERM


	private function add_terms( $item, $post_id, $is_term_inserted ) {
		foreach ( $this->_type2structs[ R\FS_TYPE_TERM ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;

			if ( ! isset( $s[ R\FS_TAXONOMY ] ) ) continue;
			$tax = $s[ R\FS_TAXONOMY ];

			$vals = $item[ $col ];
			if ( ! is_array( $vals ) ) $vals = [ $vals ];

			if ( isset( $s[ R\FS_CONV ] ) ) $vals = $this->apply_conv_table( $vals, $s[ R\FS_CONV ] );
			if ( isset( $s[ R\FS_NORM_SLUG ] ) && $s[ R\FS_NORM_SLUG ] === true ) {
				$vals = $this->normalize_slugs( $vals );
			}
			$this->add_term( $post_id, $vals, $tax, $is_term_inserted );
		}
		return true;
	}

	private function apply_conv_table( $vals, $conv_table ) {
		$ret = [];
		foreach ( $vals as $val ) {
			if ( isset( $conv_table[ $val ] ) ) {
				$ret[] = $conv_table[ $val ];
			} else {
				$ret[] = $val;
			}
		}
		return $ret;
	}

	private function normalize_slugs( $vals ) {
		$ret = [];
		foreach ( $vals as $val ) {
			$ret[] = str_replace( '_', '-', $val );
		}
		return $ret;
	}

	private function add_term( $post_id, $vals, $tax, $is_term_inserted ) {
		$ts = get_terms( $tax, [ 'hide_empty' => false, 'fields' => 'id=>slug' ] );
		if ( is_wp_error( $ts ) ) return;
		$ts = array_values( $ts );

		$slugs = array_filter( $vals, function ( $v ) use ( $ts ) {
			return in_array( $v, $ts, true );
		} );
		if ( $is_term_inserted && count( $slugs ) !== count( $vals ) ) {
			$ue_slugs = $this->ensure_term_existing( $vals );
			$slugs += $ue_slugs;
		}
		if ( ! empty( $slugs ) ) wp_set_object_terms( $post_id, $slugs, $tax );  // Replace existing terms
	}

	private function ensure_term_existing( $vals ) {
		$ue_slugs = array_filter( $vals, function ( $v ) use ( $ts ) {
			return ! in_array( $v, $ts, true );
		} );
		if ( ! empty( $ue_slugs ) ) {
			$ue_slugs = array_values( array_unique( $ue_slugs ) );
			foreach ( $ue_slugs as $slug ) {
				$ret = wp_insert_term( $slug, $tax, [ 'slug' => $slug ] );
			}
		}
		return $us_slugs;
	}


	// ---- THUMBNAIL


	private function update_post_thumbnail( $item, $post_id ) {
		$msg = '';
		foreach ( $this->_type2structs[ R\FS_TYPE_THUMBNAIL_URL ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$url = $item[ $col ];

			if ( has_post_thumbnail( $post_id ) ) {
				$id = get_post_thumbnail_id( $post_id );
				$ais = wp_get_attachment_image_src( $id, 'full' );
				if ( $ais !== false && basename( $url ) === basename( $ais[0] ) ) {
					$msg .= '<p>The thumbnail image might be the same.</p>';
					continue;
				}
			}
			$msg .= '<p>Try to download the thumbnail image: ' . esc_html( $url ) . '</p>';
			$aid = $this->insert_attachment_from_url( $url, $post_id );
			set_post_thumbnail( $post_id, $aid );
		}
		return $msg;
	}

	private function insert_attachment_from_url( $url, $post_id = 0, $timeout = 30 ) {
		$temp = download_url( $url, $timeout );
		if ( is_wp_error( $temp ) ) return $temp;

		$file = [ 'name' => basename( $url ), 'tmp_name' => $temp ];
		$attachment_id = media_handle_sideload( $file, $post_id );

		if ( is_wp_error( $attachment_id ) ) @unlink( $temp );
		return $attachment_id;
	}


	// ---- CONTENT MEDIA


	private function _filter_content_media( $val ) {
		$dom = str_get_html( $val );
		foreach ( $dom->find( 'img' ) as &$elm ) {
			$p = strpos( $elm->src, $this->_target_base_url );
			if ( $p !== false ) $elm->src = $this->_convert_url( $elm->src );
		}
		foreach ( $dom->find( 'a' ) as &$elm ) {
			$p = strpos( $elm->href, $this->_target_base_url );
			if ( $p !== false ) $elm->href = $this->_convert_url( $elm->href );
		}
		$val = $dom->save();
		$dom->clear();
		unset($dom);
		return $val;
	}

	private function _convert_url( $url ) {
		// get full size image url
		// wp-image-****
		// size-****
		// How to avoid duplication
		return $url;
	}

}
