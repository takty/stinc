<?php
namespace st\menu_order;

/**
 *
 * Menu Order (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-18
 *
 */


function initialize() {
	add_action( 'load-edit.php' , '\st\menu_order\_check_post_type_support' );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_action( 'admin_init', '\st\menu_order\_check_post_type_support' );
	}
}

function _check_post_type_support() {
	$all_post_types = get_post_types( [ 'show_ui' => true ], false );

	if ( !isset( $_REQUEST['post_type'] ) ) {
		$post_type = 'post';
	} elseif ( in_array( $_REQUEST['post_type'], array_keys( $all_post_types ), true ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		wp_die( __('Invalid post type') );
	}
	if ( post_type_supports( $post_type, 'page-attributes' ) ) _add_order_column( $post_type );
}

function _add_order_column( $post_type ) {
	add_filter( "manage_edit-{$post_type}_columns", function ( $cols ) use ( $post_type ) {
		$new_cols = [];
		foreach ( $cols as $name => $display_name ) {
			if ( $name === 'date' ) {
				$new_cols['order'] = __( 'Order' );

				add_action( "manage_{$post_type}_posts_custom_column", function ( $name, $post_id ) {
					if ( $name === 'order' ) {
						$post = get_post( (int) $post_id );
						echo $post->menu_order;
					}
				}, 10, 2 );
			}
			$new_cols[ $name ] = $display_name;
		}
		return $new_cols;
	} );
	add_filter( "manage_edit-{$post_type}_sortable_columns", function ( $cols ) {
		$cols['order'] = 'menu_order';
		return $cols;
	} );
	add_action( 'admin_print_styles-edit.php', function () {
?>
		<style type="text/css" charset="utf-8">
			.fixed .column-order {width: 7%;}
			@media screen and (max-width: 1100px) and (min-width: 782px), (max-width: 480px) {
				.fixed .column-order {width: 12%;}
			}
		</style>
<?php
	} );
}
