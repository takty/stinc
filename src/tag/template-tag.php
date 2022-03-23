<?php
namespace st;
/**
 *
 * Custom Template Tags
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-03-23
 *
 */


require_once __DIR__ . '/../util/text.php';
require_once __DIR__ . '/../util/url.php';
require_once __DIR__ . '/../util/query.php';
require_once __DIR__ . '/loop.php';


const TMK_NAME_S = '_name_s';


function has_content( $str = false ) {
	if ( $str === false ) $str = get_the_content();
	$str = strip_tags( $str, '<img><hr><br><iframe>' );
	$str = str_replace( '&nbsp;', '', $str );
	return ! empty( trim( $str ) );
}

function is_content_empty( $str = false ) {
	return ! has_content( $str );
}

function get_post_title( $short = 8, $long = 32, $mode = 'segment_small', $filter = 'esc_html' ) {
	global $post;
	if ( $post ) {
		$title = get_the_title( $post->ID );
	} else {
		$title = is_404() ? __( 'Page not found' ) : __( 'Nothing found' );
	}
	$len = mb_strlen( $title );
	$option = '';
	if ( $long <= $len ) $option = ' data-length="long"';
	if ( $len <= $short )  $option = ' data-length="short"';
	$title = \st\separate_line( $title, $mode, $filter );
	return compact( 'title', 'option' );
}

function get_post_type_name( $post_type, $singular_name = false ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		return $ml->get_post_type_name( $post_type, $singular_name );
	}
	$obj = get_post_type_object( $post_type );
	return $singular_name ? $obj->labels->singular_name : $obj->labels->name;
}

function get_site_title( $raw = false ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		return $ml->get_site_title( $raw );
	}
	$ret = [];
	$bn = htmlspecialchars_decode( get_option( 'blogname' ) );
	$bd = htmlspecialchars_decode( get_option( 'blogdescription' ) );
	$ret['name']        = $raw ? $bn : \st\separate_line( $bn, 'segment' );
	$ret['description'] = $raw ? $bd : \st\separate_line( $bd, 'segment' );
	return $ret;
}


// -----------------------------------------------------------------------------


function the_sub_title( $meta_key, $post_id = false ) {
	echo get_the_sub_title( $meta_key, $post_id );
}

function get_the_sub_title( $meta_key, $post_id = false ) {
	global $post;
	if ( $post_id === false ) $post_id = $post->ID;
	$title = get_post_meta( $post_id, $meta_key, true );
	$title = \st\esc_text_with_br( $title );
	return $title;
}

function the_sub_content( $meta_key, $post_id = false ) {
	$content = get_the_sub_content( $meta_key, $post_id );
	echo_content( $content );
}

function get_the_sub_content( $meta_key, $post_id = false ) {
	global $post;
	if ( $post_id === false ) $post_id = $post->ID;
	$content = get_post_meta( $post_id, $meta_key, true );
	return $content;
}

function the_content( $post_id ) {
	$post = get_post( $post_id );
	echo_content( $post->post_content );
}

function echo_content( $content ) {
	$content = apply_filters( 'the_content', $content );  // Shortcodes are expanded here.
	$content = str_replace( ']]>', ']]&gt;', $content );
	echo $content;
}

function the_mb_excerpt( $count = 160 ) {
	$orig = \st\remove_continuous_spaces( get_the_excerpt() );
	$text = \st\mb_trim( mb_strimwidth( $orig, 0, $count ) );
	if ( ! empty( $text ) && $orig !== $text ) $text = esc_html( $text ) . '...';
	echo $text;
}


// -----------------------------------------------------------------------------


function the_post_list_item( $post, $link_class = '', $item_class = '', $current = false ) {
	$link = esc_url( get_permalink( $post->ID ) );
	$title = esc_html( get_the_title( $post->ID ) );

	$item_class = empty( $item_class ) ? $current : ($item_class . ' ' . $current);
	$li_class = ( $item_class ) ? ' class="' . $item_class . '"' : '';
	if ( empty( $link_class ) ) {
		echo "<li$li_class><a href=\"$link\">$title</a></li>";
	} else {
		echo "<li$li_class><a class=\"$link_class\" href=\"$link\">$title</a></li>";
	}
}

function the_child_page_list( $before = '<ul>', $after = '</ul>', $link_class = '' ) {
	$ps = get_child_pages();
	if ( count( $ps ) === 0 ) return;

	echo $before;
	foreach ( $ps as $p ) the_post_list_item( $p, $link_class );
	echo $after;
}

function the_sibling_page_list( $before = '<ul>', $after = '</ul>', $link_class = '' ) {
	$ps = get_sibling_pages();
	if ( count( $ps ) === 0 ) return;
	global $post;

	echo $before;
	foreach ( $ps as $p ) the_post_list_item( $p, $link_class, '', $post->ID === $p->ID ? 'current' : false );
	echo $after;
}

function the_yearly_post_list( $post_type, $year_before = '<h3>', $year_after = '</h3>', $list_before = '<ul>', $list_after = '</ul>', $is_fiscal_year = false ) {
	$ps = get_posts( [
		'posts_per_page' => -1,
		'post_type'      => $post_type,
		'orderby'        => 'date',
		'order'          => 'desc',
	] );
	$year = -1;
	if ( count( $ps ) === 0 ) return;
	foreach ( $ps as $p ) {
		$y = intval( get_the_date( 'Y', $p->ID ) );
		if ( $is_fiscal_year ) {
			$m = intval( get_the_date( 'm', $p->ID ) );
			if ( $m <= 3 ) $y -= 1;
		}
		if ( $y !== $year ) {
			if ( $year !== -1 ) echo $list_after;
			$year = $y;
			echo $year_before . $year . $year_after;
			echo $list_before;
		}
		the_post_list_item( $p );
	}
	if ( $year !== -1 ) echo $list_after;
}


// -----------------------------------------------------------------------------


function get_term_name( $term, $singular = false, $lang = false ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		return $ml->get_term_name( $term, $singular, $lang );
	}
	if ( $singular ) {
		$name_s = get_term_meta( $term->term_id, TMK_NAME_S, true );
		if ( ! empty( $name_s ) ) return $name_s;
	}
	return $term->name;
}

function term_description( $term_id = 0, $taxonomy, $lang = false ) {
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		return $ml->term_description( $term_id, $taxonomy, $lang );
	}
	if ( ! $term_id && ( is_tax() || is_tag() || is_category() ) ) {
		$t = get_queried_object();
		$term_id  = $t->term_id;
		$taxonomy = $t->taxonomy;
	}
	return \term_description( $term_id, $taxonomy );
}

function get_term_list( $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
	$ts = empty( $args ) ? get_terms( $taxonomy ) : get_terms( $taxonomy, $args );
	if ( is_wp_error( $ts ) ) return $ts;
	if ( empty( $ts ) ) return false;

	global $wp_query;
	$cur = $wp_query->queried_object;
	if ( ! ( $cur instanceof WP_Term ) && ! ( is_object( $cur ) && property_exists( $cur, 'term_id' ) ) ) {
		$cur = false;
	}
	$singular = isset( $args['singular'] ) ? $args['singular']  : false;
	$filter = isset( $args['filter'] ) ? $args['filter'] : 'esc_html';
	return create_term_list( $ts, $taxonomy, $before, $sep, $after, $add_link, $cur, $singular, $filter );
}

function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
	$ts = get_the_terms( $post_id, $taxonomy );
	if ( is_wp_error( $ts ) ) return $ts;
	if ( empty( $ts ) ) return false;

	$singular = isset( $args['singular'] ) ? $args['singular'] : false;
	if ( isset( $args['is_root_inserted'] ) && ( $args['is_root_inserted'] === true ) ) {
		$ts = _insert_root( $ts );
	}
	$filter = isset( $args['filter'] ) ? $args['filter'] : 'esc_html';
	return create_term_list( $ts, $taxonomy, $before, $sep, $after, $add_link, false, $singular, $filter );
}

function _insert_root( $terms ) {
	$p_ts = [];
	$added = [];
	foreach ( $terms as $t ) {
		if ( $t->parent !== 0 ) {
			list( $p ) = \st\taxonomy\get_term_root( $t, 0 );
			if ( ! isset( $added[ $p->term_id ] ) ) {
				$p_ts[ $p->term_id ] = [ $p ];
				$added[ $p->term_id ] = true;
			}
			if ( ! isset( $added[ $t->term_id ] ) ) {
				$p_ts[ $p->term_id ][] = $t;
				$added[ $t->term_id ] = true;
			}
		} else {
			if ( ! isset( $added[ $t->term_id ] ) ) {
				$p_ts[ $t->term_id ] = [ $t ];
				$added[ $t->term_id ] = true;
			}
		}
	}
	$new_ts = [];
	if ( ! empty( $terms ) && class_exists( '\st\OrderedTerm' ) ) {
		$ot = \st\OrderedTerm::get_instance();
		$ps = $ot->sort_terms( array_keys( $p_ts ), $terms[0]->taxonomy );
		foreach ( $ps as $p ) {
			$ts = $p_ts[ $p ];
			foreach ( $ts as $t ) $new_ts[] = $t;
		}
	} else {
		foreach ( $p_ts as $p => $ts ) {
			foreach ( $ts as $t ) $new_ts[] = $t;
		}
	}
	return $new_ts;
}

function create_term_list( $terms, $taxonomy, $before, $sep, $after, $add_link, $current_term = false, $singular = false, $filter = 'esc_html' ) {
	$links = [];
	foreach ( $terms as $t ) {
		$cs = [ "$taxonomy-{$t->slug}" ];
		if ( $t->parent === 0 ) $cs[] = 'root';
		if ( $t->count  === 0 ) $cs[] = 'empty';
		if ( $current_term && $current_term->term_id === $t->term_id ) $cs[] = 'current';
		$cs_str = implode( ' ', $cs );

		$_name = $filter( get_term_name( $t, $singular ) );
		if ( $add_link ) {
			$link = get_term_link( $t, $taxonomy );
			if ( is_wp_error( $link ) ) return $link;
			$_link = esc_url( $link );
			$links[] = "<a href=\"$_link\" rel=\"tag\" class=\"$cs_str\">$_name</a>";
		} else {
			$links[] = "<span class=\"$cs_str\">$_name</span>";
		}
	}
	$term_links = apply_filters( "term_links-{$taxonomy}", $links );
	return $before . join( $sep, $term_links ) . $after;
}

function get_term_names( $taxonomy, $singular = false, $lang = false, $args = [] ) {
	$ts = empty( $args ) ? get_terms( $taxonomy ) : get_terms( $taxonomy, $args );
	if ( is_wp_error( $ts ) ) return $ts;
	if ( empty( $ts ) ) return false;

	return array_map( function ( $t ) use ( $singular, $lang ) {
		return get_term_name( $t, $singular, $lang );
	}, $ts );
}

function get_the_term_names( $post_id = 0, $taxonomy, $singular = false, $lang = false ) {
	$ts = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $ts ) ) return false;

	return array_map( function ( $t ) use ( $singular, $lang ) {
		return get_term_name( $t, $singular, $lang );
	}, $ts );
}


// -----------------------------------------------------------------------------


function expand_post_entries( $slug, $name, $key ) {
	$ids = _get_section_post_ids( $key );
	$ps = \st\get_pages_by_ids( $ids );
	\st\the_loop_posts_with_custom_page_template( $slug, $name, $ps );
}

function _get_section_post_ids( $key ) {
	global $post;
	$sps = \st\link_picker\get_items( $key, $post->ID );
	return array_map( function ( $e ) { return isset( $e['post_id'] ) ? intval( $e['post_id'] ) : 0; }, $sps );
}
