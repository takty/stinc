<?php
namespace st;
/**
 *
 * Query
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
 *
 */


function append_post_type_query( $post_type, $post_per_page, $args = [] ) {
	return array_merge( [
		'posts_per_page' => $post_per_page,
		'post_type'      => $post_type,
	], $args );
}

function append_tax_query( $taxonomy, $term_slug_s, $args = [] ) {
	$terms = is_array( $term_slug_s ) ? implode( ',', $term_slug_s ) : $term_slug_s;
	if ( ! isset( $args['tax_query'] ) ) $args['tax_query'] = [];
	$args['tax_query'][] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $terms
	];
	return $args;
}

function append_custom_sticky_query( $args = [] ) {
	if ( ! isset( $args['meta_query'] ) ) $args['meta_query'] = [];
	$args['meta_query'][] = [
		'key'   => '_sticky',
		'value' => '1'
	];
	return $args;
}

function append_ml_tag_query( $args = [] ) {
	if ( ! class_exists( '\st\Multilang' ) ) return $args;

	$ml = \st\Multilang::get_instance();
	if ( ! $ml->has_tag() ) return $args;

	if ( ! isset( $args['tax_query'] ) ) $args['tax_query'] = [];
	$args['tax_query'][] = $ml->get_tax_query();
	return $args;
}

function append_mh_tag_query( $args = [] ) {
	if ( ! class_exists( '\st\Multihome' ) ) return $args;

	$mh = \st\Multihome::get_instance();
	if ( ! $mh->has_tag() ) return $args;

	if ( ! isset( $args['tax_query'] ) ) $args['tax_query'] = [];
	$args['tax_query'][] = $mh->get_tax_query();
	return $args;
}


// -----------------------------------------------------------------------------


function append_page_query( $args = [] ) {
	return array_merge( [
		'posts_per_page' => -1,
		'post_type'      => 'page',
		'orderby'        => 'menu_order',
		'order'          => 'asc',
	], $args );
}

function append_child_page_query( $parent_id = false, $args = [] ) {
	if ( $parent_id === false ) $parent_id = get_the_ID();
	$args = append_page_query( $args );

	return array_merge( [
		'post_parent' => $parent_id
	], $args );
}

function append_sibling_page_query( $sibling_id = false, $args = [] ) {
	if ( $sibling_id === false ) $sibling_id = get_the_ID();
	$post = get_post( $sibling_id );
	$parent_id = empty( $post ) ? 0 : $post->post_parent;
	$args = append_page_query( $args );

	return array_merge( [
		'post_parent' => $parent_id
	], $args );
}


// -----------------------------------------------------------------------------


function get_latest_posts( $post_type, $post_per_page = 6, $ml_tag = false, $args = [] ) {
	$args = append_post_type_query( $post_type, $post_per_page, $args );
	if ( $ml_tag ) $args = append_ml_tag_query( $args );
	return get_posts( $args );
}

function get_custom_sticky_posts( $post_type, $ml_tag = false, $args = [] ) {
	$args = append_post_type_query( $post_type, -1, $args );
	$args = append_custom_sticky_query( $args );
	if ( $ml_tag ) $args = append_ml_tag_query( $args );
	return get_posts( $args );
}


// -----------------------------------------------------------------------------


function get_child_pages( $parent_id = false, $args = [] ) {
	return get_posts( append_child_page_query( $parent_id, $args ) );
}

function get_sibling_pages( $sibling_id = false, $args = [] ) {
	return get_posts( append_sibling_page_query( $sibling_id, $args ) );
}

function get_pages_by_ids( $ids ) {
	$ps = get_posts( append_page_query( [
		'post__in' => $ids
	] ) );
	$id2p = [];
	foreach ( $ps as $p ) $id2p[ $p->ID ] = $p;

	$ret = [];
	foreach ( $ids as $id ) {
		if ( isset( $id2p[ $id ] ) ) $ret[] = $id2p[ $id ];
	}
	return $ret;
}


// -----------------------------------------------------------------------------


function get_sticky_posts( $args = [] ) {
	$sps = get_option( 'sticky_posts' );
	if ( count( $sps ) === 0 ) return [];

	return get_posts( array_merge( [
		'ignore_sticky_posts' => 1,
		'posts_per_page'      => -1,
		'post__in'            => $sps,
	], $args ) );
}
