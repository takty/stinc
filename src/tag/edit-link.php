<?php
/**
 * Custom Template Tags for Edit Links
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-22
 */

namespace st;

function can_edit_post() {
	global $post;
	return $post && is_user_logged_in() && current_user_can( 'edit_post', $post->ID );
}

function can_edit_theme_options() {
	return is_user_logged_in() && current_user_can( 'edit_theme_options' );
}

function the_edit_link_post() {
	global $post;
	echo esc_attr( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) );
}

function the_edit_link_menu( $nav_menu ) {
	echo esc_attr( admin_url( 'nav-menus.php?action=edit&menu=' . $nav_menu->get_menu_id() ) );
}

function the_edit_link_widget() {
	echo esc_attr( admin_url( 'widgets.php' ) );
}

function the_edit_link_option_page( $page_slug ) {
	echo esc_attr( admin_url( 'options-general.php?page=' . $page_slug ) );
}
