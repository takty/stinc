<?php
namespace st\duration_picker;

/**
 *
 * Duration Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-13
 *
 */


const NS = 'st_duration_picker';


function get_item( $key = '', $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );

	$date_bgn = get_post_meta( $post->ID, $key . '_date_bgn', true );
	$date_end = get_post_meta( $post->ID, $key . '_date_end', true );
	return compact( 'date_bgn', 'date_end' );
}


// -----------------------------------------------------------------------------

function enqueue_script_for_admin( $url_to ) {
	if ( ! is_admin() ) return;
	wp_enqueue_script( 'flatpickr',         $url_to . '/asset/lib/flatpickr.min.js' );
	wp_enqueue_style ( 'flatpickr',         $url_to . '/asset/lib/flatpickr.min.css' );
	wp_enqueue_script( 'flatpickr.l10n.ja', $url_to . '/asset/lib/flatpickr.l10n.ja.min.js' );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
}

function add_meta_box( $key, $label, $screen, $context = 'side', $opts = [] ) {
	$opts = wp_parse_args( $opts, [
		'calendar_locale' => 'en',
		'date_bgn_label'  => 'Begin',
		'date_end_label'  => 'End',
		'year_label'      => '',
	] );
	\add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $opts ) {
			wp_nonce_field( $key, $key . '_nonce' );
			output_html( $key, $opts );
		},
		$screen, $context
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;
	save_post_meta( $post_id, $key );
}

function output_html( $key, $opts ) {
	$item = get_item( $key );
?>
	<div>
		<style>
			.flatpickr-calendar {z-index: 9999 !important;}
			.flatpickr-current-month {display: inline-flex !important; justify-content: center !important; flex-direction: row-reverse !important;}
		</style>
		<div id="date_bgn_row">
			<?php echo esc_html( $opts['date_bgn_label'] ); ?>: <input type="text" name="<?php echo $key ?>_date_bgn" id="date_bgn" size="16" value="<?php if ( isset( $item['date_bgn'] ) ) echo esc_attr( $item['date_bgn'] ); ?>" />
		</div>
		<div id="date_end_row">
			<?php echo esc_html( $opts['date_end_label'] ); ?>: <input type="text" name="<?php echo $key ?>_date_end" id="date_end" size="16" value="<?php if ( isset( $item['date_end'] ) ) echo esc_attr( $item['date_end'] ); ?>" />
		</div>
		<script>
			flatpickr('#date_bgn', {'locale': '<?php echo $opts['calendar_locale'] ?>'});
			flatpickr('#date_end', {'locale': '<?php echo $opts['calendar_locale'] ?>'});
<?php if ( ! empty( $opts['year_label'] ) ) : ?>
			var cms = document.querySelectorAll('.flatpickr-current-month > .cur-month');
			for (var i = 0; i < cms.length; i += 1) {
				var strNen = document.createElement('span');
				strNen.innerText = '<?php echo esc_html( $opts['year_label'] ); ?>';
				cms[i].parentElement.insertBefore(strNen, cms[i].nextSibling);
			}
<?php endif; ?>
		</script>
	</div>
<?php
}

function save_post_meta( $post_id, $key ) {
	$key_bgn = $key . '_date_bgn';
	$key_end = $key . '_date_end';

	$date_bgn = isset( $_POST[ $key_bgn ] ) ? $_POST[ $key_bgn ] : null;
	$date_end = isset( $_POST[ $key_end ] ) ? $_POST[ $key_end ] : null;

	$date_bgn_db = get_post_meta( $post_id, $key_bgn, TRUE );
	$date_end_db = get_post_meta( $post_id, $key_end, TRUE );

	$date_bgn = $date_bgn ? $date_bgn : $date_bgn_db;
	$date_end = $date_end ? $date_end : $date_end_db;
	if ( empty( $date_end ) ) $date_end = $date_bgn;

	$date_bgn_val = (int) str_replace( '-', '', $date_bgn );
	$date_end_val = (int) str_replace( '-', '', $date_end );
	if ( $date_end_val < $date_bgn_val ) list( $date_bgn, $date_end ) = [ $date_end, $date_bgn ];

	update_post_meta( $post_id, $key_bgn, $date_bgn );
	update_post_meta( $post_id, $key_end, $date_end );
}
