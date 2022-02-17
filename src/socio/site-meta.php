<?php
/**
 * Site Information
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2022-02-08
 */

namespace wpinc\socio;

/**
 * Sets site icon (favicon).
 *
 * @param string $dir_url The url to image directory.
 */
function set_site_icon( string $dir_url ): void {
	add_filter(
		'get_site_icon_url',
		function () use ( $dir_url ) {
			return trailingslashit( $dir_url ) . 'favicon.ico';
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Outputs the site description.
 */
function the_site_description(): void {
	echo '<meta name="description" content="' . esc_attr( get_site_description() ) . '">' . "\n";
}

/**
 * Outputs the site icon images.
 *
 * @param string $dir_url The url to image directory.
 */
function the_site_icon( string $dir_url ): void {
	$dir_url = trailingslashit( $dir_url );
	echo '<link rel="icon" href="' . esc_attr( $dir_url . 'favicon.ico' ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" href="' . esc_attr( $dir_url . 'icon-192.png' ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" type="image/png" href="' . esc_attr( $dir_url . 'icon-180.png' ) . '">' . "\n";
}


// -----------------------------------------------------------------------------


/**
 * Retrieves the title of the current page.
 *
 * @param bool   $do_append_site_name Whether the site name is appended.
 * @param string $separator           Separator between the page title and the site name.
 * @return string The title.
 */
function get_the_title( bool $do_append_site_name, string $separator ): string {
	$site_name = get_site_name();
	if ( ! is_front_page() && is_singular() ) {
		$title = _strip_custom_tags( \get_the_title() );
		if ( $do_append_site_name ) {
			$title .= $separator . $site_name;
		}
		return $title;
	} elseif ( is_archive() ) {
		$title = post_type_archive_title( '', false );
		if ( $do_append_site_name ) {
			$title .= $separator . $site_name;
		}
		return $title;
	}
	return $site_name;
}

/**
 *
 * Retrieves the website name.
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
 * Retrieves the website description.
 *
 * @return string The description of the website.
 */
function get_site_description(): string {
	$ret = get_bloginfo( 'description' );
	$ret = _strip_custom_tags( $ret );
	return $ret;
}

/**
 * Retrieves the current URL.
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
	$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];  // When reverse proxy exists.
	$req  = $_SERVER['REQUEST_URI'];
	// phpcs:enable
	return ( is_ssl() ? 'https://' : 'http://' ) . wp_unslash( $host ) . wp_unslash( $req );
}


// -----------------------------------------------------------------------------


/**
 * Strips all tags and custom 'br'.
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
