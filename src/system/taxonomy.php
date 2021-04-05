<?php
namespace st\taxonomy;
/**
 *
 * Custom Taxonomy
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-04-05
 *
 */


function register( $post_type, $slug, $label_s, $hierarchical = true, $show_ui = true ) {
	$args = [
		'hierarchical'      => $hierarchical,
		'public'            => true,
		'show_ui'           => $show_ui,
		'rewrite'           => [ 'with_front' => false, 'slug' => "{$post_type}/{$slug}" ],
		'sort'              => true,
		'show_admin_column' => true
	];
	if ( is_array( $label_s ) ) {
		$args['labels'] = $label_s;
	} else {
		$args['label'] = $label_s;
	}
	register_taxonomy( "{$post_type}_{$slug}", $post_type, $args );
}

function register_category( $post_type, $slug, $suffix = 'category', $label_s = false ) {
	$args = [
		'rewrite'           => [ 'with_front' => false, 'slug' => "$slug/$suffix" ],
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'sort'              => true,
		'show_admin_column' => true
	];
	if ( is_array( $label_s ) ) {
		$args['labels'] = $label_s;
	} else {
		$args['label'] = $label_s ? $label_s : __( 'Categories' );
	}
	register_taxonomy( "{$post_type}_$suffix", $post_type, $args );
	set_taxonomy_post_type_specific( [ "{$post_type}_$suffix" ], $post_type );
}

function register_tag( $post_type, $slug, $suffix = 'tag', $label_s = false ) {
	$args = [
		'rewrite'           => [ 'with_front' => false, 'slug' => "$slug/$suffix" ],
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'sort'              => true,
		'show_admin_column' => true
	];
	if ( is_array( $label_s ) ) {
		$args['labels'] = $label_s;
	} else {
		$args['label'] = $label_s ? $label_s : __( 'Tags' );
	}
	register_taxonomy( "{$post_type}_$suffix", $post_type, $args );
	set_taxonomy_post_type_specific( [ "{$post_type}_$suffix" ], $post_type );
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
	}, 9 );
}

function set_taxonomy_default_term( $post_type_s, $taxonomy, $default_term_slug ) {
	if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];
	foreach( $post_type_s as $post_type ) {
		add_action( "save_post_$post_type", function ( $post_id, $post ) use ( $post_type_s, $taxonomy, $default_term_slug ) {
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
	$c = wp_filter_content_tags( $c );
	$c = capital_P_dangit( $c );
	$c = do_shortcode( $c );
	$c = convert_smilies( $c );

	return str_replace( ']]>', ']]&gt;', $c );
}

function remove_term_description( $taxonomy_s ) {
	if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];

	add_action('admin_head', function () use ( $taxonomy_s ) {
		global $current_screen;
		if ( strpos( $current_screen->id, 'edit-' ) !== 0 ) return;
		$id_tax = substr( $current_screen->id, 5 );
		if ( in_array( $id_tax, $taxonomy_s, true ) ) {
?>
			<script>jQuery(function($) {$('.term-description-wrap').remove();});</script>
<?php
		}
	}, 99 );
	foreach ( $taxonomy_s as $taxonomy ) {
		add_filter( "manage_edit-{$taxonomy}_columns", function ( $columns ) {
			unset( $columns[ 'description' ] );
			return $columns;
		});
		add_filter( "manage_edit-{$taxonomy}_sortable_columns", function ( $sortable ) {
			unset( $sortable[ 'description' ] );
			return $sortable;
		});
	}
}


// Singular Name ---------------------------------------------------------------


function enable_singular_name( $taxonomy_s ) {
	if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
	foreach ( $taxonomy_s as $taxonomy ) {
		add_action( "{$taxonomy}_edit_form_fields", '\st\taxonomy\_cb_term_edit_form_fields', 10, 2 );
		add_action( 'edited_' . $taxonomy, '\st\taxonomy\_cb_edited_term', 10, 2 );
	}
}

function _cb_term_edit_form_fields( $term, $taxonomy ) {
	$_label = esc_html_x( 'Name', 'term name', 'default' );
	$id     = \st\TMK_NAME_S;
	$name   = \st\TMK_NAME_S;
	$_val   = esc_attr( get_term_meta( $term->term_id, \st\TMK_NAME_S, true ) );
?>
	<tr class="form-field">
		<th><label for="<?php echo $id ?>"><?php echo $_label ?> (Singular Form)</label></th>
		<td><input type="text" name="<?php echo $name ?>" id="<?php echo $id ?>" size="40" value="<?php echo $_val ?>" /></td>
	</tr>
<?php
}

function _cb_edited_term( $term_id, $taxonomy ) {
	if ( isset( $_POST[ \st\TMK_NAME_S ] ) ) {
		$val = isset( $_POST[ \st\TMK_NAME_S ] );
		if ( empty( $val ) ) return delete_term_meta( $term_id, \st\TMK_NAME_S );
		return update_term_meta( $term_id, \st\TMK_NAME_S, $val );
	}
}


// Utilities -------------------------------------------------------------------


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
