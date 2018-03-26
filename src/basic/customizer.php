<?php
namespace st\basic;

/**
 *
 * Customizer for Clients
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-26
 *
 */


function remove_wp_logo() {
	add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'wp-logo' );
	}, 300 );
}

function remove_customize_menu() {
	add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'customize' );
	}, 300 );

	add_action( 'admin_menu', function () {
		global $submenu;
		if ( isset( $submenu['themes.php'] ) ) {
			$customize_menu_index = -1;
			foreach ( $submenu['themes.php'] as $index => $menu_item ) {
				foreach ( $menu_item as $data ) {
					if ( strpos( $data, 'customize' ) === 0 ) {
						$customize_menu_index = $index;
						break;
					}
				}
				if ( $customize_menu_index !== -1 ) break;
			}
			unset( $submenu['themes.php'][ $customize_menu_index ] );
		}
	} );
}

function remove_post_menu_when_empty() {
	$counts = wp_count_posts();
	$sum = 0;
	foreach ( $counts as $key => $val ) {
		if ( $key === 'auto-draft' ) continue;
		$sum += $val;
	}
	if ( $sum === 0 ) {
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit.php' );
		} );
		add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
			$wp_admin_bar->remove_menu( 'new-post' );
		}, 100 );
		add_action( 'admin_enqueue_scripts', function () {
			echo '<style>#wp-admin-bar-new-content > a {pointer-events:none;user-select:none;}</style>';
		} );
		if ( is_user_logged_in() && ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', function () {
				echo '<style>#wp-admin-bar-new-content > a {pointer-events:none;user-select:none;}</style>';
			} );
		}
	}
}

function remvoe_archive_title_text() {
	add_filter( 'get_the_archive_title', function ( $title ) {
		if ( is_category() || is_tag() || is_tax() ) {
			$title = single_term_title( '', false );
		} elseif ( is_year() ) {
			$title = get_the_date( 'Y' );
		} elseif ( is_month() ) {
			$title = get_the_date( 'Y-m' );
		} elseif ( is_day() ) {
			$title = get_the_date( 'Y-m-d' );
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );
		}
		return $title;
	} );
}

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
					echo ".categorydiv div[id$='-adder'], .category-tabs{display:none;}";
					echo ".categorydiv div.tabs-panel{border:none;padding:0;}";
					echo ".categorychecklist{margin-top:4px;}";
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

function remove_taxonomy_metabox_checked_ontop() {
	add_filter('wp_terms_checklist_args', function ( $args ) {
		$args['checked_ontop'] = false;
		return $args;
	} );
}


// -----------------------------------------------------------------------------

function ensure_admin_side_bar_menu_area() {
	add_action( 'admin_menu', function () {
		global $menu;
		$menu[19] = $menu[10];
		unset( $menu[10] );
	} );
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

function enable_default_image_sizes() {
	add_image_size( 'small', 320, 9999 );
	add_image_size( 'huge', 2560, 9999 );

	add_filter( 'image_size_names_choose', function ( $sizes ) {
		$is_ja = preg_match( '/^ja/', get_locale() );
		$ns = [];
		foreach ( $sizes as $idx => $s ) {
			$ns[ $idx ] = $s;
			if ( $idx === 'thumbnail' ) $ns[ 'small' ] = ($is_ja ? '小' : 'Small');
			if ( $idx === 'medium' ) $ns[ 'medium_large' ] = ($is_ja ? '中大' : 'Medium Large');
		}
		return $ns;
	} );
}

function enable_to_add_time_stamp_to_src() {
	add_filter( 'style_loader_src', '\st\basic\_add_time_stamp_as_param' );
	add_filter( 'script_loader_src', '\st\basic\_add_time_stamp_as_param' );
}

function _add_time_stamp_as_param( $src ) {
	if ( strpos( $src, get_template_directory_uri() ) === false ) return $src;

	$removed_src = strtok( $src, '?' );
	$path = wp_normalize_path( ABSPATH );
	$resource_file = str_replace(  trailingslashit( site_url() ), trailingslashit( $path ), $removed_src );
	$resource_file = realpath( $resource_file );
	$src = add_query_arg( 'fver', date( 'Ymdhis', filemtime( $resource_file ) ), $src );
	return $src;
}


// -----------------------------------------------------------------------------

function update_default_reading_options() {
	update_option( 'show_on_front', 'page' );
	if ( empty( get_option( 'page_on_front' ) ) ) {
		$pages = get_pages( ['sort_column'  => 'post_id'] );
		if ( ! empty( $pages ) ) update_option( 'page_on_front', $pages[0]->ID );
	}
	update_option( 'page_for_posts', '' );
}

function update_default_discussion_options() {
	update_option( 'default_pingback_flag', 0 );
	update_option( 'default_ping_status', 0 );
	update_option( 'default_comment_status', 0 );
}

function update_default_media_options() {
	update_option( 'thumbnail_size_w', 320 );
	update_option( 'thumbnail_size_h', 320 );
	update_option( 'thumbnail_crop', 1 );
	update_option( 'medium_size_w', 640 );
	update_option( 'medium_size_h', 9999 );
	update_option( 'medium_large_size_w', 960 );
	update_option( 'medium_large_size_h', 9999 );
	update_option( 'large_size_w', 1280 );
	update_option( 'large_size_h', 9999 );
	update_option( 'uploads_use_yearmonth_folders', 1 );
}
