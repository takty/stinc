<?php
/**
 * Social Media
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-29
 */

namespace st;

require_once __DIR__ . '/social/analytics.php';
require_once __DIR__ . '/social/open-graph-protocol.php';
require_once __DIR__ . '/social/share-link.php';
require_once __DIR__ . '/social/site-meta.php';
require_once __DIR__ . '/social/structured-data.php';

/**
 * Output google analytics code.
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function the_google_analytics_code( string $tracking = '', string $verification = '' ) {
	\wpinc\social\analytics\the_google_analytics_code( $tracking, $verification );
}

/**
 * Output the open graph protocol meta tags.
 *
 * @param array $args {
 *     Options.
 *
 *     @type string $default_image_url     Default image URL.
 *     @type bool   $is_site_name_appended (Optional) Whether the site name is appended.
 *     @type string $separator             (Optional) Separator between the page title and the site name.
 *     @type int    $excerpt_length        (Optional) The length of excerpt.
 *     @type string $alt_description       (Optional) Alternative description.
 *     @type string $image_size            (Optional) The image size.
 *     @type string $image_meta_key        (Optional) Meta key of image.
 *     @type string $alt_image_url         (Optional) Alternative image URL.
 * }
 */
function the_ogp( array $args = array() ) {
	\wpinc\social\ogp\the_ogp( $args );
}

/**
 * Output share links.
 *
 * @param array $args {
 *     Default post navigation arguments.
 *
 *     @type string   $before                (Optional) Markup to prepend to the all links.
 *     @type string   $after                 (Optional) Markup to append to the all links.
 *     @type string   $before_link           (Optional) Markup to prepend to each link.
 *     @type string   $after_link            (Optional) Markup to append to each link.
 *     @type bool     $is_site_name_appended (Optional) Whether the site name is appended.
 *     @type string   $separator             (Optional) Separator between the page title and the site name.
 *     @type string[] $media                 (Optional) Social media names.
 * }
 */
function the_share_links( array $args = array() ) {
	\wpinc\social\share_link\the_share_links( $args );
}

/**
 * Output the site description.
 */
function the_site_description() {
	\wpinc\social\site_meta\the_site_description();
}

/**
 * Output the site icon images.
 *
 * @param string $dir_url The url to image directory.
 */
function the_site_icon( string $dir_url ) {
	\wpinc\social\site_meta\the_site_icon( $dir_url );
}

/**
 * Output the structured data.
 *
 * @param array $args {
 *     The data of the website.
 *
 *     @type string   $url         (Optional) The URL.
 *     @type string   $name        (Optional) The name.
 *     @type string   $inLanguage  (Optional) The locale.
 *     @type string   $description (Optional) The description.
 *     @type string[] $sameAs      (Optional) An array of URLs.
 *     @type string   $logo        The URL of the logo image.
 *     @type string[] $publisher {
 *         @type string $name (Optional) The name of the publisher.
 *     }
 * }
 */
function the_structured_data( array $args = array() ) {
	\wpinc\social\structured_data\the_structured_data( $args );
}
