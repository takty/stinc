<?php
namespace st\news;
/**
 *
 * News Post Type
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-15
 *
 */


require_once __DIR__ . '/post_type.php';
require_once __DIR__ . '/../admin/list-table-column.php';


function register_post_type( $post_type = 'news', $slug = false, $labels = [], $args = [] ) {
	$labels = array_merge( [
		'name' => 'News'
	], $labels );
	$args = array_merge( [
		'labels'        => $labels,
		'public'        => true,
		'show_ui'       => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-admin-post',
		'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
		'has_archive'   => true,
		'rewrite'       => false,
	], $args );

	if ( $slug === false ) $slug = $post_type;
	\register_post_type( $post_type, $args );
	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date' );
}

function set_admin_columns( $post_type, $add_cat, $add_tag ) {
	add_action( 'wp_loaded', function () use ( $post_type, $add_cat, $add_tag )  {
		$cs = \st\list_table_column\insert_default_columns();
		$cs = \st\list_table_column\insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, -1, $cs );
		$cs = \st\list_table_column\insert_mh_tag_columns( $post_type, -1, $cs );
		$cs = \st\list_table_column\insert_ml_tag_columns( $post_type, -1, $cs );
		\st\list_table_column\set_admin_columns( $post_type, $cs );
	} );
}
