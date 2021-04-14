<?php
/**
 * Social Media
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-04-14
 */

namespace st;

require_once __DIR__ . '/socio/analytics.php';
require_once __DIR__ . '/socio/open-graph-protocol.php';
require_once __DIR__ . '/socio/share-link.php';
require_once __DIR__ . '/socio/site-meta.php';
require_once __DIR__ . '/socio/structured-data.php';

/**
 * Outputs google analytics code.
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function the_google_analytics_code( string $tracking = '', string $verification = '' ) {
	\wpinc\socio\analytics\the_google_analytics_code( $tracking, $verification );
}

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
	\wpinc\socio\ogp\the_ogp( $args );
}

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
	\wpinc\socio\share_link\the_share_links( $args );
}

/**
 * Outputs the site description.
 */
function the_site_description() {
	\wpinc\socio\site_meta\the_site_description();
}

/**
 * Outputs the site icon images.
 *
 * @param string $dir_url The url to image directory.
 */
function the_site_icon( string $dir_url ) {
	\wpinc\socio\site_meta\the_site_icon( $dir_url );
}

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
	\wpinc\socio\structured_data\the_structured_data( $args );
}
