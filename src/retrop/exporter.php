<?php
namespace st;
use \st\retrop as R;
/**
 *
 * Retrop Exporter: Versatile XLSX Exporter
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-18
 *
 */


require_once __DIR__ . '/simple_html_dom.php';


class Retrop_Exporter {

	static private $_instance = [];

	static public function register( $id, $args = [] ) {
		self::$_instance[] = new Retrop_Exporter( $id, $args );
	}

	private $_id;
	private $_post_type;
	private $_structs;
	private $_url_to;
	private $_labels;
	private $_key_to_media = [];

	private function __construct( $id, $args ) {
		$this->_id        = 'retrop_export_' . $id;
		$this->_post_type = $args['post_type'];
		$this->_structs   = $this->_sort_structs( $args['structs'] );
		$this->_url_to    = $args['url_to'];

		$this->_labels = [
			'name'        => 'Retrop Exporter',
			'description' => 'Export data to a Excel (.xlsx) file.',
			'success'     => 'Successfully finished.',
			'failure'     => 'Sorry, there has been an error.',
		];
		if ( isset( $args['labels'] ) ) $this->_labels = array_merge( $this->_labels, $args['labels'] );

		add_action( 'admin_menu', [ $this, '_cb_admin_menu' ] );
	}

	private function _sort_structs( &$structs ) {
		$temp = [];
		foreach ( $structs as $key => $s ) {
			if ( $s['type'] === \R\FS_TYPE_MEDIA ) {
				$temp[ $key ] = $s;
				unset( $structs[ $key ] );
			}
		}
		foreach ( $temp as $key => $s ) {
			$structs[ $key ] = $s;
		}
	}

	public function _cb_admin_menu() {
		$label = $this->_labels['name'];
		add_submenu_page( 'tools.php', $label, $label, 'level_7', $this->_id, [ $this, '_cb_output_page' ] );
	}

	private function _header() {
		echo '<div class="wrap">';
		echo '<h2>' . $this->_labels['name'] . '</h2>';
	}

	private function _footer() {
		echo '</div>';
	}

	public function _cb_output_page() {
		wp_enqueue_script( 'xlsx', $this->_url_to . '/xlsx.full.min.js' );
		wp_enqueue_script( 'retrop-exporter', $this->_url_to . '/exporter.min.js' );

		$this->_header();

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
				$this->_output_option_page();
				break;
			case 1:
				check_admin_referer( 'export-option' );
				$fn = empty( $_POST['filename'] ) ? 'export' : $_POST['filename'];
				$bgn = empty( $_POST['date_bgn'] ) ? false : $_POST['date_bgn'];
				$end = empty( $_POST['date_end'] ) ? false : $_POST['date_end'];
				$this->_output_download_page( $fn, $bgn, $end );
				break;
		}
		$this->_footer();
	}

	private function _output_option_page() {
		echo '<div class="narrow">';
		echo '<p>' . $this->_labels['description'] . '</p>';
?>
		<form method="post" action="<?php echo esc_url( wp_nonce_url( 'tools.php?page=' . $this->_id . '&amp;step=1', 'export-option' ) ); ?>">
			<p>
				<label for="filename"><?php _e('File name:') ?></label>
				<input type="text" required="" class="regular-text" id="filename" name="filename">
			</p>
			<fieldset>
				<legend class="screen-reader-text"><?php _e( 'Date range:' ); ?></legend>
				<label for="date-bgn" class="label-responsive"><?php _e( 'Start date:' ); ?></label>
				<select name="date_bgn" id="date-bgn">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php $this->export_date_options(); ?>
				</select>
				<label for="date-end" class="label-responsive"><?php _e( 'End date:' ); ?></label>
				<select name="date_end" id="date-end">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php $this->export_date_options(); ?>
				</select>
			</fieldset>
			<?php submit_button( __('Export'), 'primary' ); ?>
		</form>
<?php
		echo '</div>';
	}

	private function export_date_options() {
		global $wpdb, $wp_locale;

		$months = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
			FROM $wpdb->posts
			WHERE post_type = %s AND post_status != 'auto-draft'
			ORDER BY post_date DESC
		", $this->_post_type ) );

		$month_count = count( $months );
		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) return;

		foreach ( $months as $date ) {
			if ( 0 == $date->year ) continue;
			$month = zeroise( $date->month, 2 );
			echo '<option value="' . $date->year . '-' . $month . '">' . $wp_locale->get_month( $month ) . ' ' . $date->year . '</option>';
		}
	}

	private function _output_download_page( $fileName, $bgn, $end ) {
		$pi = pathinfo( $fileName );
		$fileName = $pi['basename'];
		if ( empty( $pi['extension'] ) ) $fileName .= '.xlsx';
		$_fn = esc_attr( $fileName );

		$json_structs = addslashes( json_encode( array_keys( $this->_structs ) ) );
?>
		<div class="narrow">
			<p><?php echo $this->_labels['description'] ?></p>
			<?php $this->_output_data( $bgn, $end ); ?>
			<p class="submit">
				<input type="submit" name="download" id="download" class="button button-primary" value="<?php _e('Download Export File') ?>">
			</p>
			<div id="retrop-success" style="display: none;"><?php echo esc_html( $this->_labels['success'] ) ?></div>
			<div id="retrop-failure" style="display: none;"><?php echo esc_html( $this->_labels['failure'] ) ?></div>
			<input id="retrop-structs" type="hidden" value="<?php echo $json_structs ?>">
			<input id="retrop-filename" type="hidden" value="<?php echo $_fn ?>">
		</div>
<?php
	}

	private function _output_data( $bgn, $end ) {
		$args = [
			'post_type'      => $this->_post_type,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'asc'
		];
		if ( $bgn !== false || $end !== false ) {
			$dq = [];
			if ( $bgn !== false ) {
				$bgn_ym = explode( '-', $bgn );
				$dq[] = [
					'year' => intval( $bgn_ym[0] ),
					'month' => intval( $bgn_ym[1] ),
					'compare' => '>='
				];
			}
			if ( $end !== false ) {
				$end_ym = explode( '-', $end );
				$dq[] = [
					'year' => intval( $end_ym[0] ),
					'month' => intval( $end_ym[1] ),
					'compare' => '<='
				];
			}
			if ( $bgn !== false && $end !== false ) $dq['relation'] = 'AND';
			$args['date_query'] = $dq;
		}
		$ps = get_posts( $args );
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
			$val = '';
			switch ( $type ) {
			case R\FS_TYPE_TITLE:
				$val = $p->post_title;
				break;
			case R\FS_TYPE_CONTENT:
				$val = $p->post_content;
				if ( isset( $s[R\FS_FILTER] ) && $s[R\FS_FILTER] === R\FS_FILTER_CONTENT_MEDIA ) {
					$this->_key_to_media[ $key ] = $this->_extract_media( $val );
				}
				break;
			case R\FS_TYPE_MEDIA:
				$ckey = $s[R\FS_CONTENT_KEY];
				$val = isset( $this->_key_to_media[ $ckey ] ) ? $this->_key_to_media[ $ckey ] : '';
				break;
			case R\FS_TYPE_META:
				$mkey = $s[R\FS_KEY];
				$val = get_post_meta( $p->ID, $mkey, true );
				if ( isset( $s[R\FS_FILTER] ) && $s[R\FS_FILTER] === R\FS_FILTER_MEDIA_URL ) {
					$ais = wp_get_attachment_image_src( intval( $val ), 'full' );
					if ( $ais !== false ) $val = $ais[0];
				}
				break;
			case R\FS_TYPE_DATE:
				$val = $p->post_date;
				break;
			case R\FS_TYPE_DATE_GMT:
				$val = $p->post_date_gmt;
				break;
			case R\FS_TYPE_TERM:
				$tax = $s[R\FS_TAXONOMY];
				$ts = get_the_terms( $p->ID, $tax );
				if ( is_array( $ts ) ) {
					$slugs = [];
					foreach ( $ts as $t ) $slugs[] = $t->slug;
					$val = implode( ', ', $slugs );
				}
				break;
			case R\FS_TYPE_THUMBNAIL_URL:
				if ( ! has_post_thumbnail( $p->ID ) ) break;
				$id = get_post_thumbnail_id( $p->ID );
				$ais = wp_get_attachment_image_src( $id, 'full' );
				if ( $ais !== false ) $val = $ais[0];
				break;
			case R\FS_TYPE_ACF_PM:
				if ( function_exists( 'get_field' ) ) {
					$mkey = $s[R\FS_KEY];
					$val = get_field( $mkey, $p->ID, false );
				}
				break;
			}
			$val = str_replace( ["\r\n", "\r", "\n"], '\n', $val );
			$ret[] = $val;
		}
		return $ret;
	}

	private function _extract_media( $val ) {
		$ud = wp_upload_dir();
		$upload_url = $ud['baseurl'];
		$dom = str_get_html( $val );
		$id2url = [];

		foreach ( $dom->find( 'img' ) as &$elm ) {
			$p = strpos( $elm->src, $upload_url );
			if ( $p === 0 ) {
				// $elm->src = $this->_convert_url( $elm->src );
			}
		}
		foreach ( $dom->find( 'a' ) as &$elm ) {
			$p = strpos( $elm->href, $upload_url );
			if ( $p === 0 ) {
				// $elm->href = $this->_convert_url( $elm->href );
			}
		}
		$dom->clear();
		unset($dom);
		return $id2url;
	}

	// private function _get_media_id( $url ) {
		// global $wpdb;
		// $at = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) ); 
		// return $at[0];

		// global $wpdb;
		// $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s";
		// preg_match('/([^\/]+?)(-e\d+)?(-\d+x\d+)?(\.\w+)?$/', $url, $matches);
		// $post_name = $matches[1];
		// return (int)$wpdb->get_var($wpdb->prepare($sql, $post_name));
	// }

	private function _get_media_id( $url ) {
		$aid = $this->_search_media_id( $url );
		if ( ! $aid ) $aid = $this->_search_media_id( $url, false );
		return $aid;
	}

	private function _search_media_id( $url, $is_full_size = true ) {
		global $wpdb;
		$full_size_url = $url;

		if ( ! $is_full_size ) {
			$full_size_url = preg_replace( '/(-[0-9]+x[0-9]+)(\.[^.]+){0,1}$/i', '${2}', $url );
			if ( $url === $full_size_url ) return 0;
		}
		$ud = wp_upload_dir();
		$upload_url = $ud['baseurl'];
		if ( strpos( $full_size_url, $upload_url ) !== 0 ) return 0;

		$attached_file = str_replace( $upload_url . '/', '', $full_size_url );
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attached_file' AND meta_value='%s' LIMIT 1;",
			$attached_file
		) );
	}

}

