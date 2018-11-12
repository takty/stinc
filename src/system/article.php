<?php
namespace st\article;

/**
 *
 * Article Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-12
 *
 */


require_once __DIR__ . '/field.php';
require_once __DIR__ . '/taxonomy.php';
require_once __DIR__ . '/sticky.php';


function register_post_type(
	$post_type    = 'article',
	$slug         = false,
	$labels       = [ 'type_label' => 'Article' ],
	$args         = [],
	$add_category = true,
	$add_tag      = false,
	$by_post_name = false
) {
	if ( $slug === false ) $slug = $post_type;
	$base_arg = [
		'label'         => $labels['type_label'],
		'labels'        => [],
		'public'        => true,
		'show_ui'       => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-admin-post',
		'supports'      => [ 'title', 'editor', 'sticky', 'revisions', 'thumbnail' ],
		'has_archive'   => true,
		'rewrite'       => false,
	];
	if ( ! empty( $labels['enter_title_here'] ) ) {
		$base_arg['labels']['enter_title_here'] = $labels['enter_title_here'];
	}
	$args = array_merge( $base_arg, $args );
	\register_post_type( $post_type, $args );

	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date', $by_post_name );

	if ( in_array( 'sticky', $args['supports'], true ) ) {
		\st\sticky\make_custom_post_type_sticky( [ $post_type ] );
	}
	if ( $add_category ) _add_category_taxonomy( $post_type, $slug );
	if ( $add_tag )      _add_tag_taxonomy( $post_type, $slug );

	_set_column_width( $post_type, $add_category, $add_tag );
	add_filter( 'enter_title_here', '_cb_enter_title_here', 10, 2 );
}

function _add_category_taxonomy( $post_type, $slug ) {
	register_taxonomy( "{$post_type}_category", $post_type, [
		'hierarchical'      => true,
		'label'             => 'カテゴリー',
		'public'            => true,
		'show_ui'           => true,
		'rewrite'           => [ 'with_front' => false, 'slug' => "{$slug}/category" ],
		'sort'              => true,
		'show_admin_column' => true
	] );
	\st\taxonomy\set_taxonomy_post_type_specific( [ "{$post_type}_category" ], $post_type );
}

function _add_tag_taxonomy( $post_type, $slug ) {
	register_taxonomy( "{$post_type}_tag", $post_type, [
		'hierarchical'      => true,
		'label'             => 'タグ',
		'public'            => true,
		'show_ui'           => true,
		'rewrite'           => [ 'with_front' => false, 'slug' => "{$slug}/tag" ],
		'sort'              => true,
		'show_admin_column' => true
	] );
	\st\taxonomy\set_taxonomy_post_type_specific( [ "{$post_type}_tag" ], $post_type );
}

function _set_column_width( $post_type, $add_category, $add_tag ) {
	add_action( 'wp_loaded', function () use ( $post_type, $add_category, $add_tag )  {
		$cs = [ 'cb', 'title' ];
		if ( $add_category ) $cs[] = ['name' => "{$post_type}_category", 'width' => '10%'];
		if ( $add_tag )      $cs[] = ['name' => "{$post_type}_tag",      'width' => '10%'];
		if ( class_exists( '\st\Multihome' ) ) {
			$mh = \st\Multihome::get_instance();
			if ( $mh->has_tag( $post_type ) ) {
				$cs[] = [ 'name' => $mh->get_taxonomy(), 'width' => '10%' ];
			}
		}
		if ( class_exists( '\st\Multilang' ) ) {
			$ml = \st\Multilang::get_instance();
			if ( $ml->has_tag( $post_type ) ) {
				$cs[] = [ 'name' => $ml->get_taxonomy(), 'width' => '10%' ];
			}
		}
		$cs[] = 'date';
		\st\field\set_admin_columns( $post_type, $cs );
	} );
}

function _cb_enter_title_here( $enter_title_here, $post ) {
	$post_type = get_post_type_object( $post->post_type );
	if ( isset( $post_type->labels->enter_title_here ) && $post_type->labels->enter_title_here && is_string( $post_type->labels->enter_title_here ) ) {
		$enter_title_here = esc_html( $post_type->labels->enter_title_here );
	}
	return $enter_title_here;
}

function get_sticky_articles( $post_type = 'article', $ml_tag = true, $opts = [] ) {
	$args =  [
		'post_type' => $post_type,
		'posts_per_page' => 1,
		'meta_query' => [ [ 'key' => '_sticky', 'value' => '1' ] ]
	];
	if ( $ml_tag && class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		if ( $ml->has_tag() ) {
			$args['tax_query'] = [ $ml->get_tax_query() ];
		}
	}
	$args = array_merge_recursive( $args, $opts );
	$ps = get_posts( $args );
	return count( $ps ) > 0 ? $ps[0] : null;
}

function get_latest_articles( $post_type, $post_per_page = 6, $ml_tag = true, $opts = [] ) {
	$args = [
		'post_type' => $post_type,
		'posts_per_page' => $post_per_page,
	];
	if ( $ml_tag && class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		if ( $ml->has_tag() ) {
			$args['tax_query'] = [ $ml->get_tax_query() ];
		}
	}
	$args = array_merge_recursive( $args, $opts );
	$ps = get_posts( $args );
	return $ps;
}
