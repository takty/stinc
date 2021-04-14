<?php
/**
 * Share Links of Social Media
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2021-04-13
 */

namespace wpinc\socio\share_link;

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
	$args += array(
		'before'              => '<ul>',
		'after'               => '</ul>',
		'before_link'         => '<li>',
		'after_link'          => '</li>',
		'do_append_site_name' => true,
		'separator'           => ' - ',
		'media'               => array( 'facebook', 'twitter', 'pocket', 'line', 'copy' ),
	);
	$title = \wpinc\socio\site_meta\get_the_title( $args['do_append_site_name'], $args['separator'] );
	$url   = \wpinc\socio\site_meta\get_current_url();

	$search  = array( '<T>', '<U>' );
	$replace = array( rawurlencode( $title ), rawurlencode( $url ) );

	$ret = '';
	foreach ( $args['media'] as $lab => $media ) {
		$href = SOCIAL_MEDIA_LINKS[ $media ] ?? '';
		if ( ! empty( $href ) ) {
			$href = str_replace( $search, $replace, $href );
			$lab  = is_string( $lab ) ? $lab : ucfirst( $media );
			$link = '<a href="' . esc_url( $href ) . '">' . $lab . '</a>';
			$ret .= $args['before_link'] . $link . $args['after_link'] . "\n";
		} elseif ( 'copy' === $media ) {
			$lab  = is_string( $lab ) ? $lab : ucfirst( $media );
			$link = sprintf( '<a data-url="%s" data-title="%s" onclick="%s">%s</a>', esc_url( $url ), esc_attr( $title ), ON_CLICK_JS, $lab );
			$ret .= $args['before_link'] . $link . $args['after_link'] . "\n";
		}
	}
	$tags = wp_kses_allowed_html( 'post' );

	$tags['a']['onclick'] = true;
	echo wp_kses( $args['before'] . "\n$ret" . $args['after'] . "\n", $tags );
}
