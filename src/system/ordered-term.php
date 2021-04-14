<?php
/**
 * Ordered Term (Adding Order Field (Term Meta) to Taxonomies)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st {

	class OrderedTerm {

		const DEFAULT_META_KEY = '_menu_order';

		static private $_instance = null;
		static public function get_instance() {
			if ( self::$_instance === null ) {
				self::$_instance = new OrderedTerm();
			}
			return self::$_instance;
		}


		// -------------------------------------------------------------------------

		private $_key_order       = self::DEFAULT_META_KEY;
		private $_taxonomies      = array();
		private $_is_filter_added = false;

		private $_post_types_meta_key_added = array();
		private $_is_post_type_hook_added   = false;

		private function __construct() {
		}

		public function set_meta_key( $key ) {
			$this->_key_order = $key;
		}

		public function make_terms_ordered( $taxonomy_s ) {
			if ( ! is_array( $taxonomy_s ) ) {
				$taxonomy_s = array( $taxonomy_s );
			}
			$this->_taxonomies = array_merge( $this->_taxonomies, $taxonomy_s );

			if ( is_admin() ) {
				foreach ( $taxonomy_s as $tax ) {
					add_filter( "manage_edit-{$tax}_columns", array( $this, '_cb_manage_edit_taxonomy_columns' ) );
					add_filter( "manage_edit-{$tax}_sortable_columns", array( $this, '_cb_manage_edit_taxonomy_sortable_columns' ) );
					add_action( "manage_{$tax}_custom_column", array( $this, '_cb_manage_taxonomy_custom_column' ), 10, 3 );
					add_action( "{$tax}_edit_form_fields", array( $this, '_cb_taxonomy_edit_form_fields' ) );
					add_action( "edited_{$tax}", array( $this, '_cb_edited_taxonomy' ), 10, 2 );
				}
			}
			if ( ! $this->_is_filter_added ) {
				if ( is_admin() ) {
					add_action( 'admin_head', array( $this, '_cb_admin_head' ) );
					global $pagenow;
					if ( 'edit-tags.php' === $pagenow ) {
						add_action( 'quick_edit_custom_box', array( $this, '_cb_quick_edit_custom_box' ), 10, 3 );
					}
				}
				add_filter( 'terms_clauses', array( $this, '_cb_terms_clauses' ), 10, 3 );
				add_filter( 'get_the_terms', array( $this, '_cb_get_the_terms' ), 10, 3 );
				$this->_is_filter_added = true;
			}
		}

		public function get_order( $term_or_term_id ) {
			if ( is_numeric( $term_or_term_id ) ) {
				$term_id = $term_or_term_id;
			} else {
				$term_id = $term_or_term_id->term_id;
			}
			return (int) get_term_meta( $term_id, $this->_key_order, true );
		}

		public function add_order_post_meta_to_post( $post_type_s ) {
			if ( ! is_array( $post_type_s ) ) {
				$post_type_s = array( $post_type_s );
			}
			foreach ( $post_type_s as $ps ) {
				$this->_post_types_meta_key_added[] = $ps;
			}
			if ( ! $this->_is_post_type_hook_added ) {
				add_action( 'save_post', array( $this, '_cb_save_post' ) );
				$this->_is_post_type_hook_added = true;
			}
		}

		public function get_order_post_meta_key( $taxonomy ) {
			return "_$taxonomy{$this->_key_order}";
		}

		public function insert_terms( $slugs_to_labels, $taxonomy, $args ) {
			$args = array_merge(
				array(
					'parent'       => 0,
					'orders'       => false,
					'meta'         => false,
					'force_update' => false,
					'_is_ordered'  => in_array( $taxonomy, $this->_taxonomies, true ),
				),
				$args
			);
			$this->_set_terms( $taxonomy, $slugs_to_labels, $args, $args['parent'] );
		}

		private function _set_terms( $taxonomy, $slugs_to_labels, $args, $parent_id = 0, $parent_idx = 0, $depth = 0 ) {
			$cur_order = ( false === $args['orders'] ) ? array( 1, 1 ) : $args['orders'][ $depth ];
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
			if ( false !== $t && ! $args['force_update'] ) {
				return $t->term_id;
			}
			if ( is_array( $label ) ) {
				$label = $label[0];
			}
			$meta = $args['meta'];
			if ( false !== $meta ) {
				$meta_vals = explode( $meta['delimiter'], $label );
				$label     = array_shift( $meta_vals );
			}

			if ( false === $t ) {
				$ret = wp_insert_term( $label, $taxonomy, array( 'parent' => $parent, 'slug' => $slug ) );
			} elseif ( $args['force_update'] ) {
				$ret = wp_update_term( $t->term_id, $taxonomy, array( 'name' => $label ) );
			}
			if ( is_wp_error( $ret ) ) {
				return false;
			}
			$tid = $ret['term_id'];

			if ( $args['_is_ordered'] ) {
				update_term_meta( $tid, $this->_key_order, $idx );
			}
			if ( $meta !== false && 0 < count( $meta_vals ) ) {
				$keys  = $meta['keys'];
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
			if ( $column_name !== $this->_key_order ) {
				return $content;
			}
			$idx = get_term_meta( absint( $term_id ), $this->_key_order, true );
			if ( false !== $idx || '' !== $idx ) {  // DO NOT USE 'empty'.
				$content .= esc_html( $idx );
			}
			return $content;
		}

		public function _cb_taxonomy_edit_form_fields( $term ) {
			$idx  = get_term_meta( $term->term_id, $this->_key_order, true );
			$val  = ( false !== $idx ) ? $idx : '';
			$id   = $this->_key_order;
			$name = $this->_key_order;
			?>
			<tr class="form-field">
				<th><label for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Order', 'default' ); ?></label></th>
				<td><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" size="40" value="<?php echo esc_attr( $val ); ?>" /></td>
			</tr>
			<?php
		}

		public function _cb_edited_taxonomy( $term_id, $taxonomy ) {
			if ( isset( $_POST[ $this->_key_order ] ) ) {
				update_term_meta( $term_id, $this->_key_order, (int) $_POST[ $this->_key_order ] );

				if ( $this->_is_post_type_hook_added ) {
					$this->_update_post_meta( $term_id, $taxonomy );
				}
			}
		}

		public function _cb_admin_head() {
			global $pagenow;
			if ( 'edit-tags.php' !== $pagenow || ! isset( $_GET['taxonomy'] ) || ! in_array( $_GET['taxonomy'], $this->_taxonomies, true ) ) {
				return;
			}
			?>
			<style>
			#<?php echo esc_html( $this->_key_order ); ?> {width: 4rem;}
			.column-<?php echo esc_html( $this->_key_order ); ?> {text-align: right;}
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
						var order = $('.column-<?php echo esc_html( $this->_key_order ); ?>', tag_row).html();
						var input = document.querySelector('input[name="<?php echo esc_html( $this->_key_order ); ?>"]');
						input.value = order;
					}
					return false;
				};
			});
			</script>
			<?php
		}


		// Quick Edit Function -----------------------------------------------------

		public function _cb_quick_edit_custom_box( $column_name, $screen, $name ) {
			if ( ( $column_name !== $this->_key_order ) || ! in_array( $name, $this->_taxonomies, true ) ) {
				return false;
			}
			static $print_nonce = true;
			if ( $print_nonce ) {
				$print_nonce = false;
				wp_nonce_field( 'quick_edit_action', "{$column_name}_edit_nonce" );
			}
	?>
			<fieldset>
				<div id="<?php echo esc_attr( $this->_key_order ); ?>-content" class="inline-edit-col">
					<label>
						<span class="title"><?php esc_html_e( 'Order', 'default' ); ?></span>
						<span class="input-text-wrap"><input type="text" name="<?php echo esc_attr( $column_name ); ?>" class="ptitle" value=""></span>
					</label>
				</div>
			</fieldset>
	<?php
		}


		// Actually Sort Terms -----------------------------------------------------

		public function _cb_terms_clauses( $clauses, $taxes = array(), $args = array() ) {
			if ( count( $taxes ) === 0 ) {
				return $clauses;
			}
			if ( count( $taxes ) > 1 || ! in_array( $taxes[0], $this->_taxonomies, true ) ) {
				return $clauses;
			}
			global $wpdb;

			$orderby = isset( $args['orderby'] ) ? $args['orderby'] : '';
			if ( $orderby !== 'name' && $orderby !== $this->_key_order ) {
				return $clauses;
			}
			$clauses['fields'] .= ', tm.meta_key, tm.meta_value';
			$clauses['join']   .= " LEFT OUTER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id AND tm.meta_key = '{$this->_key_order}'";

			$order = isset( $args['order'] ) ? $args['order'] : 'ASC';

			$clauses['orderby'] = str_replace( 'ORDER BY', "ORDER BY tm.meta_value+0 $order,", $clauses['orderby'] );
			return $clauses;
		}

		public function _cb_get_the_terms( $terms, $post_id, $taxonomy ) {
			return $this->sort_terms( $terms, $taxonomy );
		}

		public function sort_terms( $terms_or_term_ids, $taxonomy ) {
			if ( ! in_array( $taxonomy, $this->_taxonomies, true ) ) {
				return $terms_or_term_ids;
			}
			$ts = array();
			foreach ( $terms_or_term_ids as $t ) {
				$term_id = is_int( $t ) ? $t : $t->term_id;
				$idx     = (int) get_term_meta( $term_id, $this->_key_order, true );
				$ts[]    = array( $idx, $t );
			}
			usort(
				$ts,
				function ( $a, $b ) {
					if ( $a[0] === $b[0] ) {
						return 0;
					}
					return ( $a[0] < $b[0] ) ? -1 : 1;
				}
			);
			return array_map(
				function ( $t ) {
					return $t[1];
				},
				$ts
			);
		}


		// Automatic Meta Field for Posts ------------------------------------------

		public function _cb_save_post( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( ! in_array( $post_type, $this->_post_types_meta_key_added, true ) ) {
				return;
			}
			foreach ( $this->_taxonomies as $tax ) {
				$this->_update_order_post_meta( $post_id, $tax );
			}
		}

		private function _update_post_meta( $term_id, $taxonomy ) {
			$ps = get_posts(
				array(
					'post_type' => $this->_post_types_meta_key_added,
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy,
							'terms'    => $term_id,
						),
					),
				)
			);
			if ( empty( $ps ) ) {
				return;
			}
			foreach ( $ps as $p ) {
				$this->_update_order_post_meta( $p->ID, $taxonomy );
			}
		}

		private function _update_order_post_meta( $post_id, $taxonomy ) {
			$ts = wp_get_post_terms( $post_id, $taxonomy );
			if ( is_wp_error( $ts ) || empty( $ts ) ) {
				return;
			}
			$key = $this->get_order_post_meta_key( $taxonomy );
			delete_post_meta( $post_id, $key );

			foreach ( $ts as $t ) {
				$order = $this->get_order( $t );
				add_post_meta( $post_id, $key, $order );
			}
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

	function add_order_post_meta_to_post( $post_type_s ) {
		\st\OrderedTerm::get_instance()->add_order_post_meta_to_post( $post_type_s );
	}

	function get_order_post_meta_key( $taxonomy ) {
		return \st\OrderedTerm::get_instance()->get_order_post_meta_key( $taxonomy );
	}

	function insert_terms( $slugs_to_labels, $taxonomy, $args ) {
		\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
	}

	function set_terms_with_order( $taxonomy, $slugs_to_labels, $orders, $parent_id = 0, $force_update = false ) {
		$args = array(
			'parent'       => $parent_id,
			'orders'       => $orders,
			'force_update' => $force_update,
		);
		\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
	}

	function set_terms_with_order_and_meta( $taxonomy, $slugs_to_labels, $orders, $meta, $parent_id = 0, $force_update = false ) {
		$args = array(
			'parent'       => $parent_id,
			'orders'       => $orders,
			'meta'         => $meta,
			'force_update' => $force_update,
		);
		\st\OrderedTerm::get_instance()->insert_terms( $slugs_to_labels, $taxonomy, $args );
	}

}  // namespace st\ordered_term
