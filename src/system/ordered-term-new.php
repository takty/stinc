<?php
/**
 *
 * Ordered Term (Adding Order Field (Term-Meta) to Taxonomies)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-08
 *
 */


namespace st {


class OrderedTerm {

	const DEFAULT_META_KEY = '_menu_order';

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new OrderedTerm();
		return self::$_instance;
	}


	// -------------------------------------------------------------------------

	private $_key_order       = self::DEFAULT_META_KEY;
	private $_taxonomies      = [];
	private $_is_filter_added = false;

	private function __construct() {
	}

	public function set_meta_key( $key ) {
		$this->_key_order = $key;
	}

	public function make_terms_ordered( $taxonomy_s ) {
		if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
		$this->_taxonomies = array_merge( $this->_taxonomies, $taxonomy_s );

		if ( is_admin() ) {
			foreach ( $taxonomy_s as $tax ) {
				add_filter( "manage_edit-{$tax}_columns",          [ $this, '_cb_manage_edit_taxonomy_columns' ] );
				add_filter( "manage_edit-{$tax}_sortable_columns", [ $this, '_cb_manage_edit_taxonomy_sortable_columns' ] );
				add_action( "manage_{$tax}_custom_column",         [ $this, '_cb_manage_taxonomy_custom_column' ], 10, 3 );
				add_action( "{$tax}_edit_form_fields",             [ $this, '_cb_taxonomy_edit_form_fields' ] );
				add_action( "edited_{$tax}",                       [ $this, '_cb_edited_taxonomy' ], 10, 2 );
			}
		}
		if ( ! $this->_is_filter_added ) {
			if ( is_admin() ) {
				add_action( 'admin_head', [ $this, '_cb_admin_head' ] );
				global $pagenow;
				if ( $pagenow === 'edit-tags.php' ) {
					add_action( 'quick_edit_custom_box', [ $this, '_cb_quick_edit_custom_box' ], 10, 3 );
				}
			}
			add_filter( 'terms_clauses', [ $this, '_cb_terms_clauses' ], 10, 3 );
			add_filter( 'get_the_terms', [ $this, '_cb_get_the_terms' ], 10, 3 );
			$this->_is_filter_added = true;
		}
	}

	public function get_order( $term ) {
		return intval( get_term_meta( $term->term_id, $this->_key_order, true ) );
	}

	public function insert_terms( $slugs_to_labels, $taxonomy, $args ) {
		$args = array_merge( [
			'parent'       => 0,
			'orders'       => false,
			'meta'         => false,
			'force_update' => false,
			'_is_ordered'  => in_array( $taxonomy, $this->_taxonomies ),
		], $args );
		$this->_set_terms( $taxonomy, $slugs_to_labels, $args, $args['parent'] );
	}

	private function _set_terms( $taxonomy, $slugs_to_labels, $args, $parent_id = 0, $parent_idx = 0, $depth = 0 ) {
		$cur_order = ( $args['orders'] === false ) ? [ 1, 1 ] : $args['orders'][ $depth ];
		list( $order_bgn, $order_inc) = $cur_order;

		$idx = $parent_idx + $order_bgn;
		foreach ( $slugs_to_labels as $slug => $thing ) {
			$term_id = $this->_insert_term( $taxonomy, $thing, $parent_id, $slug, $idx, $args );
			if ( is_array( $thing ) ) {
				$this->_set_terms( $taxonomy, $thing[1], $args, $term_id, $idx, $depth + 1 );
			}
			$idx += $order_inc;
		}
	}

	private function _insert_term( $taxonomy, $label, $parent, $slug, $idx, $args ) {
		$t = get_term_by( 'slug', $slug, $taxonomy );
		if ( $t !== false && ! $args['force_update'] ) return $t->term_id;

		if ( is_array( $label ) ) $label = $label[0];

		$meta = $args['meta'];
		if ( $meta !== false ) {
			$meta_vals = explode( $meta['delimiter'], $label );
			$label = array_shift( $meta_vals );
		}

		if ( $t === false ) {
			$ret = wp_insert_term( $label, $taxonomy, [ 'parent' => $parent, 'slug' => $slug ] );
		} else if ( $args['force_update'] ) {
			$ret = wp_update_term( $t->term_id, $taxonomy, [ 'name' => $label ] );
		}
		if ( is_wp_error( $ret ) ) return false;
		$tid = $ret['term_id'];

		if ( $args['_is_ordered'] ) update_term_meta( $tid, $this->_key_order, $idx );

		if ( $meta !== false && 1 < count( $meta_vals ) ) {
			$keys = $meta['keys'];
			$count = min( count( $meta_vals ), count( $keys ) );
			for ( $i = 0; $i < $count; $i += 1 ) {
				update_term_meta( $tid, $keys[ $i ], $meta_vals[ $i ] );
			}
		}
		return $tid;
	}


	// Term List View ----------------------------------------------------------

	public function _cb_manage_edit_taxonomy_columns( $columns ) {
		$columns[ $this->_key_order ] = __( 'Order', 'default' );
		return $columns;
	}

	public function _cb_manage_edit_taxonomy_sortable_columns( $sortable ) {
		$sortable[ $this->_key_order ] = $this->_key_order;
		return $sortable;
	}

	public function _cb_manage_taxonomy_custom_column( $content, $column_name, $term_id ) {
		if ( $column_name !== $this->_key_order ) return $content;

		$term_id = absint( $term_id );
		$idx = get_term_meta( $term_id, $this->_key_order, true );

		if ( $idx !== false || $idx !== '' ) {  // DO NOT USE 'empty'
			$content .= esc_html( $idx );
		}
		return $content;
	}

	public function _cb_taxonomy_edit_form_fields( $term ) {
		$idx   = get_term_meta( $term->term_id, $this->_key_order, true );
		$_val  = ( $idx !== false ) ? esc_attr( $idx ) : '';
		$_id   = esc_attr( $this->_key_order );
		$_name = esc_attr( $this->_key_order );
?>
		<tr class="form-field">
			<th><label for="<?php echo $_id ?>"><?php echo __( 'Order', 'default' ) ?></label></th>
			<td><input type="text" name="<?php echo $_name ?>" id="<?php echo $_id ?>" size="40" value="<?php echo $_val ?>" /></td>
		</tr>
<?php
	}

	public function _cb_edited_taxonomy( $term_id, $taxonomy ) {
		if ( isset( $_POST[ $this->_key_order ] ) ) {
			update_term_meta( $term_id, $this->_key_order, intval( $_POST[ $this->_key_order ] ) );
		}
	}

	public function _cb_admin_head() {
		global $pagenow;
		if ( $pagenow !== 'edit-tags.php' || ! isset( $_GET['taxonomy'] ) || ! in_array( $_GET['taxonomy'], $this->_taxonomies ) ) return;
		?><style>
		#<?php echo $this->_key_order ?> {width: 4rem;}
		.column-<?php echo $this->_key_order ?> {text-align: right;}
		#posts {width: 90px;}
		</style>
		<script>
		jQuery(document).ready(function ($) {
			var wp_inline_edit = inlineEditTax.edit;
			inlineEditTax.edit = function (id) {
				wp_inline_edit.apply(this, arguments);
				if (typeof(id) === 'object') id = parseInt(this.getId(id));
				if (id > 0) {
					var tag_row = $('#tag-' + id);
					var order = $('.column-<?php echo $this->_key_order ?>', tag_row).html();
					var input = document.querySelector('input[name="<?php echo $this->_key_order ?>"]');
					input.value = order;
				}
				return false;
			};
		});
		</script><?php
	}


	// Quick Edit Function -----------------------------------------------------

	public function _cb_quick_edit_custom_box( $column_name, $screen, $name ) {
		if ( ( $column_name !== $this->_key_order ) || ! in_array( $name, $this->_taxonomies ) ) return false;

		static $print_nonce = true;
		if ( $print_nonce ) {
			$print_nonce = false;
			wp_nonce_field( 'quick_edit_action', "{$column_name}_edit_nonce" );
		}
?>
		<fieldset>
			<div id="<?php echo $this->_key_order ?>-content" class="inline-edit-col">
				<label>
					<span class="title"><?php echo __( 'Order', 'default' ) ?></span>
					<span class="input-text-wrap"><input type="text" name="<?php echo $column_name ?>" class="ptitle" value=""></span>
				</label>
			</div>
		</fieldset>
<?php
	}


	// Actually Sort Terms -----------------------------------------------------

	public function _cb_terms_clauses( $clauses, $taxes = [], $args = [] ) {
		if ( count( $taxes ) > 1 || ! in_array( $taxes[0], $this->_taxonomies ) ) return $clauses;
		global $wpdb;

		$orderby = isset( $args['orderby'] ) ? $args['orderby'] : '';
		if ( $orderby !== 'name' && $orderby !== $this->_key_order ) return $clauses;

		$clauses['fields'] .= ', tm.meta_key, tm.meta_value';
		$clauses['join']   .= " LEFT OUTER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id AND tm.meta_key = '{$this->_key_order}'";

		$order = isset( $args['order'] ) ? $args['order'] : 'ASC';
		$clauses['orderby'] = str_replace( 'ORDER BY', "ORDER BY tm.meta_value+0 $order,", $clauses['orderby'] );

		return $clauses;
	}

	public function _cb_get_the_terms( $terms, $post_id, $taxonomy ) {
		if ( ! in_array( $taxonomy, $this->_taxonomies ) ) return $terms;
		$ts = [];
		foreach ( $terms as $t ) {
			$idx = intval( get_term_meta( $t->term_id, $this->_key_order, true ) );
			$ts[] = [ $idx, $t ];
		}
		usort ( $ts, function ( $a, $b ) {
			if ( $a[0] === $b[0] ) {
				return 0;
			}
			return ( $a[0] < $b[0] ) ? -1 : 1;
		} );
		return array_map( function ( $t ) { return $t[1]; }, $ts );
	}

}


}  // namespace st


namespace st\ordered_term {


function make_terms_ordered( $taxonomies ) {
	\st\OrderedTerm::get_instance()->make_terms_ordered( $taxonomies );
}

function get_order( $term ) {
	return \st\OrderedTerm::get_instance()->get_order( $term );
}

function insert_terms( $slugs_to_labels, $taxonomy, $args ) {
	\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
}

function set_terms_with_order( $taxonomy, $slugs_to_labels, $orders, $parent_id = 0, $force_update = false ) {
	$args = [ 'parent' => $parent_id, 'orders' => $orders, 'force_update' => $force_update ];
	\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
}

function set_terms_with_order_and_meta( $taxonomy, $slugs_to_labels, $orders, $meta, $parent_id = 0, $force_update = false ) {
	$args = [ 'parent' => $parent_id, 'orders' => $orders, 'meta' => $meta, 'force_update' => $force_update ];
	\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
}


}  // namespace st\ordered_term
