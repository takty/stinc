<?php
namespace st\post_type;

/**
 *
 * Custom Post Type Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-09
 *
 */


function add_rewrite_rules( $post_type, $struct = '', $date_slug = 'date' ) {
	add_post_type_rewrite_rules( $post_type, $struct );
	add_post_type_link_filter( $post_type );
	add_archive_rewrite_rules( $post_type, $struct );
	add_archive_link_filter( $post_type, $struct );
	add_date_archive_rewrite_rules( $post_type, $struct, $date_slug );
	add_date_archive_link_filter( $post_type, $struct, $date_slug );
}

function add_post_type_rewrite_rules( $post_type, $struct = '', $by_post_name = false ) {
	global $wp_rewrite;

	if ( empty( $struct ) ) $struct = $post_type;
	$post_tag_id = "%{$post_type}_post_id%";
	$single_id = "{$post_type}_single_id";
	add_rewrite_tag( $post_tag_id, '([0-9]+)', "post_type=$post_type&p=" );
	add_permastruct( $single_id, "/$struct/$post_tag_id", [ 'with_front' => false ] );

	if ( $by_post_name ) {
		$post_tag_slug = "%{$post_type}_post_slug%";
		$single_slug = "{$post_type}_single_slug";
		add_rewrite_tag( $post_tag_slug, '(.?.+?)', "post_type=$post_type&name=" );
		add_permastruct( $single_slug, "/$struct/$post_tag_slug", [ 'with_front' => false ] );
	}
}

function add_post_type_link_filter( $post_type, $by_post_name = false ) {  // for making pretty link of custom post types
	add_filter( 'post_type_link', function ( $post_link, $id = 0 ) use ( $post_type, $by_post_name ) {
		global $wp_rewrite;

		$post = get_post( $id );
		if ( is_wp_error( $post ) ) return $post;

		if ( $post->post_type === $post_type ) {
			if ( $by_post_name ) {
				$post_tag_slug = "%{$post_type}_post_slug%";
				$single_slug = "{$post_type}_single_slug";
				$post_link = $wp_rewrite->get_extra_permastruct( $single_slug );
				$post_link = str_replace( $post_tag_slug, $post->post_name, $post_link );
			} else {
				$post_tag_id = "%{$post_type}_post_id%";
				$single_id = "{$post_type}_single_id";
				$post_link = $wp_rewrite->get_extra_permastruct( $single_id );
				$post_link = str_replace( $post_tag_id, $post->ID, $post_link );
			}
			return home_url( user_trailingslashit( $post_link ) );
		}
		return $post_link;
	}, 1, 2 );
}

function add_archive_rewrite_rules( $post_type, $struct = '' ) {  // need to set 'has_archive => true' when registering the post type
	global $wp_rewrite;

	if ( empty( $struct ) ) $struct = $post_type;
	$struct = $wp_rewrite->root . $struct;

	add_rewrite_rule( "{$struct}/?$", "index.php?post_type=$post_type", 'top' );
	add_rewrite_rule( "{$struct}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );

	if ( $wp_rewrite->feeds ) {
		$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
		add_rewrite_rule( "{$struct}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
		add_rewrite_rule( "{$struct}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
	}
}

function add_archive_link_filter( $post_type, $struct = '' ) {
	global $wp_rewrite;

	if ( empty( $struct ) ) $struct = $post_type;
	$struct = $wp_rewrite->root . $struct;
	$archive_link = home_url( user_trailingslashit( $struct, 'post_type_archive' ) );

	add_filter( 'post_type_archive_link', function ( $link, $pt ) use ( $post_type, $archive_link ) {
		if ( $pt === $post_type ) {
			return $archive_link;
		}
		return $link;
	}, 10, 2 );
}

function add_date_archive_rewrite_rules( $post_type, $struct = '', $slug = 'date' ) {
	if ( empty( $struct ) ) $struct = $post_type;

	$tag = "%{$post_type}_{$slug}_year%";
	$name = "{$post_type}_{$slug}";

	add_rewrite_tag( $tag, '([0-9]{4})', "post_type=$post_type&$slug=1&year=" );
	add_permastruct( $name, "/$struct/$slug/$tag/%monthnum%/%day%", [ 'with_front' => false ] );

	add_filter( 'query_vars', function ( $qvars ) use ( $slug ) {
		if ( ! isset( $qvars[ $slug ] ) ) $qvars[] = $slug;
		return $qvars;
	} );
}

function add_date_archive_link_filter( $post_type, $struct = '', $slug = 'date' ) {
	if ( empty( $struct ) ) $struct = $post_type;

	add_filter( 'get_archives_link', function ( $link_html, $url, $text, $format, $before, $after ) use ( $post_type, $struct, $slug ) {
		$url_post_type = '';
		$qps = explode( '&', parse_url( $url, PHP_URL_QUERY ) );
		foreach ( $qps as $qp ) {
			$key_val = explode( '=', $qp );
			if ( count( $key_val ) === 2 && $key_val[0] === 'post_type' ) {
				$url_post_type = $key_val[1];
			}
		}
		if ( $post_type !== $url_post_type ) return $link_html;
		global $wp_rewrite;
		$front = substr( $wp_rewrite->front, 1 );
		$url = str_replace( $front, '', $url );

		if ( class_exists( '\st\Multilang' ) ) {
			$ml = \st\Multilang::get_instance();
			$blog_url = rtrim( $ml->home_url(), '/' );
		} else {
			$blog_url = rtrim( home_url(), '/' );
		}
		$blog_url = preg_replace( '/https?:\/\//', '', $blog_url );
		$ret_link = str_replace( $blog_url, $blog_url . '/%link_dir%', $url );
		$ret_link = str_replace( '%link_dir%/date', '%link_dir%', $ret_link );

		$link_dir = $struct . '/' . $slug;
		$url = str_replace( '%link_dir%', $link_dir, $ret_link );
		$url = remove_query_arg( 'post_type', $url );

		if ('link' == $format) {
			$link_html = "\t<link rel='archives' title='" . esc_attr( $text ) . "' href='$url' />\n";
		} elseif ('option' == $format) {
			$link_html = "\t<option value='$url'>$before $text $after</option>\n";
		} elseif ('html' == $format) {
			$link_html = "\t<li>$before<a href='$url'>$text</a>$after</li>\n";
		} else {  // custom
			$link_html = "\t$before<a href='$url'>$text</a>$after\n";
		}
		return $link_html;
	}, 10, 6 );
}

function make_custom_date_sortable( $post_type, $slug, $meta_key ) {
	add_action( 'pre_get_posts', function ( $query ) use ( $post_type, $slug, $meta_key ) {
		if ( is_admin() ) return;
		if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === $post_type ) {
			if ( $query->get( $slug, false ) !== false ) {
				$year = $query->get( 'year' );
				if ( ! empty( $year ) ) {
					$query->set( $slug.'_year', $year );
					$query->set( 'year', null );
				}
				$monthnum = $query->get( 'monthnum' );
				if ( ! empty( $monthnum ) ) {
					$query->set( $slug.'_monthnum', $monthnum );
					$query->set( 'monthnum', null );
				}
				$day = $query->get( 'day' );
				if ( ! empty( $day ) ) {
					$query->set( $slug.'_day', $day );
					$query->set( 'day', null );
				}
			}
			$mq_key = 'meta_'.$meta_key;
			$query->set( 'meta_query', array(
				$mq_key => array(
					'key'  => $meta_key,
					'type' => 'date'
				)
			) );
			$order = $query->get( 'order' );
			$query->set( 'orderby', array(
				$mq_key => $order,
				'date'  => $order
			) );
		}
	} );
	add_filter( 'posts_where', function ( $where, $query ) use ( $post_type, $slug ) {
		global $wpdb;
		if ( is_admin() || ! $query->is_main_query() ) {
			return $where;
		}
		if ( $query->get('post_type') === $post_type && $query->get( $slug, false ) !== false ) {
			$year = $query->get($slug.'_year', false);
			if ( $year !== false ) {
				$where .= $wpdb->prepare( " AND ( YEAR( CAST($wpdb->postmeta.meta_value AS DATE) ) = %d )", $year );
			}
			$monthnum = $query->get($slug.'_monthnum', false);
			if ( $monthnum !== false ) {
				$where .= $wpdb->prepare( " AND ( MONTH( CAST($wpdb->postmeta.meta_value AS DATE) ) = %d )", $monthnum );
			}
			$day = $query->get($slug.'_day', false);
			if ( $day !== false ) {
				$where .= $wpdb->prepare( " AND ( DAY( CAST($wpdb->postmeta.meta_value AS DATE) ) = %d )", $day );
			}
		}
		return $where;
	}, 10, 2 );
}

function enable_custom_date_adjacent_post_link( $post_type, $meta_key ) {
	add_filter( 'get_next_post_join', function ( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
		}
		return $join;
	}, 10, 5 );
	add_filter( 'get_next_post_where', function ( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$m = get_post_meta( $post->ID, $meta_key, true );
			$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) ) AND", $where );
		}
		return $where;
	}, 10, 5 );
	add_filter( 'get_next_post_sort', function ( $sort, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) ASC,", $sort);
		}
		return $sort;
	}, 10, 2 );

	add_filter( 'get_previous_post_join', function ( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
		}
		return $join;
	}, 10, 5 );
	add_filter( 'get_previous_post_where', function ( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$m = get_post_meta( $post->ID, $meta_key, true );
			$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) ) AND", $where );
		}
		return $where;
	}, 10, 5 );
	add_filter( 'get_previous_post_sort', function ( $sort, $post ) use ( $post_type, $meta_key ) {
		global $wpdb;
		if ( $post->post_type === $post_type ) {
			$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) DESC,", $sort);
		}
		return $sort;
	}, 10, 2 );
}

function post_type_title( $prefix = '', $display = true ) {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) ) $post_type = reset( $post_type );
	$post_type_obj = get_post_type_object( $post_type );
	$title = apply_filters( 'post_type_archive_title', $post_type_obj->labels->name, $post_type );
	if ( $display ) echo $prefix . $title;
	else return $prefix . $title;
}

function get_custom_archives( $meta_key, $args = '' ) {
	global $wpdb, $wp_locale;

	$defaults = array(
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false,
		'echo' => 1, 'order' => 'DESC',
		'post_type' => 'post'
	);

	$r = wp_parse_args( $args, $defaults );

	$post_type_object = get_post_type_object( $r['post_type'] );
	if ( ! is_post_type_viewable( $post_type_object ) ) {
		return;
	}
	$r['post_type'] = $post_type_object->name;

	if ( '' == $r['type'] ) {
		$r['type'] = 'monthly';
	}

	if ( ! empty( $r['limit'] ) ) {
		$r['limit'] = absint( $r['limit'] );
		$r['limit'] = ' LIMIT ' . $r['limit'];
	}

	$order = strtoupper( $r['order'] );
	if ( $order !== 'ASC' ) $order = 'DESC';

	// this is what will separate dates on weekly archive links
	$archive_week_separator = '&#8211;';

	$sql_where = $wpdb->prepare( "WHERE post_type = %s AND post_status = 'publish'", $r['post_type'] );

	$where = apply_filters( 'getarchives_where', $sql_where, $r );

	$join = "INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' )";
	$join = apply_filters( 'getarchives_join', $join, $r );

	$output = '';

	$last_changed = wp_cache_get_last_changed( 'posts' );

	$limit = $r['limit'];

	if ( 'monthly' == $r['type'] ) {
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
	} elseif ( 'yearly' == $r['type'] ) {
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

function set_enter_title_here( $post_type, $text ) {
	add_filter( 'enter_title_here', function ( $title ) use ( $post_type, $text ) {
		$screen = get_current_screen();
		if ( $screen->post_type === $post_type ) $title = $text;
		return $title;
	} );
}
