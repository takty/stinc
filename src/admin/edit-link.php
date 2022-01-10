<?php
/**
 * Custom Template Tags for Edit Links
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-01-10
 */

namespace st;

/**
 * Echo edit post link of posts when available.
 *
 * @param string $cls CSS class.
 */
function the_admin_edit_post( string $cls = '' ) {
	if ( \st\can_edit_post() ) {
		?>
	<div class="admin-edit<?php echo esc_attr( ' ' . $cls ); ?>">
		<a href="<?php \st\the_edit_link_post(); ?>">EDIT</a>
	</div>
		<?php
	}
}

/**
 * Echo edit post link of menus when available.
 *
 * @param \wpinc\navi\Nav_Menu $nav_menu Nav_Menu to be edited.
 * @param string               $cls      CSS Class.
 */
function the_admin_edit_menu( \wpinc\navi\Nav_Menu $nav_menu, string $cls = '' ) {
	if ( \st\can_edit_theme_options() ) {
		?>
	<div class="admin-edit<?php echo esc_attr( ' ' . $cls ); ?>">
		<a href="<?php \st\the_edit_link_menu( $nav_menu ); ?>">EDIT</a>
	</div>
		<?php
	}
}


// -----------------------------------------------------------------------------


/**
 * Determines whether the current user can edit the post.
 *
 * @return bool True if user can edit post.
 */
function can_edit_post() {
	global $post;
	return $post && is_user_logged_in() && current_user_can( 'edit_post', $post->ID );
}

/**
 * Determines whether the current user can edit theme options.
 *
 * @return bool True if user can edit theme options.
 */
function can_edit_theme_options() {
	return is_user_logged_in() && current_user_can( 'edit_theme_options' );
}


// -----------------------------------------------------------------------------


/**
 * Echos edit link for post.
 */
function the_edit_link_post() {
	global $post;
	echo esc_attr( admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) );
}

/**
 * Echos edit link for menu.
 *
 * @param Nav_Menu $nav_menu Nav_Menu to edit.
 */
function the_edit_link_menu( $nav_menu ) {
	echo esc_attr( admin_url( 'nav-menus.php?action=edit&menu=' . $nav_menu->get_menu_id() ) );
}

/**
 * Echos edit link for widgets.
 */
function the_edit_link_widget() {
	echo esc_attr( admin_url( 'widgets.php' ) );
}

/**
 * Echos edit link for options.
 *
 * @param string $page_slug Page slug of option to edit.
 */
function the_edit_link_option_page( string $page_slug ) {
	echo esc_attr( admin_url( 'options-general.php?page=' . $page_slug ) );
}
