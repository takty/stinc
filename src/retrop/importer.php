<?php
namespace st;
use \st\retrop as R;

/**
 *
 * Retrop Importer: Versatile XLSX Importer
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-25
 *
 */


if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) return;

require_once ABSPATH . 'wp-admin/includes/import.php';
if ( ! class_exists( '\WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
}
if ( ! class_exists( '\WP_Importer' ) ) return;
require_once __DIR__ . '/util.php';


class Retrop_Importer extends \WP_Importer {

	const PMK_DIGEST      = '_digest';
	const PMK_IMPORT_FROM = '_import_from';

	static public function register( $args = [] ) {
		$inst = new Retrop_Importer( $args );
		$GLOBALS['retrop_import'] = $inst;
		$name = 'Retrop Importer';
		$description = 'Import data from a Excel (XLSX) file.';
		if ( isset( $args[ 'labels' ] ) ) {
			$labels = $args[ 'labels' ];
			if ( isset( $labels['name'] ) ) $name = $labels['name'];
			if ( isset( $labels['description'] ) ) $description = $labels['description'];
		}
		register_importer( 'retrop', $name, $description, [ $GLOBALS['retrop_import'], 'dispatch' ] );
	}

	private $_post_type;
	private $_structs;
	private $_url_to;

	private $_type2structs;
	private $_required_cols;
	private $_digest_cols;
	private $_labels;

	private $_id;
	private $_file_name;
	private $_is_auto_add_terms_selectable = false;
	private $_add_terms = false;
	private $_items = [];

	public function __construct( $args ) {
		$this->_post_type = $args['post_type'];
		$this->_structs   = $args['structs'];
		$this->_url_to    = $args['url_to'];
		if ( isset( $args['is_auto_add_terms_selectable'] ) ) $this->_is_auto_add_terms_selectable = $args['is_auto_add_terms_selectable'];

		$this->_type2structs = $this->extract_type_struct( $args['structs'] );
		$this->_required_cols = $this->extract_columns( R\FS_REQUIRED );
		$this->_digest_cols = $this->extract_columns( R\FS_FOR_DIGEST );

		$this->_labels = [
			'name'              => 'Retrop Importer',
			'description'       => 'Import data from a Excel (.xlsx) file.',
			'message'           => 'Choose a Excel (.xlsx) file to upload, then click Upload file and import.',
			'error'             => 'Sorry, there has been an error.',
			'error_wrong_file'  => 'The file is wrong, please try again.',
			'all_done'          => 'All done.',
			'add_terms'         => 'Add Terms',
			'add_terms_message' => 'Add terms that import file contains',
			'updated'           => 'Updated',
			'new'               => 'New',
		];
		if ( isset( $args[ 'labels' ] ) ) $this->_labels = array_merge( $this->_labels, $args['labels'] );
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

	private function extract_columns( $skey ) {
		$cols = [];
		foreach ( $this->_structs as $col => $s ) {
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


	public function dispatch() {
		wp_enqueue_script( 'retrop-loader', $this->_url_to . '/loader.min.js' );
		wp_enqueue_script( 'xlsx', $this->_url_to . '/xlsx.full.min.js' );

		$this->_header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
				$this->_greet();
				break;
			case 1:
				check_admin_referer( 'import-upload' );
				if ( $this->_handle_upload() ) $this->_parse_upload();
				break;
			case 2:
				check_admin_referer( 'import-retrop' );
				$this->_id        = (int) $_POST['file_id'];
				$this->_file_name = pathinfo( get_attached_file( $this->_id ), PATHINFO_FILENAME );
				$this->_add_terms = $this->_is_auto_add_terms_selectable && ( ! empty( $_POST['add_terms'] ) && $_POST['add_terms'] === 'term' );
				set_time_limit(0);
				$this->_import( stripslashes( $_POST['retrop_items'] ) );
				break;
		}

		$this->_footer();
	}

	private function _header() {
		echo '<div class="wrap">';
		echo '<h2>' . $this->_labels['name'] . '</h2>';
	}

	private function _footer() {
		echo '</div>';
	}


	// Step 0 ------------------------------------------------------------------


	private function _greet() {
		echo '<div class="narrow">';
		echo '<p>' . $this->_labels['description'] . '</p>';
		echo '<p>' . $this->_labels['message'] . '</p>';
		wp_import_upload_form( 'admin.php?import=retrop&amp;step=1' );
		echo '</div>';
	}


	// Step 1 ------------------------------------------------------------------


	private function _handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . $this->_labels['error'] . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . $this->_labels['error'] . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		}
		$this->_id = (int) $file['id'];
		return true;
	}

	private function _parse_upload() {
		$url = wp_get_attachment_url( $this->_id );
		$json_structs = json_encode( $this->_structs );
?>
<form action="<?php echo admin_url( 'admin.php?import=retrop&amp;step=2' ); ?>" method="post" name="form">
	<?php wp_nonce_field( 'import-retrop' ); ?>
	<input type="hidden" name="file_id" value="<?php echo $this->_id; ?>" />
	<input type="hidden" name="retrop_items" id="retrop-items" value="" />
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			RETROP.loadFiles('<?php echo addslashes($json_structs) ?>', ['<?php echo $url ?>'], '#retrop-items', function (successAll) {
				if (successAll) document.form.submit.disabled = false;
				else document.getElementById('error').style.display = 'block';
			});
		});
	</script>
<?php if ( $this->_is_auto_add_terms_selectable ) : ?>
	<h3><?php echo $this->_labels['add_terms'] ?></h3>
	<p>
		<input type="radio" value="term" name="add_terms" id="add-terms" />
		<label for="add-terms"><?php echo $this->_labels['add_terms_message'] ?></label>
	</p>
<?php endif; ?>
	<p class="submit"><input type="submit" name="submit" disabled class="button button-primary" value="<?php esc_attr_e( 'Submit' ); ?>" /></p>
</form>
<?php
		echo '<p id="error" style="display: none;"><strong>' . $this->_labels['error'] . '</strong><br />';
	}


	// Step 2 ------------------------------------------------------------------


	private function _import( $json ) {
		add_filter( 'http_request_timeout', function ( $val ) { return 60; } );

		$this->_import_start( $json );
		wp_suspend_cache_invalidation( true );

		$tax_to_terms = [];
		foreach ( $this->_items as $item ) $this->collect_unexisting_terms( $item, $tax_to_terms );
		$this->add_unexisting_terms( $tax_to_terms );
		$count = 0;
		foreach ( $this->_items as $item ) {
			if ( $this->process_item( $item ) ) $count += 1;
		}
		wp_suspend_cache_invalidation( false );
		$this->_import_end( $count, count( $this->_items ) );
	}

	private function _import_start( $json ) {
		$data = json_decode( $json, true );
		if ( $data === null ) {
			echo '<p><strong>' . $this->_labels['error'] . '</strong><br />';
			echo $this->_labels['error_wrong_file'] . '</p>';
			$this->_footer();
			die();
		}
		$this->_items = $data;

		do_action( 'import_start' );
	}

	private function _import_end( $added_count, $all_count ) {
		wp_import_cleanup( $this->_id );
		wp_cache_flush();

		echo '<p>' . $this->_labels['all_done'] . ' (' . $added_count . '/' . $all_count . ')</p>';

		do_action( 'import_end' );
	}


	// -------------------------------------------------------------------------


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

	private function add_unexisting_terms( $tax_to_terms ) {
		foreach ( $tax_to_terms as $tax => $terms ) {
			$terms = array_values( array_unique( $terms ) );
			foreach ( $terms as $t ) {
				$ret = wp_insert_term( $t, $tax, [ 'slug' => $t ] );
			}
		}
	}


	// -------------------------------------------------------------------------


	private function process_item( $item ) {
		if ( ! $this->is_required_field_filled( $item ) ) return false;
		$digested_text = $this->get_digested_text( $item );
		$digest = \st\retrop\make_digest( $digested_text );

		$olds = get_posts( [
			'post_type' => $this->_post_type,
			'meta_query' => [ [ 'key' => self::PMK_DIGEST, 'value' => $digest ] ],
		] );
		$old_id = empty( $olds ) ? false : $olds[0]->ID;

		$args = [
			'post_type'    => $this->_post_type,
			'post_title'   => $this->get_post_title( $item ),
			'post_content' => $this->get_post_content( $item ),
			'post_status'  => 'publish',
		];
		if ( $old_id !== false ) $args['ID'] = $old_id;
		$post_id = wp_insert_post( $args );
		if ( $post_id === 0 ) return false;

		update_post_meta( $post_id, self::PMK_IMPORT_FROM, $this->_file_name );
		update_post_meta( $post_id, self::PMK_DIGEST,      $digest );

		$this->update_post_metas( $item, $post_id );
		$this->add_terms( $item, $post_id );
		$this->update_post_thumbnail( $item, $post_id );

		echo '<p>' . ( $old_id === false ? $this->_labels['new'] : $this->_labels['updated'] ) . ': ';
		echo wp_kses_post( $digested_text ) . '</p>';
		return true;
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
		foreach ( $this->_type2structs[ R\FS_TYPE_THUMBNAIL_URL ] as $col => $s ) {
			if ( ! isset( $item[ $col ] ) ) continue;
			$url = $item[ $col ];

			if ( has_post_thumbnail( $post_id ) ) {
				$id = get_post_thumbnail_id( $post_id );
				$ais = wp_get_attachment_image_src( $id, 'full' );
				if ( $ais !== false && basename( $url ) === basename( $ais[0] ) ) {
					echo '<p>The thumbnail image might be the same.</p>';
					continue;
				}
			}
			echo '<p>Try to download the thumbnail image: ' . esc_html( $url ) . '</p>';
			$aid = $this->insert_attachment_from_url( $url, $post_id );
		}
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
