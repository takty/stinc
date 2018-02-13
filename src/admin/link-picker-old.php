<?php
namespace st\link_picker;

/**
 *
 * Link Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-01-28
 *
 * require system\field.php
 *
 */


const NS = 'st_link_picker';


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

function enqueue_script_for_admin( $url_to ) {
	if ( ! is_admin() ) return;
	wp_enqueue_script( 'st-link-picker', $url_to.'/asset/link-picker.js', [ 'jquery-ui-sortable' ] );
	wp_enqueue_style( 'st-link-picker', $url_to.'/asset/link-picker.css' );
}

function add_admin_enqueue_scripts_action( $url_to ) {
	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $url_to ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
		enqueue_script_for_admin( $url_to );
	} );
}

function add_meta_box( $key, $label, $screen, $opts = [] ) {
	\add_meta_box(
		"{$key}_mb", $label,
		function ( $post ) use ( $key, $opts ) {
			wp_nonce_field( $key, $key . '_nonce' );
			$is_internal_only = isset( $opts['is_internal_only'] ) ? $opts['is_internal_only'] : false;
			output_html( $key, $is_internal_only );
		},
		$screen
	);
}

function save_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;

	save_post( $key, $post_id );
}

function output_html( $key, $is_internal_only ) {
?>
	<input type="hidden" id="<?php echo $key ?>" name="<?php echo $key ?>" value="" />
	<table class="<?php echo NS ?>_table">
		<tbody id="<?php echo $key ?>_tbody">
<?php
output_row( '', '', '', NS.'_item_template' );
foreach ( get_items( $key ) as $it ) {
	output_row( $it['title'], $it['url'], $it['post_id'], NS.'_item', $is_internal_only );
}
?>
			<tr class="<?php echo NS ?>_add_row"><td></td><td><a href="javascript:void(0);" class="<?php echo NS ?>_add button"><?php echo __( 'Add Link', 'default' ) ?></a></td></tr>
		</tbody>
	</table>
	<script>st_link_picker_init('<?php echo $key ?>', <?php echo $is_internal_only ? 'true' : 'false' ?>);</script>
	<textarea id="<?php echo $key ?>_hidden_textarea" style="display: none;"></textarea>
	<div id="<?php echo $key ?>_hidden_div" style="display: none;"></div>
<?php
}

function output_row( $title, $url, $post_id, $class, $is_internal_only = false ) {
?>
	<tr class="<?php echo $class ?>">
		<td>
			<label class="widget-control-remove <?php echo NS ?>_delete_label"><input type="checkbox" class="<?php echo NS ?>_delete"></input><br /><?php echo __( 'Remove', 'default' ) ?></label>
		</td>
		<td>
			<div><span class="<?php echo NS ?>_title_handle"><?php echo __( 'Title', 'default' ) ?>:</span>
			<input type="text" class="<?php echo NS ?>_title" value="<?php echo esc_attr( $title ) ?>" /></div>
			<div><span><a href="<?php echo esc_url( $url ) ?>" target="_blank">URL</a>:</span>
			<input type="text" class="<?php echo NS ?>_url" value="<?php echo esc_attr( $url ) ?>" <?php if ( $is_internal_only ) echo 'readonly' ?>/>
			<a href="javascript:void(0);" class="button <?php echo NS ?>_select"><?php echo __( 'Select', 'default' ) ?></a></div>
			<input type="hidden" value="<?php echo esc_attr( $post_id ) ?>" />
		</td>
	</tr>
<?php
}

function save_post( $key, $post_id ) {
	$items = \st\field\get_multiple_post_meta_from_post( $key, [ 'title', 'url', 'post_id', 'delete' ] );
	$items = array_filter( $items, function ( $it ) { return ! $it['delete'] && ! empty( $it['url'] ); } );
	$items = array_values( $items );

	foreach ( $items as &$it ) {
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
	\st\field\update_multiple_post_meta( $post_id, $key, $items, [ 'title', 'url', 'post_id' ] );
}
