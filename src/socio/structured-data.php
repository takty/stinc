<?php
/**
 * Structured Data
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2022-01-27
 */

namespace wpinc\socio;

require_once __DIR__ . '/site-meta.php';

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
function the_structured_data( array $args = array() ): void {
	$args = array_replace_recursive(
		array(
			'@context'    => 'http://schema.org',
			'@type'       => 'WebSite',
			'url'         => home_url(),
			'name'        => \wpinc\socio\get_site_name(),
			'in_language' => get_locale(),
			'description' => \wpinc\socio\get_site_description(),
			'same_as'     => array(),
			'publisher'   => array(
				'@type' => 'Organization',
				'name'  => \wpinc\socio\get_site_name(),
				'logo'  => '',
			),
		),
		$args
	);
	if ( isset( $args['logo'] ) ) {
		$args['publisher']['logo'] = $args['logo'];
		unset( $args['logo'] );
	}
	$args = _rearrange_array( $args );
	$json = wp_json_encode( $args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG );
	echo '<script type="application/ld+json">' . "\n$json\n" . '</script>' . "\n";  // phpcs:ignore

	if ( isset( $args['publisher']['logo'] ) && class_exists( 'Simply_Static\Plugin' ) ) {
		echo '<link href="' . esc_attr( $args['publisher']['logo'] ) . '"><!-- for simply static -->' . "\n";
	}
}

/**
 * Removes empty entries from an array and change key from camel case to snake case.
 *
 * @access private
 *
 * @param array $array An array.
 * @return array Filtered array.
 */
function _rearrange_array( array $array ): array {
	$ret = array();
	foreach ( $array as $key => $val ) {
		if ( is_array( $val ) ) {
			$val = _rearrange_array( $val );
		}
		if ( ! empty( $val ) ) {
			if ( is_int( $key ) ) {
				$ret[] = $val;
			} else {
				$key = lcfirst( strtr( ucwords( strtr( $key, '_', ' ' ) ), ' ', '' ) );

				$ret[ $key ] = $val;
			}
		}
	}
	return $ret;
}
