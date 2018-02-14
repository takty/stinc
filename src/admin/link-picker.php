<?php
namespace st\link_picker;

/**
 *
 * Link Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-14
 *
 */


require_once __DIR__ . '/../system/field.php';


const NS = 'st-link-picker';

const CLS_TABLE     = NS . '-table';

const CLS_ITEM      = NS . '-item';
const CLS_ITEM_TEMP = NS . '-item-template';
const CLS_ADD       = NS . '-add';

const CLS_HANDLE    = NS . '-handle';
const CLS_SEL       = NS . '-select';

const CLS_URL       = NS . '-url';
const CLS_TITLE     = NS . '-title';
const CLS_DEL       = NS . '-delete';
const CLS_POST_ID   = NS . '-post-id';


function get_items( $key, $post_id = false ) {
	if ( $post_id === false ) $post_id = get_the_ID();
	$post = get_post( $post_id );

	$items = \st\field\get_multiple_post_meta( $post_id, $key, [ 'title', 'url', 'post_id' ] );
	foreach ( $items as &$it ) {
		if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {  // for Backward Compatible
			$it['post_id'] = $it['url'];
		}
		if ( empty( $it['post_id'] ) || ! is_numeric( $it['post_id'] ) ) continue;
		$permalink = get_permalink( intval( $it['post_id'] ) );
		if ( $permalink !== false && $it['url'] !== $permalink ) {
			$it['url'] = $permalink;
		}
	}
	return $items;
}


// -----------------------------------------------------------------------------

function enqueue_script( $url_to = false ) {
	if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
	if ( is_admin() ) {
		wp_enqueue_script( 'picker-link', $url_to . '/asset/picker-link.min.js' );
		wp_enqueue_script( 'st-link-picker', $url_to . '/asset/link-picker.min.js', [ 'jquery-ui-sortable' ] );
		wp_enqueue_style( 'st-link-picker', $url_to . '/asset/link-picker.min.css' );
	}
}

function add_meta_box( $key, $label, $screen, $opts = [] ) {
	\add_meta_box(
		"{$key}_mb", $label,
		function ( $post ) use ( $key, $opts ) {
			wp_nonce_field( $key, $key . '_nonce' );
			$is_internal_only = isset( $opts['is_internal_only'] ) ? $opts['is_internal_only'] : false;
			$max_count        = isset( $opts['max_count'] )        ? $opts['max_count']        : false;
			output_html( $key, $is_internal_only, $max_count );
		},
		$screen
	);
}

function save_meta_box( $post_id, $key, $opts = [] ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;

	$is_internal_only = isset( $opts['is_internal_only'] ) ? $opts['is_internal_only'] : false;
	save_post( $key, $post_id, $is_internal_only );
}

function output_html( $key, $is_internal_only, $max_count ) {
?>
	<input type="hidden" id="<?php echo $key ?>" name="<?php echo $key ?>" value="" />
	<table class="<?php echo CLS_TABLE ?>">
		<tbody id="<?php echo $key ?>-item-set">
<?php
output_row( '', '', '', CLS_ITEM_TEMP );
foreach ( get_items( $key ) as $idx => $it ) {
	output_row( $it['title'], $it['url'], $it['post_id'], CLS_ITEM, $is_internal_only );
	if ( $max_count !== false && $idx + 1 === $max_count ) break;
}
?>
			<tr><td></td><td><a href="javascript:void(0);" class="<?php echo CLS_ADD ?> button"><?php echo __( 'Add Link', 'default' ) ?></a></td></tr>
		</tbody>
	</table>
	<script>initializeLinkPicker('<?php echo $key ?>', <?php echo $is_internal_only ? 'true' : 'false' ?><?php echo $max_count !== false ? ", $max_count" : '' ?>);</script>
<?php
}

function output_row( $title, $url, $post_id, $class, $read_only = false ) {
	$_url     = esc_url( $url );
	$_title   = esc_attr( $title );
	$_post_id = esc_attr( $post_id );
?>
	<tr class="<?php echo $class ?>">
		<td>
			<label class="widget-control-remove"><input type="checkbox" class="<?php echo CLS_DEL ?>"></input><br /><?php esc_html_e( 'Remove', 'default' ) ?></label>
		</td>
		<td>
			<div>
				<span class="<?php echo CLS_HANDLE ?>"><?php esc_html_e( 'Title', 'default' ) ?>:</span>
				<input type="text" class="<?php echo CLS_TITLE ?> link-title" value="<?php echo $_title ?>" />
			</div>
			<div>
				<span><a href="<?php echo $_url ?>" target="_blank" class="link-url">URL</a>:</span>
				<input type="text" class="<?php echo CLS_URL ?> link-url" value="<?php echo $_url ?>" <?php if ( $read_only ) echo 'readonly' ?>/>
				<a href="javascript:void(0);" class="button <?php echo CLS_SEL ?>"><?php esc_html_e( 'Select', 'default' ) ?></a>
			</div>
			<input type="hidden" class="<?php echo CLS_POST_ID ?> link-post-id" value="<?php echo $_post_id ?>" />
		</td>
	</tr>
<?php
}

function save_post( $key, $post_id, $is_internal_only ) {
	$items = \st\field\get_multiple_post_meta_from_post( $key, [ 'title', 'url', 'post_id', 'delete' ] );
	$items = array_filter( $items, function ( $it ) { return ! $it['delete'] && ! empty( $it['url'] ); } );
	$items = array_values( $items );

	if ( $is_internal_only ) {
		foreach ( $items as &$it ) ensure_internal_link( $it );
	}
	\st\field\update_multiple_post_meta( $post_id, $key, $items, [ 'title', 'url', 'post_id' ] );
}

function ensure_internal_link( &$it ) {
	$pid = url_to_postid( $it['url'] );

	if ( empty( $it['post_id'] ) ) {
		if ( $pid === 0 ) {
			$p = get_page_by_title( $it['title'] );
			if ( $p !== null ) {
				$it['url'] = get_permalink( $p->ID );
				$it['post_id'] = $p->ID;
			}
		} else {
			$it['post_id'] = $pid;
		}
	} else {
		if ( $pid === 0 ) {
			$url = get_permalink( intval( $it['post_id'] ) );
			if ( $url === false ) {
				$p = get_page_by_title( $it['title'] );
				if ( $p !== null ) $it['url'] = get_permalink( $p->ID );
			} else {
				$it['url'] = $url;
			}
		} else if ( $pid !== intval( $it['post_id'] ) ) {
			$it['post_id'] = $pid;
		}
	}
}
