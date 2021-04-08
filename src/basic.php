<?php
namespace st\basic;
/**
 *
 * Basic Customization
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-04-08
 *
 */


require_once __DIR__ . '/basic/anti-function.php';
require_once __DIR__ . '/basic/anti-output.php';
require_once __DIR__ . '/basic/anti-spam.php';

require_once __DIR__ . '/basic/custom-admin-bar.php';
require_once __DIR__ . '/basic/custom-editor.php';
require_once __DIR__ . '/basic/custom-front.php';
require_once __DIR__ . '/basic/custom-pdf-thumbnail.php';
require_once __DIR__ . '/basic/custom-system.php';

require_once __DIR__ . '/basic/default-option.php';


function apply_anti( $is_feed_used = false ) {
	if ( is_admin() ) {
		disable_file_edit();
		disable_comment_menu();
	}
	disable_generator_output();
	disable_version_output();
	disable_unnecessary_header_tag_output( $is_feed_used );
	disable_login_link_output();
	disable_robots_txt_output();

	disable_xml_rpc();
	disable_embed();
	disable_author_page();

	disable_comment_support();
	disable_comment_feed();
	disable_trackback();
}

function apply_custom() {
	if ( is_admin_bar_showing() ) {
		remove_wp_logo();
		remove_customize_menu();
		remove_post_menu_when_empty();
	}
	if ( is_admin() ) {
		disable_taxonomy_metabox_sorting();
		disable_table_resizing();
		ensure_admin_side_bar_menu_area();
		enable_enter_title_here_label();
		enable_to_upload_svg();
		enable_to_show_slug();
		enable_menu_order_column();
	} else {
		remove_archive_title_text();
		remove_separator_in_title_and_description();
		disable_embedded_sticky_post_behavior();
	}
	disable_emoji();
	enable_used_tags();
	enable_default_image_sizes();
	enable_to_add_timestamp_to_src();
}

function apply_default_options() {
	update_reading_options();
	update_discussion_options();
	update_media_options();
}
