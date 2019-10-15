<?php
namespace st;
/**
 *
 * Navigation Tags
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-12
 *
 */


require_once __DIR__ . '/../util/text.php';
require_once __DIR__ . '/../util/url.php';


function the_yearly_archive_select( $post_type = 'post', $default_title = 'Year', $args = [], $meta_key = false ) {
	$args = array_merge( [
		'post_type' => $post_type,
		'type'      => 'yearly',
		'format'    => 'option',
	], $args );
?>
	<select onchange="document.location.href = this.value;">
		<option value="#"><?php echo $default_title ?></option>
<?php
	if ( $meta_key === false ) {
		wp_get_archives( $args );
	} else {
		get_custom_archives( $meta_key, $args );
	}
?>
	</select>
<?php
}

function the_taxonomy_archive_select( $taxonomy, $default_title = 'Category', $check_lang_visible = false, $hide_empty = false ) {
	$key_visible = '_visible';
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		$key_visible .= '_' . $ml->get_site_lang();
	}
	$terms = get_terms( $taxonomy, [ 'hide_empty' => $hide_empty, 'parent' => 0 ] );
?>
		<select onchange="document.location.href = this.value;">
			<option value="#"><?php echo $default_title ?></option>
<?php
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
		foreach ( $terms as $t ) {
			if ( $check_lang_visible && empty( get_term_meta( $t->term_id, $key_visible, true ) ) ) continue;
			echo '<option value="' . esc_attr( get_term_link( $t ) ) . '">' . esc_html( $ml->get_term_name( $t ) ) . '</option>';

			$cts = get_terms( $taxonomy, [ 'hide_empty' => $hide_empty, 'parent' => $t->term_id ] );
			foreach ( $cts as $ct ) {
				echo '<option value="' . esc_attr( get_term_link( $ct ) ) . '">' . '— ' . esc_html( $ml->get_term_name( $ct ) ) . '</option>';
			}

		}
	} else {
		foreach ( $terms as $t ) {
			if ( $check_lang_visible && empty( get_term_meta( $t->term_id, $key_visible, true ) ) ) continue;
			echo '<option value="' . esc_attr( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</option>';

			$cts = get_terms( $taxonomy, [ 'hide_empty' => $hide_empty, 'parent' => $t->term_id ] );
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


function get_custom_archives( $meta_key, $args = [] ) {
	global $wpdb, $wp_locale;
	$r = array_merge( [
		'type'            => 'monthly',
		'limit'           => '',
		'format'          => 'html',
		'before'          => '',
		'after'           => '',
		'show_post_count' => false,
		'echo'            => 1,
		'order'           => 'DESC',
		'post_type'       => 'post'
	], $args );

	$post_type_object = get_post_type_object( $r['post_type'] );
	if ( ! is_post_type_viewable( $post_type_object ) ) return;

	if ( ! empty( $r['limit'] ) ) {
		$r['limit'] = absint( $r['limit'] );
		$r['limit'] = ' LIMIT ' . $r['limit'];
	}

	$order = strtoupper( $r['order'] );
	if ( $order !== 'ASC' ) $order = 'DESC';

	$where = $wpdb->prepare( "WHERE post_type = %s AND post_status = 'publish'", $r['post_type'] );
	$where = apply_filters( 'getarchives_where', $where, $r );
	$join  = "INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' )";
	$join  = apply_filters( 'getarchives_join', $join, $r );

	$output = '';
	$last_changed = wp_cache_get_last_changed( 'posts' );
	$limit = $r['limit'];

	if ( 'monthly' === $r['type'] ) {
		$query = "SELECT YEAR(meta_value) AS `year`, MONTH(meta_value) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(meta_value), MONTH(meta_value) ORDER BY meta_value $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				$url = get_month_link( $result->year, $result->month );
				if ( 'post' !== $r['post_type'] ) {
					$url = add_query_arg( 'post_type', $r['post_type'], $url );
				}
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $result->month ), $result->year );
				if ( $r['show_post_count'] ) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
			}
		}
	} elseif ( 'yearly' === $r['type'] ) {
		$query = "SELECT YEAR(meta_value) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(meta_value) ORDER BY meta_value $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			$after = $r['after'];
			foreach ( (array) $results as $result ) {
				$url = get_year_link( $result->year );
				if ( 'post' !== $r['post_type'] ) {
					$url = add_query_arg( 'post_type', $r['post_type'], $url );
				}
				$text = sprintf( '%d', $result->year );
				if ( $r['show_post_count'] ) {
					$r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
				}
				$output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
			}
		}
	}
	if ( $r['echo'] ) {
		echo $output;
	} else {
		return $output;
	}
}


// -----------------------------------------------------------------------------


function the_post_navigation_with_list_link( $args = [] ) {
	$args = array_merge( [ 'has_list_link' => true ], $args );
	echo get_the_post_navigation( $args );
}

function get_the_post_navigation_with_list_link( $args = [] ) {
	$args = array_merge( [ 'has_list_link' => true ], $args );
	return get_the_post_navigation( $args );
}

function the_post_navigation( $args = [] ) {
	echo get_the_post_navigation( $args );
}

function get_the_post_navigation( $args = [] ) {
	$args = array_merge( [
		'prev_text'          => '%title',
		'next_text'          => '%title',
		'list_text'          => 'List',
		'in_same_term'       => false,
		'excluded_terms'     => '',
		'taxonomy'           => 'category',
		'screen_reader_text' => __( 'Post navigation' ),
		'has_list_link'      => false,
		'link_list_pos'      => 'center',
	], $args );
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
	if ( ! $prev && ! $next && ! $list ) return '';

	$temp = '';
	switch ( $args['link-list-pos'] ) {
		case 'start':
			$temp = $list . $prev . $next;
			break;
		case 'center':
			$temp = $prev . $list . $next;
			break;
		case 'end':
			$temp = $prev . $next . $list;
			break;
	}
	return _navigation_markup( $temp, 'post-navigation', $args['screen_reader_text'] );
}


// -----------------------------------------------------------------------------


function the_child_page_navigation( $args = [] ) {
	echo get_the_child_page_navigation( $args );
}

function get_the_child_page_navigation( $args = [] ) {
	$ps = get_child_pages();
	if ( isset( $args['hide_page_with_thumbnail'] ) && $args['hide_page_with_thumbnail'] ) {
		$ps = array_values( array_filter( $ps, function ( $p ) {
			return ! has_post_thumbnail( $p->ID );
		} ) );
	}
	if ( count( $ps ) === 0 ) return;

	ob_start();
?>
	<nav class="navigation child-page-navigation">
		<div class="nav-links">
			<ul class="child-page-nav">
				<li class="child-page-nav-link parent current"><span><?php the_title() ?></span></li>
				<?php foreach ( $ps as $p ) the_post_list_item( $p, 'child-page-nav-link' ); ?>
			</ul>
		</div>
	</nav>
<?php
	return ob_get_clean();
}

function the_sibling_page_navigation( $args = [] ) {
	echo get_the_sibling_page_navigation( $args );
}

function get_the_sibling_page_navigation( $args = [] ) {
	$ps = get_sibling_pages();
	if ( isset( $args['hide_page_with_thumbnail'] ) && $args['hide_page_with_thumbnail'] ) {
		$ps = array_values( array_filter( $ps, function ( $p ) {
			return ! has_post_thumbnail( $p->ID );
		} ) );
	}
	if ( count( $ps ) === 0 ) return;

	global $post;
	$pid = $post->post_parent;
	$e_href = esc_attr( get_permalink( $pid ) );
	$e_title = esc_html( get_the_title( $pid ) );
	ob_start();
?>
	<nav class="navigation sibling-page-navigation">
		<div class="nav-links">
			<ul class="sibling-page-nav">
				<li><a class="sibling-page-nav-link parent" href="<?php echo $e_href ?>"><?php echo $e_title ?></a></li>
				<?php foreach ( $ps as $p ) the_post_list_item( $p, 'sibling-page-nav-link' ); ?>
			</ul>
		</div>
	</nav>
<?php
	return ob_get_clean();
}
