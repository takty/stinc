<?php
/**
 * Adding Timestamp to Ensure Updated Styles and Scripts Are Loaded.
 *
 * @package Wpinc Robor
 * @author Takuto Yanagida
 * @version 2022-01-10
 */

namespace wpinc\robor;

/**
 * Add timestamps to style and script sources.
 */
function add_timestamp_to_source() {
	add_filter( 'style_loader_src', '\wpinc\robor\_cb_loader_src__add_timestamp' );
	add_filter( 'script_loader_src', '\wpinc\robor\_cb_loader_src__add_timestamp' );
}

/**
 * Callback function for '*_loader_src' filters.
 *
 * @access private
 *
 * @param string $src The source URL of the enqueued style and script.
 */
function _cb_loader_src__add_timestamp( $src ) {
	if ( strpos( $src, get_template_directory_uri() ) === false ) {
		return $src;
	}
	$removed_src   = strtok( $src, '?' );
	$path          = wp_normalize_path( ABSPATH );
	$resource_file = str_replace( trailingslashit( site_url() ), trailingslashit( $path ), $removed_src );
	$resource_file = realpath( $resource_file );
	$src           = add_query_arg( 'fver', gmdate( 'Ymdhis', filemtime( $resource_file ) ), $src );
	return $src;
}
