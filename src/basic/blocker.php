<?php
namespace st\basic;

/**
 *
 * Blocker - Disable Unused Functions
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-16
 *
 * Usage:
 *  require_once get_parent_theme_file_path( '/lib/stinc/basic/blocker.php' );
 *  add_action( 'after_setup_theme', '\st\basic\disable_unused_functions' );
 *
 */


function disable_unused_functions( $args = [] ) {
	$args = array_merge( [
		'disable_rest_api'       => true,
		'disable_rest_api_force' => false,
		'permitted_rest_route'   => [ 'oembed', 'contact-form-7' ],
	], $args );
	disable_author_page();
	disable_generator_output();
	if ( $args['disable_rest_api'] ) {
		disable_rest_api( $args['disable_rest_api_force'], $args['permitted_rest_route'] );
	}
	disable_xml_rpc();
	disable_file_edit();
	disable_embed();

	disable_version_output();
	disable_tag_output();
	disable_login_link_output();
	disable_robotstxt();
}

function disable_author_page() {
	add_filter( 'author_rewrite_rules', '__return_empty_array' );
	add_filter( 'author_link', '__return_empty_string' );

	add_filter( 'parse_query', function ( $query ) {
		if ( ! is_admin() && is_author() ) {
			$query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	} );

	// Remove authors from feeds
	add_filter( 'the_author', function ( $author ) {
		return is_feed() ? get_bloginfo( 'name' ) : $author;
	} );
	add_filter( 'the_author_url', function ( $author_meta ) {
		return is_feed() ? home_url() : $author_meta;
	} );
}

function disable_generator_output() {
	$actions = [
		'rss2_head',
		'commentsrss2_head',
		'rss_head',
		'rdf_header',
		'atom_head',
		'comments_atom_head',
		'opml_head',
		'app_head'
	];
	foreach ( $actions as $action ) {
		remove_action( $action, 'the_generator' );
	}
}

function disable_rest_api( $force = true, $permitted_route = [] ) {
	if ( $force ) {
		remove_action( 'rest_api_init', 'create_initial_rest_routes', 99 );
		add_filter( 'rewrite_rules_array', function ( $rules ) {
			foreach ( $rules as $rule => $rewrite ) {
				if ( preg_match( '/wp-json/', $rule ) ) {
					unset( $rules[ $rule ] );
				}
			}
			return $rules;
		} );
		return;
	}
	add_filter( 'rest_pre_dispatch', function ( $result, $wp_rest_server, $request ) use ( $permitted_route ) {
		$route = $request->get_route();
		foreach ( $permitted_route as $r ) {
			if ( strpos( $route, "/$r/" ) === 0 ) return $result;
		}
		return new \WP_Error( 'disabled', [ 'status' => rest_authorization_required_code() ] );
	}, 10, 3 );
}

function disable_xml_rpc() {
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', function ( $methods ) {
		unset( $methods['pingback.ping'] );
		return $methods;
	} );
}

function disable_file_edit() {
	if ( ! defined ( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}

function disable_embed() {
	add_filter( 'embed_oembed_discover', '__return_false' );
	add_filter( 'embed_oembed_html', function ( $cached_html, $url, $attr, $post_id ) {
		global $wp_embed;
		return $wp_embed->maybe_make_link( $url );
	}, 10, 4 );
}


// -----------------------------------------------------------------------------

function disable_version_output() {
	if ( ! function_exists( 'remove_wp_ver' ) ) {
		function remove_wp_ver( $inst ) {
			$inst->default_version = '';
		}
	}
	add_action( 'wp_default_scripts', '\st\basic\remove_wp_ver' );
	add_action( 'wp_default_styles', '\st\basic\remove_wp_ver' );

	if ( ! function_exists( 'remove_wp_ver_str' ) ) {
		function remove_wp_ver_str( $src ) {
			if ( strpos( $src, 'ver=' ) ) {
				$src = remove_query_arg( 'ver', $src );
			}
			return $src;
		}
	}
	add_filter( 'style_loader_src', '\st\basic\remove_wp_ver_str' );
	add_filter( 'script_loader_src', '\st\basic\remove_wp_ver_str' );
}

function disable_tag_output() {
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
}

function disable_login_link_output() {
	add_filter( 'loginout', '__return_empty_string' );
}

function disable_robotstxt() {
	add_filter( 'rewrite_rules_array', function ( $rules ) {
		foreach ( $rules as $rule => $rewrite ) {
			if ( preg_match( '/robots\\.txt\$/', $rule ) ) {
				unset( $rules[ $rule ] );
			}
	    }
		return $rules;
	} );
}
