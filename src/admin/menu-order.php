<?php
namespace st\menu_order;

/**
 *
 * Menu Order (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-23
 *
 */


function initialize() {
	add_action( 'load-edit.php' , '\st\menu_order\_check_post_type_support_page_attr' );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_action( 'admin_init', '\st\menu_order\_check_post_type_support_page_attr' );
	}
}

function _check_post_type_support_page_attr() {
	$all_post_types = get_post_types( [ 'show_ui' => true ], false );

	if ( !isset( $_REQUEST['post_type'] ) ) {
		$edit_post_type = 'post';
	} elseif ( in_array( $_REQUEST['post_type'], array_keys( $all_post_types ) ) ) {
		$edit_post_type = $_REQUEST['post_type'];
	} else {
		wp_die( __('Invalid post type') );
	}

	if ( ! post_type_supports( $edit_post_type, 'page-attributes' ) ) return;

	add_filter( 'manage_'.$edit_post_type.'_posts_columns', function ( $posts_columns ) {
		$new_columns = [];
		foreach ( $posts_columns as $column_name => $column_display_name ) {
			if ( $column_name === 'date' ) {
				$new_columns['order'] = __( 'Order' );
				add_action( 'manage_pages_custom_column', '\st\menu_order\_display_menu_order_column', 10, 2 );
				add_action( 'manage_posts_custom_column', '\st\menu_order\_display_menu_order_column', 10, 2 );
			}
			$new_columns[$column_name] = $column_display_name;
		}
		return $new_columns;
	} );
	add_filter( 'manage_edit-'.$edit_post_type.'_sortable_columns', function ( $sortable_column ) {
		$sortable_column['order'] = 'menu_order';
		return $sortable_column;
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

function _display_menu_order_column( $column_name, $post_id ) {
	if ( $column_name === 'order' ) {
		$post_id = (int) $post_id;
		$post = get_post( $post_id );
		echo $post->menu_order;
	}
}
