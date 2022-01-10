<?php
/**
 * Default Customization
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-08
 */

namespace st;

require_once __DIR__ . '/robor/custom-admin.php';
require_once __DIR__ . '/robor/no-discussion.php';
require_once __DIR__ . '/robor/remove-default-post.php';
require_once __DIR__ . '/robor/secure-site.php';
require_once __DIR__ . '/robor/source-timestamp.php';
require_once __DIR__ . '/robor/suppressor.php';

/**
 * Customize by defaults.
 *
 * @param array $args {
 *     Arguments.
 *
 *     @type string[] 'permitted_routes'     Permitted routes.
 *     @type bool     'do_remove_feed_links' Whether feed links are removed.
 * }
 */
function customize_by_default( array $args ) {
	$args += array(
		'permitted_routes'     => array(),
		'do_remove_feed_links' => true,
	);

	if ( is_admin_bar_showing() ) {
		// custom-admin.
		remove_wp_logo();
		remove_customize_menu();
		customize_side_menu_order();
	}

	// no-discussion.
	disable_comment_support();
	disable_comment_feed();
	if ( is_admin() ) {
		disable_comment_menu();
	}
	disable_pingback();
	disable_trackback();

	if ( is_admin() ) {
		// remove-default-post.
		remove_default_post_ui();
		remove_default_post_when_empty();
	}

	// secure-site.
	disable_rest_api( $args['permitted_routes'] );
	if ( is_admin() ) {
		disallow_file_edit();
	}
	disable_xml_rpc();
	disable_embed();
	disable_author_page();
	set_membership_option();

	// source-timestamp.
	add_timestamp_to_source();

	// suppressor.
	suppress_head_meta_output( $args['do_remove_feed_links'] );
	suppress_feed_generator_output();
	suppress_emoji_function();
	suppress_version_output();
	suppress_loginout_link_output();
	suppress_robots_txt_output();
}
