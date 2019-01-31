<?php
namespace st;
use \st\retrop as R;

/**
 *
 * Retrop Importer: Versatile XLSX Importer
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-31
 *
 */


require_once ABSPATH . 'wp-admin/includes/import.php';
if ( ! class_exists( '\WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) require $class_wp_importer;
}
if ( ! class_exists( '\WP_Importer' ) ) return;

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/registerer.php';
require_once __DIR__ . '/../system/ajax.php';


class Retrop_Importer extends \WP_Importer {

	static private $_instance = [];

	static public function register( $id, $args = [] ) {
		self::$_instance[] = new Retrop_Importer( $id, $args );
	}

	private $_id;
	private $_json_structs;
	private $_url_to;
	private $_labels;

	private $_can_auto_add_terms = false;
	private $_is_ajax            = false;
	private $_registerer;

	private $_file_id;
	private $_file_name;
	private $_auto_add_terms = false;
	private $_ajax_request_url;

	private function __construct( $id, $args ) {
		$this->_id           = 'retrop_import_' . $id;
		$this->_json_structs = json_encode( $args['structs'] );
		$this->_url_to       = $args['url_to'];

		if ( isset( $args['can_auto_add_terms'] ) ) $this->_can_auto_add_terms = $args['can_auto_add_terms'];
		if ( isset( $args['ajax'] ) )               $this->_is_ajax            = $args['ajax'];

		$this->_labels = [
			'name'              => 'Retrop Importer',
			'description'       => 'Import data from a Excel (.xlsx) file.',
			'message'           => 'Choose a Excel (.xlsx) file to upload, then click Upload file and import.',
			'error'             => 'Sorry, there has been an error.',
			'success'           => 'Successfully finished.',
			'error_wrong_file'  => 'The file is wrong, please try again.',
			'all_done'          => 'All done.',
			'add_terms'         => 'Add Terms',
			'add_terms_message' => 'Add terms that import file contains',
			'updated'           => 'Updated',
			'new'               => 'New',
		];
		if ( isset( $args[ 'labels' ] ) ) $this->_labels = array_merge( $this->_labels, $args['labels'] );

		$this->initialize();
		$this->_registerer = new Registerer( $args['post_type'], $args['structs'], $this->_labels );
		if ( $this->_is_ajax ) $this->_ajax_request_url = $this->initialize_ajax();
	}

	private function initialize() {
		$GLOBALS[ $this->_id ] = $this;
		register_importer(
			$this->_id,
			$this->_labels['name'],
			$this->_labels['description'],
			[ $GLOBALS[ $this->_id ], 'dispatch' ]
		);
	}

	private function initialize_ajax() {
		$ajax = new \st\Ajax( $this->_id, function () {
			$cont = file_get_contents( 'php://input' );
			if ( $cont === 'finished' ) {
				$this->_import_ajax_actually_end();
			} else {
				$item = json_decode( $cont, true );
				$msg = $this->_registerer->process_item( $item, $this->_file_name );
				return [ 'msg' => $msg ];
			}
		}, false );
		return $ajax->get_url();
	}


	// -------------------------------------------------------------------------


	public function dispatch() {
		wp_enqueue_script( 'retrop-importer', $this->_url_to . '/importer.min.js' );
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
				$this->_file_id        = (int) $_POST['retrop_file_id'];
				$this->_file_name = pathinfo( get_attached_file( $this->_file_id ), PATHINFO_FILENAME );
				$this->_auto_add_terms = $this->_can_auto_add_terms && ( ! empty( $_POST['add_terms'] ) && $_POST['add_terms'] === '1' );
				set_time_limit(0);
				if ( $this->_is_ajax ) {
					$this->_import_ajax( stripslashes( $_POST['retrop_items'] ) );
				} else {
					$this->_import( stripslashes( $_POST['retrop_items'] ) );
				}
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
		wp_import_upload_form( 'admin.php?import=' . $this->_id . '&amp;step=1' );
		echo '</div>';
	}


	// Step 1 ------------------------------------------------------------------


	private function _handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) || ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . $this->_labels['error'] . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		}
		$this->_file_id = (int) $file['id'];
		return true;
	}

	private function _parse_upload() {
		$_jstr = esc_attr( $this->_json_structs );
		$_url  = esc_attr( wp_get_attachment_url( $this->_file_id ) );
		$_act  = esc_attr( admin_url( 'admin.php?import=' . $this->_id . '&amp;step=2' ) );
?>
		<form action="<?php echo $_act ?>" method="post" name="form">
			<?php wp_nonce_field( 'import-retrop' ); ?>
			<input type="hidden" id="retrop-load-files" />
			<input type="hidden" id="retrop-structs" value="<?php echo $_jstr ?>" />
			<input type="hidden" id="retrop-url" value="<?php echo $_url ?>" />
			<input type="hidden" name="retrop_items" id="retrop-items" />
			<input type="hidden" name="retrop_file_id" value="<?php echo $this->_file_id; ?>" />

<?php if ( $this->_can_auto_add_terms ) : ?>
			<h3><?php echo $this->_labels['add_terms'] ?></h3>
			<p>
				<input type="radio" value="1" name="add_terms" id="add-terms" />
				<label for="add-terms"><?php echo $this->_labels['add_terms_message'] ?></label>
			</p>
<?php endif; ?>

			<p class="submit"><input type="submit" name="submit" disabled class="button button-primary" value="<?php esc_attr_e( 'Submit' ); ?>" /></p>
		</form>
		<p id="error" style="display: none;"><strong><?php echo $this->_labels['error'] ?></strong></p>
<?php
	}


	// Step 2 ------------------------------------------------------------------


	private function _import( $json ) {
		add_filter( 'http_request_timeout', function ( $val ) { return 60; } );
		$items = $this->_import_start( $json );
		wp_suspend_cache_invalidation( true );
		if ( $this->_auto_add_terms ) $this->_registerer->add_unexisting_terms( $items );

		$count = 0;
		foreach ( $items as $item ) {
			$msg = $this->_registerer->process_item( $item, $this->_file_name );
			if ( $msg !== false ) {
				echo $msg;
				$count += 1;
			}
		}
		wp_suspend_cache_invalidation( false );
		$this->_import_end( $count, count( $items ) );
	}

	private function _import_start( $json ) {
		$data = json_decode( $json, true );
		if ( $data === null ) {
			echo '<p><strong>' . $this->_labels['error'] . '</strong><br />';
			echo $this->_labels['error_wrong_file'] . '</p>';
			$this->_footer();
			die();
		}
		do_action( 'import_start' );
		return $data;
	}

	private function _import_end( $added_count, $all_count ) {
		wp_import_cleanup( $this->_file_id );
		wp_cache_flush();
		echo '<p>' . $this->_labels['all_done'] . ' (' . $added_count . '/' . $all_count . ')</p>';
		do_action( 'import_end' );
	}


	// Step 2 (Ajax) -----------------------------------------------------------


	private function _import_ajax( $json ) {
		add_filter( 'http_request_timeout', function ( $val ) { return 60; } );
		$items = $this->_import_start( $json );
		wp_suspend_cache_invalidation( true );
		if ( $this->_auto_add_terms ) $this->_registerer->add_unexisting_terms( $items );

		$count = 0;
		foreach ( $items as $idx => $item ) {
			$ij = json_encode( $item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			echo '<input type="hidden" id="retrop-item-' . $idx . '" value="' . esc_attr( $ij ) . '" />';
		}
?>
	<input type="hidden" id="ajax-request-url" value="<?php echo esc_attr( $this->_ajax_request_url ) ?>" />
	<p class="submit"><input type="submit" name="submit-ajax" class="button button-primary" value="<?php esc_attr_e( 'Submit' ); ?>" /></p>
	<div id="response-msgs"></div>
	<p id="error" style="display: none;"><strong><?php echo $this->_labels['error'] ?></strong></p>
	<p id="success" style="display: none;"><strong><?php echo $this->_labels['success'] ?></strong></p>
<?php
		wp_suspend_cache_invalidation( false );
		$this->_import_ajax_end( $count, count( $items ) );
	}

	private function _import_ajax_end( $added_count, $all_count ) {
		wp_import_cleanup( $this->_file_id );
		wp_cache_flush();
	}

	private function _import_ajax_actually_end() {
		do_action( 'import_end' );
	}

}
