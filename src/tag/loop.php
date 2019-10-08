<?php
namespace st;
/**
 *
 * Loop
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-08
 *
 */


function the_loop_posts( $slug, $name, $ps ) {
	global $post;
	foreach ( $ps as $post ) {
		setup_postdata( $post );
		get_template_part( $slug, $name );
	}
	wp_reset_postdata();
}

function the_loop_posts_with_custom_page_template( $slug, $name, $ps ) {
	global $post;
	foreach ( $ps as $post ) {
		setup_postdata( $post );

		$pt = get_post_meta( $post->ID, '_wp_page_template', TRUE );
		if ( ! empty( $pt ) && $pt !== 'default' ) {
			get_template_part( $slug, basename( $pt, '.php' ) );
		} else {
			get_template_part( $slug, $name );
		}
	}
	wp_reset_postdata();
}

function the_loop_query( $slug, $name, $args, $opts = [] ) {
	$ps = get_posts( $args );
	if ( isset( $opts['has_post_thumbnail'] ) ) {
		$hpt = $opts['has_post_thumbnail'];
		$ps = array_values( array_filter( $ps, function ( $p ) use ( $hpt ) {
			return $hpt === has_post_thumbnail( $p->ID );
		} ) );
	}
	if ( count( $ps ) === 0 ) return;
	the_loop_posts( $slug, $name, $ps );
}


// -----------------------------------------------------------------------------


function the_loop_of_child_pages( $slug, $name, $args = [], $opts = [] ) {
	$args = \st\append_page_query( array_merge( [
		'post_parent' => get_the_ID()
	], $args ) );
	the_loop_query( $slug, $name, $args, $opts );
}


// -----------------------------------------------------------------------------


function the_loop_of_posts( $slug, $name, $args = [], $opts = [] ) {
	$args = array_merge( [
		'posts_per_page' => -1,
		'post_type'      => 'post',
		'order'          => 'asc',
	], $args );
	the_loop_query( $slug, $name, $args, $opts );
}
