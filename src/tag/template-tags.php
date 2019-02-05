<?php
namespace st;

/**
 *
 * Custom Template Tags
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-05
 *
 */


require_once __DIR__ . '/text.php';
require_once __DIR__ . '/url.php';


function is_content_empty( $str = false ) {
	if ( $str === false ) {
		$str = get_the_content();
	}
	return trim( str_replace( '&nbsp;', '', strip_tags( $str, '<img><hr><br>' ) ) ) === '';
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


// -----------------------------------------------------------------------------


function the_loop_posts( $slug, $name = '', $ps ) {
	global $post;
	foreach ( $ps as $post ) {
		setup_postdata( $post );
		get_template_part( $slug, $name );
	}
	wp_reset_postdata();
}

function the_loop_posts_with_custom_page_template( $slug, $name = '', $ps ) {
	global $post;
	foreach ( $ps as $post ) {
		setup_postdata( $post );

		$pt = get_post_meta( $post->ID, '_wp_page_template', TRUE );
		if ( ! empty( $pt ) && $pt !== 'default' ) {
			get_template_part( $slug, basename( $pt, '.php' ) );
		} else {
			get_template_part( $slug, $name );
		}
	}
	wp_reset_postdata();
}

function the_loop_query( $slug, $name = '', $args, $opts = [] ) {
	$ps = get_posts( $args );
	if ( isset( $opts['has_post_thumbnail'] ) ) {
		$hpt = $opts['has_post_thumbnail'];
		$ps = array_values( array_filter( $ps, function ( $p ) use ( $hpt ) {
			return $hpt === has_post_thumbnail( $p->ID );
		} ) );
	}
	if ( count( $ps ) === 0 ) return;
	the_loop_posts( $slug, $name, $ps );
}

function the_loop_of_child_pages( $slug, $name, $args = [], $opts = [] ) {
	$args += [
		'posts_per_page' => -1,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'asc',
		'post_parent' => get_the_ID()
	];
	the_loop_query( $slug, $name, $args, $opts );
}

function the_loop_of_posts( $slug, $name = '', $args = [], $opts = [] ) {
	$args += [
		'post_type' => 'post',
		'posts_per_page' => -1,
		'order' => 'asc',
	];
	the_loop_query( $slug, $name, $args, $opts );
}


// -----------------------------------------------------------------------------


function the_sub_title( $meta_key, $post_id = false ) {
	echo get_the_sub_title( $meta_key, $post_id );
}

function get_the_sub_title( $meta_key, $post_id = false ) {
	global $post;
	if ( $post_id === false ) $post_id = $post->ID;
	$title = get_post_meta( $post_id, $meta_key, true );
	$title = \st\esc_html_br( $title );
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


// -----------------------------------------------------------------------------


function get_sticky_posts( $term_slug, $taxonomy ) {
	$t = get_term_by( 'slug', $term_slug, $taxonomy );
	$stickies = get_option( 'sticky_posts' );
	if ( count( $stickies ) === 0 ) return [];

	return get_posts( [
		'posts_per_page' => 4,
		'category' => $t->slug,
		'post__in'  => $stickies,
		'ignore_sticky_posts' => 1,
	] );
}

function get_child_pages( $parent_id = false ) {
	if ( $parent_id === false ) $parent_id = get_the_ID();
	return get_posts( [
		'posts_per_page' => -1,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'asc',
		'post_parent' => $parent_id
	] );
}

function get_sibling_pages( $sibling_id = false ) {
	$post = null;
	if ( $sibling_id === false ) {
		$post = get_post();
	} else {
		$post = get_post( $sibling_id );
	}
	$parent_id = ! empty( $post ) ? $post->post_parent : 0;
	return get_posts( [
		'posts_per_page' => -1,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'asc',
		'post_parent' => $parent_id
	] );
}

function get_pages_by_ids( $ids ) {
	$ps = get_posts( [
		'posts_per_page' => -1,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'asc',
		'post__in' => $ids
	] );
	$id2p = [];
	foreach ( $ps as $p ) {
		$id2p[ $p->ID ] = $p;
	}
	$ret = [];
	foreach ( $ids as $id ) {
		if ( isset( $id2p[ $id ] ) ) $ret[] = $id2p[ $id ];
	}
	return $ret;
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

function the_child_page_list( $before = '<ul>', $after = '</ul>', $link_class = '', $dummy_item_num = 0 ) {
	$ps = get_child_pages();
	if ( count( $ps ) === 0 ) return;

	echo $before;
	foreach ( $ps as $p ) the_post_list_item( $p, $link_class );
	for ( $i = 0; $i < $dummy_item_num; $i += 1 ) echo "<li></li>";
	echo $after;
}

function the_sibling_page_list( $before = '<ul>', $after = '</ul>', $link_class = '', $dummy_item_num = 0 ) {
	$ps = get_sibling_pages();
	if ( count( $ps ) === 0 ) return;
	global $post;

	echo $before;
	foreach ( $ps as $p ) the_post_list_item( $p, $link_class, '', $post->ID === $p->ID ? 'current' : false );
	for ( $i = 0; $i < $dummy_item_num; $i += 1 ) echo "<li></li>";
	echo $after;
}

function the_yearly_post_list( $post_type, $year_before = '<h3>', $year_after = '</h3>', $list_before = '<ul>', $list_after = '</ul>', $is_fiscal_year = false ) {
	$ps = get_posts( [
		'posts_per_page' => -1,
		'post_type' => $post_type,
		'orderby' => 'date',
		'order' => 'desc',
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


function the_yearly_archive_select( $post_type = 'post', $default_title = 'YEAR', $args = [], $meta_key = false ) {
	$args = array_merge( [
		'type' => 'yearly', 'format' => 'option', 'post_type' => $post_type
	], $args );
?>
	<select onchange="document.location.href = this.value;">
		<option value="#"><?php echo $default_title ?></option>
<?php
	if ( $meta_key === false ) {
		wp_get_archives( $args );
	} else {
		\st\post_type\get_custom_archives( $meta_key, $args );
	}
?>
	</select>
<?php
}

function the_taxonomy_archive_select( $taxonomy, $default_title = 'CATEGORY', $check_lang_visible = false ) {
	$key_visible = '_visible';
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		$key_visible .= '_' . $ml->get_site_lang();
	}
	$terms = get_terms( $taxonomy, [ 'hide_empty' => false, 'parent' => 0 ] );
?>
		<select onchange="document.location.href = this.value;">
			<option value="#"><?php echo $default_title ?></option>
<?php
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		foreach ( $terms as $t ) {
			if ( $check_lang_visible && empty( get_term_meta( $t->term_id, $key_visible, true ) ) ) continue;
			echo '<option value="' . esc_attr( get_term_link( $t ) ) . '">' . esc_html( $ml->get_term_name( $t ) ) . '</option>';

			$cts = get_terms( $taxonomy, [ 'hide_empty' => false, 'parent' => $t->term_id ] );
			foreach ( $cts as $ct ) {
				echo '<option value="' . esc_attr( get_term_link( $ct ) ) . '">' . '— ' . esc_html( $ml->get_term_name( $ct ) ) . '</option>';
			}

		}
	} else {
		foreach ( $terms as $t ) {
			if ( $check_lang_visible && empty( get_term_meta( $t->term_id, $key_visible, true ) ) ) continue;
			echo '<option value="' . esc_attr( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</option>';

			$cts = get_terms( $taxonomy, [ 'hide_empty' => false, 'parent' => $t->term_id ] );
			foreach ( $cts as $ct ) {
				echo '<option value="' . esc_attr( get_term_link( $ct ) ) . '">' . '— ' . esc_html( $ct->name ) . '</option>';
			}

		}
	}
?>
		</select>
<?php
}


// -----------------------------------------------------------------------------


function the_post_navigation_with_list_link( $args = [] ) {
	echo get_the_post_navigation_with_list_link( $args );
}

function get_the_post_navigation_with_list_link( $args = [] ) {
	$args = wp_parse_args( $args, [ 'has_list_link' => true ] );
	return get_the_post_navigation( $args );
}

function the_post_navigation( $args = [] ) {
	echo get_the_post_navigation( $args );
}

function get_the_post_navigation( $args = [] ) {
	$args = wp_parse_args( $args, [
		'prev_text'          => '%title',
		'next_text'          => '%title',
		'list_text'          => 'List',
		'in_same_term'       => false,
		'excluded_terms'     => '',
		'taxonomy'           => 'category',
		'screen_reader_text' => __( 'Post navigation' ),
		'has_list_link'      => false,
	] );
	$prev = get_previous_post_link(
		'<div class="nav-previous">%link</div>',
		$args['prev_text'],
		$args['in_same_term'],
		$args['excluded_terms'],
		$args['taxonomy']
	);
	if ( ! $prev ) {
		$str = $args['prev_text'][0] === '%' ? '&nbsp;' : $args['prev_text'];
		$prev = '<div class="nav-previous disabled"><a>' . $str . '</a></div>';
	}
	$next = get_next_post_link(
		'<div class="nav-next">%link</div>',
		$args['next_text'],
		$args['in_same_term'],
		$args['excluded_terms'],
		$args['taxonomy']
	);
	if ( ! $next ) {
		$str = $args['next_text'][0] === '%' ? '&nbsp;' : $args['next_text'];
		$next = '<div class="nav-next disabled"><a>' . $str . '</a></div>';
	}
	$list = '';
	if ( $args['has_list_link'] ) {
		global $post;
		$list = '<div class="nav-list"><a href="' . esc_url( get_post_type_archive_link( $post->post_type ) ) . '">' . $args['list_text'] . '</a></div>';
	}
	$navigation = '';
	if ( $prev || $next ) {
		$navigation = _navigation_markup( $prev . $list . $next, 'post-navigation', $args['screen_reader_text'] );
	}
	return $navigation;
}

function add_archive_current_class() {
	add_filter( 'get_archives_link', function ( $link_html ) {
		$regex = '/^\t<(link |option |li>)/';
		if ( preg_match( $regex, $link_html, $m ) ) {
			switch ( $m[1] ) {
			case 'option ':
				$search = '<option';
				$replace = '<option selected="selected"';
				$regex = "/^\t<option value='([^']+)'>[^<]+<\/option>/";
				break;
			case 'li>':
				$search = '<li>';
				$replace = '<li class="current-arichive-item">';
				$regex = "/^\t<li><a href='([^']+)' title='[^']+'>[^<]+<\/a><\/li>/";
				break;
			default:
				$search = '';
				$replace = '';
				$regex = '';
			}
		}
		if ( is_year() && $regex && preg_match( $regex, $link_html, $m ) ) {
			$host = \st\get_server_host();
			$url = ( is_ssl() ? 'https://' : 'http://' ) . $host . $_SERVER[ 'REQUEST_URI' ];
			if ( strpos( $url, $m[1] ) === 0 ) {
				$link_html = str_replace( $search, $replace, $link_html );
			}
		}
		return $link_html;
	}, 99 );
}


// -----------------------------------------------------------------------------


function the_page_navigation( $args = [] ) {
	echo get_the_page_navigation( $args );
}

function get_the_page_navigation( $args = [] ) {
	$ps = get_child_pages();
	if ( isset( $args['hide_page_with_thumbnail'] ) && $args['hide_page_with_thumbnail'] ) {
		$ps = array_values( array_filter( $ps, function ( $p ) {
			return ! has_post_thumbnail( $p->ID );
		} ) );
	}
	if ( count( $ps ) === 0 ) return;
	?>
			<nav class="navigation page-navigation">
				<ul class="nav-links">
	<?php
	foreach ( $ps as $p ) the_post_list_item( $p, 'child-page-nav-link' );
	?>
				</ul>
			</nav>
	<?php
}
