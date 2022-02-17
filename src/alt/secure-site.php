<?php
/**
 * Secure Site - Disabling Unnecessary Functions
 *
 * @package Wpinc Alt
 * @author Takuto Yanagida
 * @version 2022-01-16
 */

namespace wpinc\alt;

/**
 * Disable REST API for specific routes.
 *
 * @param string[] $permitted_routes Permitted routes. For example, array( 'oembed', 'contact-form-7' ).
 */
function disable_rest_api( array $permitted_routes = array() ): void {
	add_filter(
		'rest_pre_dispatch',
		function ( $result, $wp_rest_server, $request ) use ( $permitted_routes ) {
			$path = $request->get_route();
			foreach ( $permitted_routes as $r ) {
				if ( 0 === strpos( $path, "/$r/" ) ) {
					return $result;
				}
			}
			return new \WP_Error(
				'rest_disabled',
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		},
		10,
		3
	);
}

/**
 * Shutdown REST API completely.
 */
function shutdown_rest_api(): void {
	remove_action( 'init', 'rest_api_init' );
	remove_action( 'rest_api_init', 'rest_api_default_filters', 10, 1 );
	remove_action( 'rest_api_init', 'register_initial_settings', 10 );
	remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
	remove_action( 'parse_request', 'rest_api_loaded' );
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


/**
 * Disallow file edit.
 */
function disallow_file_edit(): void {
	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}

/**
 * Disable XML RPC.
 */
function disable_xml_rpc(): void {
	add_filter( 'xmlrpc_enabled', '__return_false' );
}

/**
 * Disable embed feature.
 */
function disable_embed(): void {
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
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10, 3 );

	add_filter(
		'rest_endpoints',
		function ( $endpoints ) {
			unset( $endpoints['/oembed/1.0/embed'] );
			return $endpoints;
		}
	);
	add_filter(
		'oembed_response_data',
		function ( $data ) {
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return false;
			}
			return $data;
		}
	);
	add_filter(
		'rewrite_rules_array',
		function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( false !== strpos( $rewrite, 'embed=true' ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		}
	);
	add_action(
		'wp_default_scripts',
		function ( $scripts ) {
			if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
				$scripts->registered['wp-edit-post']->deps = array_diff(
					$scripts->registered['wp-edit-post']->deps,
					array( 'wp-embed' )
				);
			}
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Disable author pages.
 */
function disable_author_page(): void {
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
	// Remove authors from endpoints.
	add_filter(
		'rest_endpoints',
		function ( $endpoints ) {
			unset( $endpoints['/wp/v2/users'] );
			unset( $endpoints['/wp/v2/users/(?P<id>[d]+)'] );
			return $endpoints;
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Set membership options secure.
 */
function set_membership_option(): void {
	update_option( 'users_can_register', 0 );
	update_option( 'default_role', 'subscriber' );
}
