<?php
namespace st;
use \st\retrop as R;

/**
 *
 * Retrop Registerer
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-28
 *
 */


require_once __DIR__ . '/util.php';


class Registerer {

	const PMK_DIGEST      = '_digest';
	const PMK_IMPORT_FROM = '_import_from';

	private $_post_type;
	private $_type2structs;
	private $_required_cols;
	private $_digest_cols;
	private $_labels;

	public function __construct( $post_type, $structs, $labels = [] ) {
		$this->_post_type     = $post_type;
		$this->_type2structs  = $this->extract_type_struct( $structs );
		$this->_required_cols = $this->extract_columns( $structs, R\FS_REQUIRED );
		$this->_digest_cols   = $this->extract_columns( $structs, R\FS_FOR_DIGEST );
		$this->_labels        = $labels;
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


	public function add_unexisting_terms( $items ) {
		$tax_to_terms = [];
		foreach ( $items as $item ) $this->collect_unexisting_terms( $item, $tax_to_terms );
		$this->insert_terms( $tax_to_terms );
	}

	private function collect_unexisting_terms( $item, &$tax_to_terms ) {
		foreach ( $this->_type2structs[ R\FS_TYPE_TERM ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;

			if ( ! isset( $s[ R\FS_AUTO_ADD ] ) || $s[ R\FS_AUTO_ADD ] === false ) continue;
			if ( ! isset( $s[ R\FS_TAXONOMY ] ) ) continue;
			$tax = $s[ R\FS_TAXONOMY ];

			$vals = $item[ $col ];
			if ( ! is_array( $vals ) ) $vals = [ $vals ];

			$ts = get_terms( $tax, [ 'hide_empty' => false, 'fields' => 'id=>slug' ] );
			if ( is_wp_error( $ts ) ) return;
			$ts = array_values( $ts );

			$slugs = array_filter( $vals, function ( $v ) use ( $ts ) {
				return ! in_array( $v, $ts, true );
			} );
			if ( empty( $slugs ) ) continue;

			$tts = isset( $tax_to_terms[ $tax ] ) ? $tax_to_terms[ $tax ] : [];
			foreach ( $slugs as $slug ) $tts[] = $slug;
			$tax_to_terms[ $tax ] = $tts;
		}
	}

	private function insert_terms( $tax_to_terms ) {
		foreach ( $tax_to_terms as $tax => $terms ) {
			$terms = array_values( array_unique( $terms ) );
			foreach ( $terms as $t ) {
				$ret = wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
		}
	}


	// -------------------------------------------------------------------------


	public function process_item( $item, $file_name ) {
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
		$this->add_terms( $item, $post_id );
		$msg = $this->update_post_thumbnail( $item, $post_id );

		$msg .= '<p>' . ( $old_id === false ? $this->_labels['new'] : $this->_labels['updated'] ) . ': ';
		$msg .= wp_kses_post( $digested_text ) . '</p>';
		return $msg;
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
			$c = $item[ $col ];
			$content .= str_replace( '\\n', PHP_EOL, $c );
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

			$this->update_post_meta( $post_id, $key, $val, $filter );
		}
		return true;
	}

	private function update_post_meta( $post_id, $key, $val, $filter ) {
		$val = str_replace( '\\n', PHP_EOL, $val );

		if ( $filter ) {
			switch ( $filter ) {
			case R\FS_FILTER_CONTENT:
				$val = wp_kses_post( $val );
				break;
			case R\FS_FILTER_NORM_DATE:
				$val = \st\field\normalize_date( $val );
				break;
			default:
				if ( is_callable( $filter ) ) $val = call_user_func( $filter, $val );
				break;
			}
		}
		update_post_meta( $post_id, $key, $val );
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


	private function add_terms( $item, $post_id ) {
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
			$this->add_term( $post_id, $vals, $tax );
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

	private function add_term( $post_id, $vals, $tax ) {
		$ts = get_terms( $tax, [ 'hide_empty' => false, 'fields' => 'id=>slug' ] );
		if ( is_wp_error( $ts ) ) return;
		$ts = array_values( $ts );

		$slugs = array_filter( $vals, function ( $v ) use ( $ts ) {
			return in_array( $v, $ts, true );
		} );
		if ( ! empty( $slugs ) ) wp_set_object_terms( $post_id, $slugs, $tax );  // Replace existing terms
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

}
