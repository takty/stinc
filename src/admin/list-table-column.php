<?php
namespace st\list_table_column;
/**
 *
 * List Table Columns
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-11-15
 *
 */


function insert_default_columns( $pos = false, $cs = [] ) {
	$ns = [ 'cb', 'title', 'date' ];
	if ( $pos === false ) return array_merge( $cs, $ns );
	return array_splice( $cs, $pos, 0, $ns );
}

function insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, $pos = false, $cs = [] ) {
	$ns = [];
	if ( $add_cat ) $ns[] = ['name' => "{$post_type}_category", 'width' => '10%'];
	if ( $add_tag ) $ns[] = ['name' => "{$post_type}_tag",      'width' => '10%'];
	if ( $pos === false ) return array_merge( $cs, $ns );
	return array_splice( $cs, $pos, 0, $ns );
}

function insert_ml_tag_columns( $post_type, $pos = false, $cs ) {
	if ( ! class_exists( '\st\Multilang' ) ) return $cs;

	$ml = \st\Multilang::get_instance();
	if ( ! $ml->has_tag( $post_type ) ) return $cs;

	$ns = [ [ 'name' => $ml->get_taxonomy(), 'width' => '10%' ] ];
	if ( $pos === false ) return array_merge( $cs, $ns );
	return array_splice( $cs, $pos, 0, $ns );
}

function insert_mh_tag_columns( $post_type, $pos = false, $cs ) {
	if ( ! class_exists( '\st\Multihome' ) ) return $cs;

	$mh = \st\Multihome::get_instance();
	if ( ! $mh->has_tag( $post_type ) ) return $cs;

	$ns = [ [ 'name' => $mh->get_taxonomy(), 'width' => '10%' ] ];
	if ( $pos === false ) return array_merge( $cs, $ns );
	return array_splice( $cs, $pos, 0, $ns );
}


// -----------------------------------------------------------------------------


function set_admin_columns( $post_type, $all_columns, $sortable_columns = [] ) {
	$DEFAULT_COLUMNS = [
		'cb'     => '<input type="checkbox" />',
		'title'  => _x( 'Title', 'column name', 'default' ),
		'author' => __( 'Author', 'default' ),
		'date'   => __( 'Date', 'default' ),
		'order'  => __( 'Order', 'default' ),
	];
	$columns = [];
	$styles  = [];
	$val_fns = [];

	foreach ( $all_columns as $c ) {
		if ( is_array( $c ) ) {
			if ( taxonomy_exists( $c['name'] ) ) {
				$l = empty( $c['label'] ) ? get_taxonomy( $c['name'] )->labels->name : $c['label'];
				$columns[ 'taxonomy-' . $c['name'] ] = $l;
			} else {
				$columns[ $c['name'] ] = empty( $c['label'] ) ? $c['name'] : $c['label'];
			}
			// Column Styles
			if ( isset( $c['name'] ) && isset( $c['width'] ) ) {
				$tax = taxonomy_exists( $c['name'] ) ? 'taxonomy-' : '';
				$styles[] = ".column-$tax{$c['name']} {width: {$c['width']} !important;}";
			}
			// Column Value Functions
			if ( isset( $c['value'] ) && is_callable( $c['value'] ) ) {
				$val_fns[ $c['name'] ] = $c['value'];
			}
		} else {
			if ( taxonomy_exists( $c ) ) {
				$columns[ 'taxonomy-' . $c ] = get_taxonomy( $c )->labels->name;
			} else {
				$columns[ $c ] = $DEFAULT_COLUMNS[ $c ];
			}
		}
	}
	add_filter( "manage_edit-{$post_type}_columns", function () use ( $columns ) {
		return $columns;
	} );
	add_action( 'admin_head', function () use ( $post_type, $styles ) {
		if ( get_query_var( 'post_type' ) === $post_type ) {
			?><style>
			<?php echo implode( "\n", $styles ); ?>
			</style><?php
		}
	} );
	add_action( "manage_{$post_type}_posts_custom_column", function ( $column_name, $post_id ) use ( $val_fns ) {
		if ( isset( $val_fns[ $column_name ] ) ) {
			$fn = $val_fns[ $column_name ];
			echo call_user_func( $fn, get_post_meta( $post_id, $column_name, true ) );
		}
	}, 10, 2 );

	if ( count( $sortable_columns ) > 0 ) set_admin_columns_sortable( $post_type, $sortable_columns );
}

function set_admin_columns_sortable( $post_type, $sortable_columns ) {
	$names = [];
	$types = [];
	foreach ( $sortable_columns as $c ) {
		if ( is_array( $c ) ) {
			$names[] = $c['name'];
			if ( isset( $c['type'] ) ) $types[ $c['name'] ] = $c['type'];
		} else {
			$names[] = $c;
		}
	}
	add_filter( "manage_edit-{$post_type}_sortable_columns", function ( $cols ) use ( $names ) {
		foreach ( $names as $name ) {
			$tax = taxonomy_exists( $name ) ? 'taxonomy-' : '';
			$cols[ $tax . $name ] = $name;
		}
		return $cols;
	} );
	add_filter( 'request', function ( $vars ) use ( $names, $types ) {
		if ( ! isset( $vars['orderby'] ) ) return $vars;
		$key = $vars['orderby'];
		if ( in_array( $key, $names, true ) && ! taxonomy_exists( $key ) ) {
			$orderby = [ 'meta_key' => $key, 'orderby' => 'meta_value' ];
			if ( isset( $types[ $key ] ) ) {
				$orderby['meta_type'] = $types[ $key ];
			}
			$vars = array_merge( $vars, $orderby );
		}
		return $vars;
	} );
}
