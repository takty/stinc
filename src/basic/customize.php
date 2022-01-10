<?php
/**
 * Default Customization
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-10
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
function customize_by_default( array $args = array() ) {
	$args += array(
		'permitted_routes'     => array(),
		'do_remove_feed_links' => true,
	);

	if ( is_admin_bar_showing() ) {
		// custom-admin.
		\wpinc\robor\remove_wp_logo();
		\wpinc\robor\remove_customize_menu();
		\wpinc\robor\customize_side_menu_order();
	}

	// no-discussion.
	\wpinc\robor\disable_comment_support();
	\wpinc\robor\disable_comment_feed();
	if ( is_admin() ) {
		\wpinc\robor\disable_comment_menu();
	}
	\wpinc\robor\disable_pingback();
	\wpinc\robor\disable_trackback();

	if ( is_admin() ) {
		// remove-default-post.
		\wpinc\robor\remove_default_post_ui();
		\wpinc\robor\remove_default_post_when_empty();
	}

	// secure-site.
	\wpinc\robor\disable_rest_api( $args['permitted_routes'] );
	if ( is_admin() ) {
		\wpinc\robor\disallow_file_edit();
	}
	\wpinc\robor\disable_xml_rpc();
	\wpinc\robor\disable_embed();
	\wpinc\robor\disable_author_page();
	\wpinc\robor\set_membership_option();

	// source-timestamp.
	\wpinc\robor\add_timestamp_to_source();

	// suppressor.
	\wpinc\robor\suppress_head_meta_output( $args['do_remove_feed_links'] );
	\wpinc\robor\suppress_feed_generator_output();
	\wpinc\robor\suppress_emoji_function();
	\wpinc\robor\suppress_version_output();
	\wpinc\robor\suppress_loginout_link_output();
	\wpinc\robor\suppress_robots_txt_output();
}
