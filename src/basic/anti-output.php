<?php
/**
 * Anti-Outputs - Disabling Unnecessary Outputs
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-04-08
 */

namespace st\basic;

function disable_generator_output() {
	$as = array(
		'rss2_head',
		'commentsrss2_head',
		'rss_head',
		'rdf_header',
		'atom_head',
		'comments_atom_head',
		'opml_head',
		'app_head'
	);
	foreach ( $as as $a ) {
		remove_action( $a, 'the_generator' );
	}
}

function disable_version_output() {
	add_action(
		'wp_default_scripts',
		function ( $inst ) {
			$inst->default_version = '';
		}
	);
	add_action(
		'wp_default_styles',
		function ( $inst ) {
			$inst->default_version = '';
		}
	);
	add_filter( 'style_loader_src', '\st\basic\_cb_loader_src_ver' );
	add_filter( 'script_loader_src', '\st\basic\_cb_loader_src_ver' );
}

function _cb_loader_src_ver( $src ) {
	if ( strpos( $src, 'ver=' ) !== false ) {
		return remove_query_arg( 'ver', $src );
	}
	return $src;
}

function disable_unnecessary_header_tag_output( $is_feed_used = false ) {
	if ( ! $is_feed_used ) {
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
}

function disable_login_link_output() {
	add_filter( 'loginout', '__return_empty_string' );
}

function disable_robots_txt_output() {
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( preg_match( '/robots\\.txt\$/', $rule ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}
