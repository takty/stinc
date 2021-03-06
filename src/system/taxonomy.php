<?php
namespace st\taxonomy;
/**
 *
 * Custom Taxonomy
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-07
 *
 */


function register( $post_type, $slug, $label, $hierarchical = true, $show_ui = true ) {
	register_taxonomy( "{$post_type}_{$slug}", $post_type, [
		'hierarchical'      => $hierarchical,
		'label'             => $label,
		'public'            => true,
		'show_ui'           => $show_ui,
		'rewrite'           => [ 'with_front' => false, 'slug' => "{$post_type}/{$slug}" ],
		'sort'              => true,
		'show_admin_column' => true
	] );
}

function register_without_post_type( $taxonomy, $label, $hierarchical = true, $show_ui = true ) {
	register_taxonomy( $taxonomy, null, [
		'hierarchical'      => $hierarchical,
		'label'             => $label,
		'public'            => true,
		'show_ui'           => $show_ui,
		'rewrite'           => false,
		'sort'              => true,
		'show_admin_column' => true
	] );
}

function set_terms( $taxonomy, $slugs_to_labels, $parent_id = 0, $force_rename = false ) {
	foreach ( $slugs_to_labels as $slug => $label ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( is_array( $label ) ) {
			if ( $term === false ) {
				$ret = wp_insert_term( $label[0], $taxonomy, [ 'slug' => $slug, 'parent' => $parent_id ] );
				set_terms( $taxonomy, $label[1], $ret['term_id'] );
			} else if ( $force_rename ) {
				wp_update_term( $term->term_id, $taxonomy, [ 'name' => $label[0] ] );
				set_terms( $taxonomy, $label[1], $term->term_id, $force_rename );
			}
		} else {
			if ( $term === false ) {
				wp_insert_term( $label, $taxonomy, [ 'slug' => $slug, 'parent' => $parent_id ] );
			} else if ( $force_rename ) {
				wp_update_term( $term->term_id, $taxonomy, [ 'name' => $label ] );
			}
		}
	}
}


// -----------------------------------------------------------------------------


function set_taxonomy_post_type_specific( $taxonomies, $post_type ) {
	add_action( 'pre_get_posts', function ( $query ) use ( $taxonomies, $post_type ) {
		if ( is_admin() || ! $query->is_main_query() ) return;

		foreach ( $taxonomies as $tax ) {
			if ( $query->is_tax( $tax ) ) {
				$query->set( 'post_type', $post_type );
				break;
			}
		}
	} );
}

function set_taxonomy_default_term( $post_types, $taxonomy, $default_term_slug ) {
	foreach( $post_types as $post_type ) {
		add_action( "save_post_$post_type", function ( $post_id, $post ) use ( $post_types, $taxonomy, $default_term_slug ) {
			$ts = wp_get_object_terms( $post_id, $taxonomy );
			if ( is_wp_error( $ts ) || ! empty( $ts ) ) return;
			wp_set_object_terms( $post_id, $default_term_slug, $taxonomy );
		}, 10, 2 );
	}
}

function set_taxonomy_exclusive( $taxonomy_s ) {
	if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
	add_action( 'admin_print_footer_scripts', function () use ( $taxonomy_s ) {
?>
		<script type="text/javascript">
		jQuery(function ($) {
			var taxes = ['<?php echo implode( "', '", $taxonomy_s ); ?>'];
			// for Edit Screen
			for (var i = 0; i < taxes.length; i += 1) {
				$('#taxonomy-' + taxes[i] + ' input[type=checkbox]').each(function () {$(this).attr('type', 'radio');});
			}

			// for Quick Edit
			for (var i = 0; i < taxes.length; i += 1) {
				var checklist = $('.' + taxes[i] + '-checklist input[type=checkbox]');
				checklist.each(function () { $(this).prop('type', 'radio'); });
			}
			$('#the-list').on('click', 'button.editinline', function () {
				var post_id = inlineEditPost.getId(this);
				var rowData = $('#inline_'+ post_id);
				$('.post_category', rowData).each(function () {
					var taxonomy = $(this).attr('id').replace('_' + post_id, '');
					if (taxes.indexOf(taxonomy) !== -1) {
						var term_ids = $(this).text();
						term_ids = term_ids.trim() !== '' ? term_ids.trim() : '0';
						var term_id = term_ids.split(',');
						term_id = term_id ? term_id[0] : '0';
						if (term_id === '0') {
							$('.' + taxonomy + '-checklist li input:radio').prop('checked', false);
						} else {
							$('li#' + taxonomy + '-' + term_id).find('input:radio').first().prop('checked', true);
						}
					}
				});
			});

			// for Bulk Edit
			$('#doaction, #doaction2').click(function (e) {
				var n = $(this).attr('id').substr(2);
				if ('edit' === $('select[name="' + n + '"]').val()) {
					e.preventDefault();
					$('.cat-checklist').each(function () {
						if ($(this).find('input[type="radio"]').length) {
							$(this).find('input[type="radio"]').prop('checked', false);
							$(this).prev('input').remove();
						}
					});
				}
			});
		});
		</script>
<?php
	} );
	add_action( 'set_object_terms', function ( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) use ( $taxonomy_s ) {
		if ( ! in_array( $taxonomy, $taxonomy_s, true ) ) return;
		$ai = array_intersect( $old_tt_ids, $tt_ids );
		if ( empty( $ai ) || count( $ai ) === count( $tt_ids ) ) return;

		wp_remove_object_terms( $object_id, $ai, $taxonomy );
	}, 10, 6 );
}


// Count Posts with Terms ------------------------------------------------------


function count_term_from_posts( $posts, $taxonomy, $term_slug ) {
	$post_sets = [];
	foreach ( $posts as $p ) {
		$terms = wp_get_object_terms( $p->ID, $taxonomy, [ 'fields' => 'slugs' ] );
		foreach ( $terms as $t ) {
			if ( ! isset( $post_sets[ $t ] ) ) $post_sets[ $t ] = [];
			$post_sets[ $t ][ $p->ID ] = 1;
		}
	}
	$root = get_term_by( 'slug', $term_slug, $taxonomy );
	_count_term( $taxonomy, $root, $post_sets );

	$counts = [];
	foreach ( array_keys( $post_sets ) as $slug ) {
		$count = count( $post_sets[ $slug ] );
		if ( $count > 0 ) $counts[ $slug ] = $count;
	}
	return $counts;
}

function _count_term( $taxonomy, $term, &$post_sets ) {
	$set = [];
	$child = get_terms( $taxonomy, [ 'hide_empty' => false, 'parent' => $term->term_id ] );
	foreach ( $child as $c ) {
		_count_term( $taxonomy, $c, $post_sets );
		if ( isset( $post_sets[ $c->slug ] ) ) {
			$set = array_merge( $set, $post_sets[ $c->slug ] );
		}
	}
	if ( ! isset( $post_sets[ $term->slug ] ) ) $post_sets[ $term->slug ] = [];
	$post_sets[ $term->slug ] = array_merge( $post_sets[ $term->slug ], $set );
}


// Limit Archive Links by Terms ------------------------------------------------


function limit_archive_links_by_terms( $post_type ) {
	add_filter( 'getarchives_join', function ( $join, $r ) use ( $post_type ) {
		if ( $r['post_type'] !== $post_type ) return $join;
		global $wpdb;
		$join .= " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)";
		$join .= " INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
		$join .= " INNER JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)";
		return $join;
	}, 10, 2 );
	add_filter( 'getarchives_where', function ( $where, $r ) {
		if ( isset( $r['taxonomy'] ) && isset( $r['term'] ) ) {
			global $wpdb;
			$where .= " AND taxonomy = '{$r['taxonomy']}' AND slug = '{$r['term']}'";
		}
		return $where;
	}, 10, 2 );
}


// Utilities -------------------------------------------------------------------


function make_tax_query( $tax, $term_slug_s ) {
	$terms = is_array( $term_slug_s ) ?  implode( ',', $term_slug_s ) : $term_slug_s;
	return [ 'taxonomy' => $tax, 'field' => 'slug', 'terms' => $terms ];
}

function get_term_root( $term, $root_id ) {
	$cur = $term->term_id;
	$ret = [$term, $term];

	while ( $cur !== 0 && $cur !== $root_id ) {
		if ( $term->parent === 0 || $term->parent === $root_id ) break;
		$term = get_term( $term->parent, $term->taxonomy );
		$cur = $term->term_id;
		$ret[1] = $ret[0];
		$ret[0] = $term;
	}
	return $ret;
}

function term_description( $term_id = 0, $taxonomy ) {
	if ( ! $term_id && ( is_tax() || is_tag() || is_category() ) ) {
		$t = get_queried_object();
		$term_id  = $t->term_id;
		$taxonomy = $t->taxonomy;
	}
	return \term_description( $term_id, $taxonomy );
}

function get_term_list( $taxonomy, $before = '', $sep = '', $after = '', $add_link = true ) {
	$ts = get_terms( $taxonomy );
	if ( is_wp_error( $ts ) ) return $ts;
	if ( empty( $ts ) ) return false;

	global $wp_query;
	$term = $wp_query->queried_object;
	if ( ! ( $term instanceof WP_Term ) && ! ( is_object( $term ) && property_exists( $term, 'term_id' ) ) ) {
		$term = null;
	}

	$links = [];
	foreach ( $ts as $t ) {
		$current = ( $term && $term->term_id === $t->term_id ) ? 'current ' : '';
		if ( $add_link ) {
			$link = get_term_link( $t, $taxonomy );
			if ( is_wp_error( $link ) ) return $link;
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag" class="' . $current . $taxonomy . '-' . $t->slug . '">' . esc_html( $t->name ) . '</a>';
		} else {
			$links[] = '<span class="' . $current . $taxonomy . '-' . $t->slug . '">' . esc_html( $t->name ) . '</span>';
		}
	}
	$term_links = apply_filters( "term_links-{$taxonomy}", $links );
	return $before . join( $sep, $term_links ) . $after;
}

function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true ) {
	$ts = get_the_terms( $post_id, $taxonomy );
	if ( is_wp_error( $ts ) ) return $ts;
	if ( empty( $ts ) ) return false;

	$links = [];
	foreach ( $ts as $t ) {
		if ( $add_link ) {
			$link = get_term_link( $t, $taxonomy );
			if ( is_wp_error( $link ) ) return $link;
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag" class="' . $taxonomy . '-' . $t->slug . '">' . esc_html( $t->name ) . '</a>';
		} else {
			$links[] = '<span class="' . $taxonomy . '-' . $t->slug . '">' . esc_html( $t->name ) . '</span>';
		}
	}
	$term_links = apply_filters( "term_links-{$taxonomy}", $links );
	return $before . join( $sep, $term_links ) . $after;
}

function get_the_term_names( $post_id, $taxonomy ) {
	$ts = get_the_terms( $post_id, $taxonomy );
	if ( ! is_array( $ts ) ) return [];

	$tns = [];
	foreach ( $ts as $t ) $tns[] = $t->name;
	return $tns;
}

function get_terms( $id, $taxonomy, $before = '', $sep = '', $after = '' ) {
	$terms = get_the_terms( $id, $taxonomy );
	if ( is_wp_error( $terms ) ) return $terms;
	if ( empty( $terms ) ) return false;

	$names = array_map( function ( $t ) { return $t->name; }, $terms );
	return $before . implode( $sep, $names ) . $after;
}


// Term Content (Rich Editor) --------------------------------------------------


function add_term_content_field( $taxonomy, $key, $label_postfix = '', $priority = 10 ) {
	add_action( "{$taxonomy}_edit_form_fields", function ( $term ) use ( $key, $label_postfix ) {
		$cont = get_term_meta( $term->term_id, $key, true );
?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="<?php echo $key ?>"><?php _e('Content'); ?><?php echo $label_postfix; ?></label></th>
			<td><?php wp_editor( $cont, $key, ['textarea_rows' => '8'] ); ?></td>
		</tr>
<?php
	}, $priority );
	add_action( "edited_$taxonomy", function ( $term_id ) use ( $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			$val = $_POST[ $key ];
			if ( empty( $val ) ) {
				delete_term_meta( $term_id, $key );
			} else {
				$val = apply_filters( 'content_save_pre', $val );
				update_term_meta( $term_id, $key, $val );
			}
		}
	} );
}

function get_term_content( $term, $key ) {
	$c = get_term_meta( $term->term_id, $key, true );
	if ( empty( $c ) ) return '';

	// Apply the filters for 'the_content'
	if ( function_exists( 'do_blocks' ) ) $c = do_blocks( $c );
	$c = wptexturize( $c );
	$c = wpautop( $c );
	$c = shortcode_unautop( $c );
	$c = prepend_attachment( $c );
	$c = wp_make_content_images_responsive( $c );
	$c = capital_P_dangit( $c );
	$c = do_shortcode( $c );
	$c = convert_smilies( $c );

	return str_replace( ']]>', ']]&gt;', $c );
}

function remove_term_description( $taxonomy ) {
	add_action('admin_head', function () use ( $taxonomy ) {
		global $current_screen;
		if ( $current_screen->id === "edit-$taxonomy" ) {
?>
			<script>jQuery(function($) {$('.term-description-wrap').remove();});</script>
<?php
		}
	}, 99 );
	add_filter( "manage_edit-{$taxonomy}_columns", function ( $columns ) {
		unset( $columns[ 'description' ] );
		return $columns;
	});
	add_filter( "manage_edit-{$taxonomy}_sortable_columns", function ( $sortable ) {
		unset( $sortable[ 'description' ] );
		return $sortable;
	});
}
