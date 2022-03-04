<?php
/**
 * Default Customization
 *
 * @package Wpinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-03-04
 */

namespace st;

require_once __DIR__ . '/alt/custom-admin.php';
require_once __DIR__ . '/alt/no-discussion.php';
require_once __DIR__ . '/alt/pseudo-html.php';
require_once __DIR__ . '/alt/remove-default-post.php';
require_once __DIR__ . '/alt/secure-site.php';
require_once __DIR__ . '/alt/source-timestamp.php';
require_once __DIR__ . '/alt/suppressor.php';

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
function customize_by_default( array $args = array() ): void {
	$args += array(
		'do_remove_feed_links' => true,
		'permitted_route'      => array( 'oembed', 'contact-form-7' ),
	);

	if ( is_admin_bar_showing() ) {
		// custom-admin.
		\wpinc\alt\remove_wp_logo();
		\wpinc\alt\remove_customize_menu();
		\wpinc\alt\customize_side_menu_order();
	}

	// no-discussion.
	\wpinc\alt\disable_comment_support();
	\wpinc\alt\disable_comment_feed();
	if ( is_admin() ) {
		\wpinc\alt\disable_comment_menu();
	}
	\wpinc\alt\disable_pingback();
	\wpinc\alt\disable_trackback();

	if ( is_admin() ) {
		// remove-default-post.
		\wpinc\alt\remove_default_post_ui();
		\wpinc\alt\remove_default_post_when_empty();
	}

	// secure-site.
	\wpinc\alt\disable_rest_api_without_permission( $args['permitted_route'] );
	if ( is_admin() ) {
		\wpinc\alt\disallow_file_edit();
	}
	\wpinc\alt\disable_xml_rpc();
	\wpinc\alt\disable_embed();
	\wpinc\alt\disable_author_page();
	\wpinc\alt\set_membership_option();

	// source-timestamp.
	\wpinc\alt\add_timestamp_to_source();

	// suppressor.
	\wpinc\alt\suppress_head_meta_output( $args['do_remove_feed_links'] );
	\wpinc\alt\suppress_feed_generator_output();
	\wpinc\alt\suppress_emoji_function();
	\wpinc\alt\suppress_version_output();
	\wpinc\alt\suppress_loginout_link_output();
	\wpinc\alt\suppress_robots_txt_output();
}


// -----------------------------------------------------------------------------


/**
 * Enables pseudo HTML.
 */
function enable_pseudo_html() {
	\wpinc\alt\enable_pseudo_html();
}
