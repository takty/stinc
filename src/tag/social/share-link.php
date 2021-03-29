<?php
/**
 * Share Links of Social Media
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-29
 */

namespace wpinc\social\share_link;

require_once __DIR__ . '/site-meta.php';

/**
 * The templates of social media sharing links.
 */
define(
	'SOCIAL_MEDIA_LINKS',
	array(
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=<U>&amp;t=<T>',
		'twitter'  => 'https://twitter.com/intent/tweet?url=<U>&amp;text=<T>',
		'pocket'   => 'https://getpocket.com/edit?url=<U>&title=<T>',
		'line'     => 'https://line.me/R/msg/text/?<T>%0d%0a<U>',
	)
);

/**
 * The script for 'copy' function.
 */
const ON_CLICK_JS = "navigator.clipboard.writeText(this.dataset.title + ' ' + this.dataset.url);this.classList.add('copied');";

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
	$args += array(
		'before'                => '<ul>',
		'after'                 => '</ul>',
		'before_link'           => '<li>',
		'after_link'            => '</li>',
		'is_site_name_appended' => true,
		'separator'             => ' - ',
		'media'                 => array( 'facebook', 'twitter', 'pocket', 'line', 'copy' ),
	);
	$title = \wpinc\social\site_meta\get_the_title( $args['is_site_name_appended'], $args['separator'] );
	$url   = \wpinc\social\site_meta\get_current_url();

	$search  = array( '<U>', '<T>' );
	$replace = array( rawurlencode( $title ), rawurlencode( $url ) );

	$ret = '';
	foreach ( $args['media'] as $lab => $media ) {
		$href = SOCIAL_MEDIA_LINKS[ $media ] ?? '';
		if ( ! empty( $href ) ) {
			$href = str_replace( $search, $replace, $href );
			$lab  = is_string( $lab ) ? $lab : ucfirst( $media );
			$link = '<a href="' . esc_url( $href ) . '">' . $lab . '</a>';
			$ret .= $args['before_link'] . $link . $args['after_link'];
		} elseif ( 'copy' === $media ) {
			$lab  = is_string( $lab ) ? $lab : ucfirst( $media );
			$link = sprintf( '<a data-url="%s" data-title="%s" onclick="%s">%s</a>', esc_url( $url ), esc_attr( $title ), esc_attr( ON_CLICK_JS ), $lab );
			$ret .= $args['before_link'] . $link . $args['after_link'];
		}
	}
	$tags = wp_kses_allowed_html( 'post' );

	$tags['a']['onclick'] = true;
	echo wp_kses( $args['before'] . $ret . $args['after'], $tags );
}
