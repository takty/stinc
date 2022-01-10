<?php
/**
 * Custom Admin List
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-10
 */

namespace st;

function enable_to_show_slug() {
	add_filter(
		'manage_pages_columns',
		function ( $columns ) {
			$columns['slug'] = __( 'Slug' );
			return $columns;
		}
	);
	add_action(
		'manage_pages_custom_column',
		function ( $column_name, $post_id ) {
			if ( 'slug' === $column_name ) {
				$post = get_post( $post_id );
				echo esc_attr( $post->post_name );
			}
		},
		10,
		2
	);
	add_action(
		'admin_head',
		function () {
			echo '<style>.fixed .column-slug{width:20%;}</style>';
		}
	);
}

function enable_menu_order_column() {
	add_action( 'load-edit.php', '\wpinc\robor\_check_post_type_support' );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_action( 'admin_init', '\wpinc\robor\_check_post_type_support' );
	}
}

function _check_post_type_support() {
	$all_post_types = get_post_types( array( 'show_ui' => true ), false );

	if ( ! isset( $_REQUEST['post_type'] ) ) {
		$post_type = 'post';
	} elseif ( in_array( $_REQUEST['post_type'], array_keys( $all_post_types ), true ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		wp_die( esc_html__( 'Invalid post type' ) );
	}
	if ( ! post_type_supports( $post_type, 'page-attributes' ) ) {
		return;
	}
	add_filter(
		"manage_edit-{$post_type}_columns",
		function ( $cols ) {
			$new_cols = array();
			foreach ( $cols as $name => $display_name ) {
				if ( 'date' === $name ) {
					$new_cols['order'] = __( 'Order' );
				}
				$new_cols[ $name ] = $display_name;
			}
			return $new_cols;
		}
	);
	add_filter(
		"manage_edit-{$post_type}_sortable_columns",
		function ( $cols ) {
			$cols['order'] = 'menu_order';
			return $cols;
		}
	);
	add_action(
		"manage_{$post_type}_posts_custom_column",
		function ( $name, $post_id ) {
			if ( 'order' === $name ) {
				$post = get_post( (int) $post_id );
				echo (int) $post->menu_order;
			}
		},
		10,
		2
	);
	add_action(
		'admin_print_styles-edit.php',
		function () {
			?>
			<style type="text/css" charset="utf-8">
				.fixed .column-order {width:7%;}
				@media screen and (max-width:1100px) and (min-width:782px), (max-width:480px) {.fixed .column-order {width:12%;}}
			</style>
			<?php
		}
	);
}
