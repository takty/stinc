<?php
namespace st\ordered_term;

/**
 *
 * Ordered Term (Adding Order Field (Term-Meta) to Taxonomies)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-06
 *
 */


class _ORDERED_TERM_NS {  // just for name space of variables
	public static $key_order = '';
}

function make_terms_ordered( $taxonomies, $key_prefix = '_' ) {
	_ORDERED_TERM_NS::$key_order = $key_prefix . 'menu_order';

	// for Term List View ------------------------------------------------------

	foreach ( $taxonomies as $t ) {
		add_filter( "manage_edit-{$t}_columns", function ( $columns ) {
			$columns[_ORDERED_TERM_NS::$key_order] = __( 'Order', 'default' );
			return $columns;
		} );
		add_filter( "manage_edit-{$t}_sortable_columns", function ( $sortable ) {
			$sortable[_ORDERED_TERM_NS::$key_order] = _ORDERED_TERM_NS::$key_order;
			return $sortable;
		} );
		add_action( "manage_{$t}_custom_column", function ( $content, $column_name, $term_id ) {
			if ( $column_name !== _ORDERED_TERM_NS::$key_order ) {
				return $content;
			}
			$term_id = absint( $term_id );
			$order = get_term_meta( $term_id, _ORDERED_TERM_NS::$key_order, true );

			if ( $order !== '' ) {  // DO NOT USE 'empty'
				$content .= esc_html( $order );
			}
			return $content;
		}, 10, 3 );
		add_action ( "{$t}_edit_form_fields", function ( $tag ) {
			$t_id = $tag->term_id;
			$t_meta = get_term_meta( $t_id, _ORDERED_TERM_NS::$key_order, true );

			$id = _ORDERED_TERM_NS::$key_order;
			$name = _ORDERED_TERM_NS::$key_order;
			$val = isset($t_meta) ? $t_meta : '';
?>
			<tr class="form-field">
				<th><label for="<?= $id ?>"><?= __( 'Order', 'default' ) ?></label></th>
				<td><input type="text" name="<?= $name ?>" id="<?= $id ?>" size="40" value="<?= isset( $val ) ? esc_html( $val ) : '' ?>" /></td>
			</tr>
<?php
		} );
		add_action ( "edited_{$t}", function ( $term_id, $taxonomy ) {
			if ( isset( $_POST[_ORDERED_TERM_NS::$key_order] ) ) {
				update_term_meta( $term_id, _ORDERED_TERM_NS::$key_order, intval( $_POST[_ORDERED_TERM_NS::$key_order] ) );
			}
		}, 10, 2 );
	}
	add_action( 'admin_head', function () use ( $taxonomies ) {
		global $pagenow;
		if ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && in_array( $_GET['taxonomy'], $taxonomies ) ) {
			?><style>
			#<?php echo _ORDERED_TERM_NS::$key_order ?> {width: 4rem;}
			.column-<?php echo _ORDERED_TERM_NS::$key_order ?> {text-align: right;}
			#posts {width: 90px;}
			</style>
			<script>
			jQuery(document).ready(function ($) {
				var wp_inline_edit = inlineEditTax.edit;
				inlineEditTax.edit = function (id) {
					wp_inline_edit.apply(this, arguments);

					if (typeof(id) === 'object') {
						id = parseInt(this.getId(id));
					}
					if (id > 0) {
						var tag_row = $('#tag-' + id);
						var order = $('.column-<?= _ORDERED_TERM_NS::$key_order ?>', tag_row).html();
						var input = document.querySelector('input[name="<?= _ORDERED_TERM_NS::$key_order ?>"]');
						input.value = order;
					}
					return false;
				};
			});
			</script><?php
		}
	} );


	// Quick Edit Function -----------------------------------------------------

	global $pagenow;
	if ( $pagenow === 'edit-tags.php' ) {
		add_action('quick_edit_custom_box', function ( $column_name, $screen, $name ) use ( $taxonomies ) {
			if ( ( $column_name != _ORDERED_TERM_NS::$key_order ) || ! in_array( $name, $taxonomies ) ) return false;

			static $print_nonce = true;
			if ( $print_nonce ) {
				$print_nonce = false;
				wp_nonce_field( 'quick_edit_action', $column_name . '_edit_nonce' );
			}
?>
			<fieldset>
				<div id="<?= _ORDERED_TERM_NS::$key_order ?>-content" class="inline-edit-col">
					<label>
						<span class="title"><?= __( 'Order', 'default' ) ?></span>
						<span class="input-text-wrap"><input type="text" name="<?= $column_name ?>" class="ptitle" value=""></span>
					</label>
				</div>
			</fieldset>
<?php
		}, 10, 3 );
	}


	// Actually Sort Terms -----------------------------------------------------

	add_filter( 'terms_clauses', function ( $clauses, $taxes = [], $args = [] ) use ( $taxonomies ) {
		if ( count( $taxes ) > 1 || ! in_array( $taxes[0], $taxonomies ) ) return $clauses;
		global $wpdb;

		$orderby = isset( $args[ 'orderby' ] ) ? $args[ 'orderby' ] : '';
		if ( $orderby !== 'name' && $orderby !== _ORDERED_TERM_NS::$key_order ) return $clauses;

		$clauses['fields'] .= ', tm.meta_key, tm.meta_value';
		$clauses['join']   .= " LEFT OUTER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id AND tm.meta_key = '" . _ORDERED_TERM_NS::$key_order . "'";

		$order = isset( $args[ 'order' ] ) ? $args[ 'order' ] : 'ASC';
		$clauses['orderby'] = str_replace( 'ORDER BY', "ORDER BY tm.meta_value+0 $order,", $clauses['orderby'] );

		return $clauses;
	}, 10, 3 );

	add_filter( 'get_the_terms', function ( $terms, $post_id, $taxonomy ) use ( $taxonomies ) {
		if ( in_array( $taxonomy, $taxonomies ) ) {
			$ts = [];
			foreach ( $terms as $t ) {
				$order = intval( get_term_meta( $t->term_id, _ORDERED_TERM_NS::$key_order, true ) );
				$ts[] = [ $order, $t ];
			}
			usort ( $ts, function ( $a, $b ) {
				if ( $a[0] === $b[0] ) {
					return 0;
				}
				return ( $a[0] < $b[0] ) ? -1 : 1;
			} );
			$terms = array_map( function ( $t ) { return $t[1]; }, $ts );
		}
		return $terms;
	}, 10, 3 );

}

function get_order( $term ) {
	return intval( get_term_meta( $term->term_id, _ORDERED_TERM_NS::$key_order, true ) );
}

function set_terms_with_order( $taxonomy, $slugs_to_labels, $orders, $parent_id = 0 ) {
	$order = array_shift( $orders );
	$o = $order[0];
	foreach ( $slugs_to_labels as $slug => $label ) {
		$t = get_term_by( 'slug', $slug, $taxonomy );
		$tid = ( $t === false ) ? -1 : $t->term_id;
		if ( is_array( $label ) ) {
			if ( $t === false ) {
				$ret = wp_insert_term( $label[0], $taxonomy, [ 'slug' => $slug, 'parent' => $parent_id ] );
				$tid = $ret['term_id'];
				update_term_meta( $tid, _ORDERED_TERM_NS::$key_order, $o );
			}
			$orders[0][0] += $o;
			set_terms_with_order( $taxonomy, $label[1], $orders, $tid );
			$orders[0][0] -= $o;
		} else {
			if ( $t === false ) {
				$ret = wp_insert_term( $label, $taxonomy, [ 'slug' => $slug, 'parent' => $parent_id ] );
				$tid = $ret['term_id'];
				update_term_meta( $tid, _ORDERED_TERM_NS::$key_order, $o );
			}
		}
		$o += $order[1];
	}
	array_unshift( $orders, $order );
}

function set_terms_with_order_and_meta( $taxonomy, $slugs_to_labels, $orders, $meta, $parent_id = 0 ) {
	$order = array_shift( $orders );
	$o = $order[0];
	foreach ( $slugs_to_labels as $slug => $label ) {
		$t = get_term_by( 'slug', $slug, $taxonomy );
		$tid = ( $t === false ) ? -1 : $t->term_id;
		if ( is_array( $label ) ) {
			if ( $t === false ) {
				$tid = _insert_term( $taxonomy, $label[0], $slug, $parent_id, $o, $meta );
			}
			$orders[0][0] += $o;
			set_terms_with_order_and_meta( $taxonomy, $label[1], $orders, $meta, $tid );
			$orders[0][0] -= $o;
		} else {
			if ( $t === false ) {
				$tid = _insert_term( $taxonomy, $label, $slug, $parent_id, $o, $meta );
			}
		}
		$o += $order[1];
	}
	array_unshift($orders, $order);
}

function _insert_term( $taxonomy, $label, $slug, $parent_id, $order, $meta ) {
	$labels = explode( $meta['delimiter'], $label );

	$ret = wp_insert_term( $labels[0], $taxonomy, [ 'slug' => $slug, 'parent' => $parent_id ] );
	$tid = $ret['term_id'];
	update_term_meta( $tid, _ORDERED_TERM_NS::$key_order, $order );

	if ( count( $labels ) > 1 ) {
		$keys = $meta['keys'];
		$count = min( count( $labels ) - 1, count( $keys ) );
		for ( $i = 0; $i < $count; $i += 1 ) {
			update_term_meta( $tid, $keys[$i], $labels[$i + 1] );
		}
	}
	return $tid;
}
