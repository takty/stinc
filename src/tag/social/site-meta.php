<?php
/**
 * Site Information
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-29
 */

namespace wpinc\social\site_meta;

/**
 * Output the site description.
 */
function the_site_description() {
	echo '<meta name="description" content="' . esc_attr( get_site_description() ) . '">' . "\n";
}

/**
 * Output the site icon images.
 *
 * @param string $dir_url The url to image directory.
 */
function the_site_icon( string $dir_url ) {
	$dir_url = trailingslashit( $dir_url );
	echo '<link rel="icon" href="' . esc_attr( $dir_url . 'favicon.ico' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" href="' . esc_attr( $dir_url . 'icon-192.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" type="image/png" href="' . esc_attr( $dir_url . 'icon-180.png' ) . '">' . "\n";
}


// -----------------------------------------------------------------------------


/**
 * Retrieve the title of the current page.
 *
 * @param bool   $is_site_name_appended Whether the site name is appended.
 * @param string $separator             Separator between the page title and the site name.
 * @return string The title.
 */
function get_the_title( bool $is_site_name_appended, string $separator ): string {
	$site_name = get_site_name();
	if ( ! is_front_page() && is_singular() ) {
		$title = _strip_custom_tags( \get_the_title() );
		if ( $is_site_name_appended ) {
			$title .= $separator . $site_name;
		}
		return $title;
	} elseif ( is_archive() ) {
		$title = post_type_archive_title( '', false );
		if ( $is_site_name_appended ) {
			$title .= $separator . $site_name;
		}
		return $title;
	}
	return $site_name;
}
/**
 *
 * Retrieve the website name.
 *
 * @return string The name of the website.
 */
function get_site_name(): string {
	$ret = get_bloginfo( 'name' );
	$ret = _strip_custom_tags( $ret );
	return $ret;
}

/**
 *
 * Retrieve the website description.
 *
 * @return string The description of the website.
 */
function get_site_description(): string {
	$ret = get_bloginfo( 'description' );
	$ret = _strip_custom_tags( $ret );
	return $ret;
}

/**
 * Retrieve the current URL.
 *
 * @return string The current URL.
 */
function get_current_url(): string {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( ! isset( $_SERVER['HTTP_HOST'] ) || ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return home_url();
	}
	// phpcs:disable
	if ( isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ) {  // When reverse proxy exists.
		$host = wp_unslash( $_SERVER['HTTP_X_FORWARDED_HOST'] );
	} else {
		$host = wp_unslash( $_SERVER['HTTP_HOST'] );
	}
	$path = wp_unslash( $_SERVER['REQUEST_URI'] );
	// phpcs:enable
	return ( is_ssl() ? 'https://' : 'http://' ) . $host . $path;
}


// -----------------------------------------------------------------------------


/**
 * Strip all tags and custom 'br'.
 *
 * @access private
 *
 * @param string $text The text.
 * @return string The stripped text.
 */
function _strip_custom_tags( string $text ): string {
	// Replace double full-width spaces and br tags to single space.
	$text = preg_replace( '/　　|<\s*br\s*\/?>/ui', ' ', $text );
	$text = wp_strip_all_tags( $text, true );
	return $text;
}
