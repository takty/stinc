<?php
namespace st\basic;
/**
 *
 * Custom Editor
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-23
 *
 */


function remove_unused_heading( $first_level = 2, $count = 3 ) {
	$hs = array_map( function ( $l ) { return "Heading $l=h$l"; }, range( $first_level, $first_level + $count - 1 ) );

	add_filter( 'tiny_mce_before_init', function ( $initArray ) use ( $hs ) {
		// Original from tinymce.min.js "Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre"
		$initArray['block_formats'] = "Paragraph=p;" . implode( ';', $hs ) . ";Preformatted=pre";
		return $initArray;
	} );
}

function remove_taxonomy_metabox_adder_and_tabs( $taxonomies = false, $post_types = false ) {
	add_action( 'admin_head', function () use ( $taxonomies, $post_types ) {
		global $pagenow, $post_type;

		if ( is_admin() && ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) ) {
			if ( $post_types === false || in_array( $post_type, $post_types, true ) ) {
				echo '<style type="text/css">';
				if ( $taxonomies === false ) {
					echo '.categorydiv div[id$="-adder"], .category-tabs{display:none;}';
					echo '.categorydiv div.tabs-panel{border:none;padding:0;}';
					echo '.categorychecklist{margin-top:4px;}';
				} else {
					foreach ( $taxonomies as $tax ) {
						echo "#$tax-adder,#$tax-tabs{display:none;}";
						echo "#$tax-all{border:none;padding:0;}";
						echo "#{$tax}checklist{margin-top:4px;}";
					}
				}
				echo '</style>';
			}
		}
	} );
}


// -----------------------------------------------------------------------------


function disable_taxonomy_metabox_sorting() {
	add_filter('wp_terms_checklist_args', function ( $args ) {
		$args['checked_ontop'] = false;
		return $args;
	} );
}

function disable_table_resizing() {
	add_filter( 'tiny_mce_before_init', function ( $mceInit ) {
		$mceInit['table_resize_bars'] = false;
		$mceInit['object_resizing'] = 'img';
		return $mceInit;
	} );
}

function ensure_admin_side_bar_menu_area() {
	add_action( 'admin_menu', function () {
		global $menu;
		$menu[19] = $menu[10];
		unset( $menu[10] );
	} );
}

function enable_enter_title_here_label() {
	add_filter( 'enter_title_here', function ( $enter_title_here, $post ) {
		$pto = get_post_type_object( $post->post_type );
		if ( isset( $pto->labels->enter_title_here ) && is_string( $pto->labels->enter_title_here ) ) {
			$enter_title_here = esc_html__( $pto->labels->enter_title_here );
		}
		return $enter_title_here;
	}, 10, 2 );
}

function enable_to_upload_svg() {
	add_filter( 'ext2type', function ( $ext2types ) {
		array_push( $ext2types, [ 'image' => [ 'svg', 'svgz' ] ] );
		return $ext2types;
	} );

	add_filter( 'upload_mimes', function ( $mimes ) {
		$mimes['svg'] = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
		return $mimes;
	} );

	add_filter( 'getimagesize_mimes_to_exts', function ( $mime_to_ext ) {
		$mime_to_ext['image/svg+xml'] = 'svg';
		return $mime_to_ext;
	} );
}

function enable_to_show_slug() {
	add_filter( 'manage_pages_columns', function ( $columns ) {
		$columns['slug'] = __('Slug');
		return $columns;
	} );
	add_action( 'manage_pages_custom_column', function ( $column_name, $post_id ) {
		if ( $column_name === 'slug' ) {
			$post = get_post( $post_id );
			echo esc_attr( $post->post_name );
		}
	}, 10, 2);
	add_action( 'admin_head', function () {
		echo '<style>.fixed .column-slug{width:20%;}</style>';
	} );
}

function enable_menu_order_column() {
	add_action( 'load-edit.php' , '\st\basic\_check_post_type_support' );
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		add_action( 'admin_init', '\st\basic\_check_post_type_support' );
	}
}

function _check_post_type_support() {
	$all_post_types = get_post_types( [ 'show_ui' => true ], false );

	if ( ! isset( $_REQUEST['post_type'] ) ) {
		$post_type = 'post';
	} elseif ( in_array( $_REQUEST['post_type'], array_keys( $all_post_types ), true ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		wp_die( esc_html__('Invalid post type') );
	}
	if ( ! post_type_supports( $post_type, 'page-attributes' ) ) return;

	add_filter( "manage_edit-{$post_type}_columns", function ( $cols ) {
		$new_cols = [];
		foreach ( $cols as $name => $display_name ) {
			if ( $name === 'date' ) $new_cols['order'] = __( 'Order' );
			$new_cols[ $name ] = $display_name;
		}
		return $new_cols;
	} );
	add_filter( "manage_edit-{$post_type}_sortable_columns", function ( $cols ) {
		$cols['order'] = 'menu_order';
		return $cols;
	} );
	add_action( "manage_{$post_type}_posts_custom_column", function ( $name, $post_id ) {
		if ( $name === 'order' ) {
			$post = get_post( (int) $post_id );
			echo intval( $post->menu_order );
		}
	}, 10, 2 );
	add_action( 'admin_print_styles-edit.php', function () {
?>
		<style type="text/css" charset="utf-8">
			.fixed .column-order {width:7%;}
			@media screen and (max-width:1100px) and (min-width:782px), (max-width:480px) {.fixed .column-order {width:12%;}}
		</style>
<?php
	} );
}