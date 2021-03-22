<?php
namespace st;
/**
 *
 * Query
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-22
 *
 */


function append_post_type_query( $post_type, $post_per_page, $args = [] ) {
	return array_merge( [
		'posts_per_page' => $post_per_page,
		'post_type'      => $post_type,
	], $args );
}

function append_tax_query( $taxonomy, $term_slug_s, $args = [] ) {
	if ( is_string( $term_slug_s ) && strpos( $term_slug_s, ',' ) !== false ) {
		$term_slug_s = array_map( function ( $e ) { return trim( $e ); }, explode( ',', $term_slug_s ) );
	}
	if ( ! isset( $args['tax_query'] ) ) $args['tax_query'] = [];
	$args['tax_query'][] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $term_slug_s
	];
	return $args;
}

function append_tax_query_with_term_of( $taxonomy, $post_id, $args = [] ) {
	$term_slugs = [];
	$ts = get_the_terms( $post_id, $taxonomy );
	if ( $ts !== false ) $term_slugs = array_map( function ( $t ) { return $t->slug; }, $ts );

	if ( ! isset( $args['tax_query'] ) ) $args['tax_query'] = [];
	$args['tax_query'][] = [
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $term_slugs
	];
	return $args;
}

function append_custom_sticky_query( $args = [] ) {
	if ( ! isset( $args['meta_query'] ) ) $args['meta_query'] = [];
	$args['meta_query'][] = [
		'key'   => \st\sticky\PMK_STICKY,
		'value' => '1'
	];
	return $args;
}

function append_upcoming_post_query( $offset_year = 1, $offset_month = 0, $offset_day = 0, $args = [] ) {
	$today  = \st\create_date_string_of_today();
	$limit  = \st\create_date_string_of_today( $offset_year, $offset_month, $offset_day );

	if ( ! isset( $args['meta_query'] ) ) $args['meta_query'] = [];
	$args['meta_query']['relation'] = 'AND';
	$args['meta_query'][] = [
		'key'     => \st\event\PMK_DATE_END,
		'value'   => $today,
		'type'    => 'DATE',
		'compare' => '>=',
	];
	$args['meta_query'][] = [
		'key'     => \st\event\PMK_DATE_BGN,
		'value'   => $limit,
		'type'    => 'DATE',
		'compare' => '<=',
	];
	$args['order'] = 'ASC';
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


function get_latest_posts( $post_type, $post_per_page = 6, $args = [] ) {
	$args = append_post_type_query( $post_type, $post_per_page, $args );
	return get_posts( $args );
}

function get_custom_sticky_posts( $post_type, $args = [] ) {
	$args = append_post_type_query( $post_type, -1, $args );
	$args = append_custom_sticky_query( $args );
	return get_posts( $args );
}

function get_custom_sticky_and_latest_posts( $post_type, $post_per_page = 6, $args = [] ) {
	$sticky = get_custom_sticky_posts( $post_type, $args );
	$latest = get_latest_posts( $post_type, $post_per_page, $args );

	return merge_sticky_and_latest( $sticky, $latest, $post_per_page );
}


// -----------------------------------------------------------------------------


function merge_sticky_and_latest( $sticky, $latest, $count ) {
	$sticky_ids = array_map( function ( $p ) { return $p->ID; }, $sticky );
	$ret = $sticky;

	foreach ( $latest as $l ) {
		if ( $count !== -1 && $count <= count( $ret ) ) break;
		if ( in_array( $l->ID, $sticky_ids, true ) ) continue;
		$ret[] = $l;
	}
	return $ret;
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
