<?php
/**
 * Social Media
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-10-28
 */

namespace st;

require_once __DIR__ . '/socio/assets/url.php';
require_once __DIR__ . '/socio/analytics.php';
require_once __DIR__ . '/socio/open-graph-protocol.php';
require_once __DIR__ . '/socio/share-link.php';
require_once __DIR__ . '/socio/site-meta.php';
require_once __DIR__ . '/socio/structured-data.php';

/**
 * Outputs google analytics code.
 *
 * @param array  $args {
 *     Arguments.
 *
 *     @type string 'url_to'            URL to this script.
 *     @type string 'site_verification' The site verification code.
 *     @type string 'tag_id'            The google tag ID.
 *     @type bool   'do_show_dialog'    Whether to show the dialog.
 *     @type int    'expired_day'       The length of keeping settings.
 *     @type string 'id_dialog'         Element ID of the dialog. Defaults 'wpinc-socio-analytics-dialog'.
 *     @type string 'id_accept'         Element ID of the accept button. Defaults 'wpinc-socio-analytics-accept'.
 *     @type string 'id_reject'         Element ID of the reject button. Defaults 'wpinc-socio-analytics-reject'.
 * }
 * @param string $site_ver (Optional) The site verification code.
 */
function the_google_analytics_code( $args = array(), ?string $site_ver = null ): void {
	\wpinc\socio\the_google_analytics_code( $args, $site_ver );
}


// -----------------------------------------------------------------------------


/**
 * Outputs the open graph protocol meta tags.
 *
 * @param array $args {
 *     (Optional) Options.
 *
 *     @type string 'default_image_url'   Default image URL.
 *     @type bool   'do_append_site_name' Whether the site name is appended.
 *     @type string 'separator'           Separator between the page title and the site name.
 *     @type int    'excerpt_length'      The length of excerpt.
 *     @type string 'alt_description'     Alternative description.
 *     @type string 'image_size'          The image size.
 *     @type string 'image_meta_key'      Meta key of image.
 *     @type string 'alt_image_url'       Alternative image URL.
 * }
 */
function the_ogp( array $args = array() ) {
	\wpinc\socio\the_ogp( $args );
}


// -----------------------------------------------------------------------------


/**
 * Outputs share links.
 *
 * @param array $args {
 *     (Optional) Post navigation arguments.
 *
 *     @type string   'before'              Markup to prepend to the all links.
 *     @type string   'after'               Markup to append to the all links.
 *     @type string   'before_link'         Markup to prepend to each link.
 *     @type string   'after_link'          Markup to append to each link.
 *     @type bool     'do_append_site_name' Whether the site name is appended.
 *     @type string   'separator'           Separator between the page title and the site name.
 *     @type string[] 'media'               Social media names.
 * }
 */
function the_share_links( array $args = array() ) {
	\wpinc\socio\the_share_links( $args );
}


// -----------------------------------------------------------------------------


/**
 * Sets site icon (favicon).
 *
 * @param string     $dir_url The url to image directory.
 * @param array|null $icons   Array of icon sizes to icon file names.
 */
function set_site_icon( string $dir_url, ?array $icons = null ): void {
	\wpinc\socio\set_site_icon( $dir_url, $icons );
}

/**
 * Outputs the site description.
 */
function the_site_description(): void {
	\wpinc\socio\the_site_description();
}

/**
 * Retrieves the title of the current page.
 *
 * @param bool   $do_append_site_name Whether the site name is appended.
 * @param string $separator           Separator between the page title and the site name.
 * @return string The title.
 */
function get_the_title( bool $do_append_site_name, string $separator ): string {
	return \wpinc\socio\get_the_title( $do_append_site_name, $separator );
}

/**
 *
 * Retrieves the website name.
 *
 * @return string The name of the website.
 */
function get_site_name(): string {
	return \wpinc\socio\get_site_name();
}

/**
 *
 * Retrieves the website description.
 *
 * @return string The description of the website.
 */
function get_site_description(): string {
	return \wpinc\socio\get_site_description();
}

if ( ! function_exists( '\st\get_current_url' ) ) {
	/**
	 * Retrieves the current URL.
	 *
	 * @return string The current URL.
	 */
	function get_current_url(): string {
		return \wpinc\get_current_url();
	}
}


// -----------------------------------------------------------------------------


/**
 * Outputs the structured data.
 *
 * @param array $args {
 *     (Optional) The data of the website.
 *
 *     @type string   'url'         The URL.
 *     @type string   'name'        The name.
 *     @type string   'in_language' The locale.
 *     @type string   'description' The description.
 *     @type string[] 'same_as'     An array of URLs.
 *     @type string   'logo'        The URL of the logo image.
 *     @type string[] 'publisher' {
 *         @type string $name The name of the publisher.
 *     }
 * }
 */
function the_structured_data( array $args = array() ) {
	\wpinc\socio\the_structured_data( $args );
}
