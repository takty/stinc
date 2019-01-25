<?php
namespace st;

/**
 *
 * Retrop Exporter: Versatile XLSX Exporter
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-25
 *
 */


class Retrop_Exporter {

	static private $_instance = [];

	static public function register( $args = [] ) {
		self::$_instance[] = new Retrop_Exporter( $args );
	}

	private $_post_type;
	private $_structs;
	private $_url_to;

	private $_labels;

	private function __construct( $args ) {
		$this->_post_type = $args['post_type'];
		$this->_structs   = $args['structs'];
		$this->_url_to    = $args['url_to'];

		$this->_labels = [
			'name'        => 'Retrop Exporter',
			'description' => 'Export data to a Excel (.xlsx) file.',
		];
		if ( isset( $args[ 'labels' ] ) ) $this->_labels = array_merge( $this->_labels, $args['labels'] );

		add_action( 'admin_menu', [ $this, '_cb_admin_menu' ] );
	}

	public function _cb_admin_menu() {
		$label = $this->_labels['name'];
		add_submenu_page( 'tools.php', $label, $label, 'level_7', 'retrop_export', [ $this, '_cb_output_page' ] );
	}

	private function _header() {
		echo '<div class="wrap">';
		echo '<h2>' . $this->_labels['name'] . '</h2>';
	}

	private function _footer() {
		echo '</div>';
	}

	public function _cb_output_page() {
		wp_enqueue_script( 'retrop-saver', $this->_url_to . '/saver.min.js' );
		wp_enqueue_script( 'xlsx', $this->_url_to . '/xlsx.full.min.js' );

		$this->_header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
				$this->_output_option_page();
				break;
			case 1:
				check_admin_referer( 'export-option' );
				$this->_output_download_page();
				break;
		}

		$this->_footer();
	}

	private function _output_option_page() {
		echo '<div class="narrow">';
		echo '<p>' . $this->_labels['description'] . '</p>';
?>
<form method="post" action="<?php echo esc_url( wp_nonce_url( 'tools.php?page=retrop_export&amp;step=1', 'export-option' ) ); ?>">
<p>
<!-- <label for="upload"><?php //_e( 'Choose a file from your computer:' ); ?></label> (<?php //printf( __('Maximum size: %s' ), $size ); ?>) -->
<!-- <input type="file" id="upload" name="import" size="25" /> -->
<!-- <input type="hidden" name="action" value="save" /> -->
<!-- <input type="hidden" name="max_file_size" value="<?php //echo $bytes; ?>" /> -->
</p>
<?php submit_button( __('Submit'), 'primary' ); ?>
</form>
<?php
		echo '</div>';

	}

	private function _output_download_page() {
		$json_structs = json_encode( array_keys( $this->_structs ) );

		echo '<div class="narrow">';
		echo '<p>' . $this->_labels['description'] . '</p>';

		$this->_output_data();

?>
		<p class="submit"><input type="submit" name="download" id="download" class="button button-primary" value="<?php _e('Download Export File') ?>"></p>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const download = document.getElementsByName('download')[0];
				download.addEventListener('click', (e) => {
					RETROP.saveFile('<?php echo addslashes($json_structs) ?>', 'export.xlsx', '#retrop-chunk-', function (success) {
						// if (success) document.form.submit.disabled = false;
						// else document.getElementById('error').style.display = 'block';
					});
				});
			});
		</script>
<?php
		echo '</div>';
	}

	private function _output_data() {
		$ps = get_posts( [
			'post_type' => $this->_post_type,
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'asc'
		] );
		$pss = array_chunk( $ps, 20 );
		foreach ( $pss as $idx => $ps ) {
			$as = [];
			foreach ( $ps as $p ) {
				$as[] = $this->_make_record_array( $p );
			}
			$js = json_encode( $as, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			$js = mb_ereg_replace( '&#x000d;', '', $js );
?>
			<input type="hidden" id="retrop-chunk-<?php echo $idx ?>" value="<?php echo esc_attr( $js ) ?>" />
<?php
		}
	}

	private function _make_record_array( $p ) {
		$ret = [];
		foreach ( $this->_structs as $key => $s ) {
			$type = $s['type'];
			switch ( $type ) {
			case \st\retrop\FS_TYPE_TITLE:
				$val = $p->post_title;
				break;
			case \st\retrop\FS_TYPE_CONTENT:
				$val = $p->post_content;
				break;
			case \st\retrop\FS_TYPE_META:
				$key = $s[\st\retrop\FS_KEY];
				$val = get_post_meta( $p->ID, $key, true );
				if ( isset( $s[\st\retrop\FS_FILTER] ) && $s[\st\retrop\FS_FILTER] === \st\retrop\FS_FILTER_ADD_BR ) {
					$val = str_replace( ["\r\n", "\r", "\n"], '<br />\n', $val );
				}
				break;
			case \st\retrop\FS_TYPE_TERM:
				$tax = $s[\st\retrop\FS_TAXONOMY];
				$ts = get_the_terms( $p->ID, $tax );
				if ( is_array( $ts ) ) {
					$slugs = [];
					foreach ( $ts as $t ) {
						$slugs[] = $t->slug;
					}
					$val = implode( ', ', $slugs );
				}
				break;
			case \st\retrop\FS_TYPE_THUMBNAIL_URL:
				if ( ! has_post_thumbnail( $p->ID ) ) break;
				$id = get_post_thumbnail_id( $p->ID );
				$ais = wp_get_attachment_image_src( $id, 'full' );
				if ( $ais !== false ) $val = $ais[0];
				break;
			case \st\retrop\FS_TYPE_ACF_PM:
				if ( function_exists( 'get_field' ) ) {
					$key = $s[\st\retrop\FS_KEY];
					$val = get_field( $key, $p->ID );
					if ( isset( $s[\st\retrop\FS_FILTER] ) && $s[\st\retrop\FS_FILTER] === \st\retrop\FS_FILTER_ADD_BR ) {
						$val = str_replace( ["\r\n", "\r", "\n"], '<br />\n', $val );
					}
				}
				break;
			}
			$val = str_replace( ["\r\n", "\r", "\n"], '\\n', $val );
			$ret[] = $val;
		}
		return $ret;
	}

}

