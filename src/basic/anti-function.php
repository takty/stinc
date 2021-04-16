<?php
/**
 * Anti-Functions - Disabling Unnecessary Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-04-08
 */

namespace st\basic;

function disable_rest_api( $permitted_route = array( 'oembed', 'contact-form-7' ) ) {
	add_filter(
		'rest_pre_dispatch',
		function ( $result, $wp_rest_server, $request ) use ( $permitted_route ) {
			$route = $request->get_route();
			foreach ( $permitted_route as $r ) {
				if ( strpos( $route, "/$r/" ) === 0 ) {
					return $result;
				}
			}
			return new \WP_Error( 'disabled', array( 'status' => rest_authorization_required_code() ) );
		},
		10,
		3
	);
}

function disable_rest_api_all() {
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( preg_match( '/wp-json/', $rule ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
}


// -----------------------------------------------------------------------------


function disable_file_edit() {
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}


// -----------------------------------------------------------------------------


function disable_xml_rpc() {
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter(
		'xmlrpc_methods',
		function ( $methods ) {
			unset( $methods['pingback.ping'] );
			return $methods;
		}
	);
}

function disable_embed() {
	add_filter( 'embed_oembed_discover', '__return_false' );
	add_filter(
		'embed_oembed_html',
		function ( $cached_html, $url, $attr, $post_id ) {
			global $wp_embed;
			return $wp_embed->maybe_make_link( $url );
		},
		10,
		4
	);
}

function disable_author_page() {
	add_filter( 'author_rewrite_rules', '__return_empty_array' );
	add_filter( 'author_link', '__return_empty_string' );

	add_filter(
		'parse_query',
		function ( $query ) {
			if ( ! is_admin() && is_author() ) {
				$query->set_404();
				status_header( 404 );
				nocache_headers();
			}
		}
	);
	// Remove authors from feeds.
	add_filter(
		'the_author',
		function ( $author ) {
			return is_feed() ? get_bloginfo_rss( 'name' ) : $author;
		}
	);
	add_filter(
		'the_author_url',
		function ( $author_meta ) {
			return is_feed() ? home_url() : $author_meta;
		}
	);
}
