<?php
namespace st\field;

/**
 *
 * Custom Field Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-02
 *
 */


function save_post_meta( $post_id, $key, $filter = null, $default = null ) {
	$val = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
	if ( $filter !== null && $val !== null ) {
		$val = $filter( $val );
	}
	if ( empty( $val ) ) {
		if ( $default === null ) {
			delete_post_meta( $post_id, $key );
			return;
		}
		$val = $default;
	}
	update_post_meta( $post_id, $key, $val );
}

function add_post_meta_input( $post_id, $key, $label, $type = 'text' ) {
	$val = get_post_meta( $post_id, $key, true );
	output_input_row( $label, $key, $val, $type );
}

function add_post_meta_textarea( $post_id, $key, $label ) {
	$val = get_post_meta( $post_id, $key, true );
	output_textarea_row( $label, $key, $val );
}

function add_post_meta_related_term_select( $post_id, $key, $label, $taxonomy, $field = 'slug' ) {
	$val = get_post_meta( $post_id, $key, true );
	$terms = get_the_terms( $post_id, $taxonomy );
	output_term_select_row( $label, $key, $terms, $val, $field );
}

function output_input_row( $label, $key, $val, $type = 'text' ) {
	$val = isset( $val ) ? esc_attr( $val ) : '';
?>
	<div style="margin-top:1rem;">
		<label><?php echo esc_html( $label ) ?>
		<input <?php esc_key_e( $key ) ?> type="<?php echo esc_attr( $type ) ?>" value="<?php echo $val ?>" size="64" style="width:100%;">
		</label>
	</div>
<?php
}

function output_textarea_row( $label, $key, $val ) {
	$val = isset( $val ) ? esc_attr( $val ) : '';
?>
	<div style="margin-top:1rem;">
		<label><?php echo esc_html( $label ) ?>
		<textarea <?php esc_key_e( $key ) ?> cols="64" rows="2" style="width:100%;"><?php echo $val ?></textarea>
		</label>
	</div>
<?php
}

function output_term_select_row( $label, $key, $taxonomy_or_terms, $cur_val, $field = 'slug' ) {
	if ( is_array( $taxonomy_or_terms ) ) {
		$terms = $taxonomy_or_terms;
	} else {
		$terms = get_terms( $taxonomy_or_terms );
	}
	if ( ! is_array( $terms ) ) $terms = [];
?>
	<div style="margin-top:1rem;">
		<label><?php echo esc_html( $label ) ?>
			<select style="width:100%;" name="<?php echo esc_attr( $key ) ?>">
<?php
	foreach ( $terms as $t ) {
		$_name = esc_html( $t->name );
		$field = get_term_field( $t, $field );
		$_val = esc_attr( $field );
		echo "<option value=\"{$_val}\"" . selected( $field, $cur_val, false ) . ">{$_name}</option>";
	}
?>
			</select>
		</label>
	</div>
<?php
}

function get_term_field( $term, $field ) {
	if ( $field === 'id' ) return $term->term_id;
	if ( $field === 'slug' ) return $term->slug;
	if ( $field === 'name' ) return $term->name;
	if ( $field === 'term_taxonomy_id' ) return $term->term_taxonomy_id;
	return false;
}

function esc_key_e( $key ) {
	$_key = esc_attr( $key );
	echo "name=\"$_key\" id=\"$_key\"";
}

function normalize_date( $str ) {
	$str = mb_convert_kana( $str, 'n', 'utf-8' );
	$nums = preg_split( '/\D/', $str );
	$vals = [];
	foreach ( $nums as $num ) {
		$v = (int) trim( $num );
		if ( $v !== 0 ) $vals[] = $v;
	}
	if ( 3 <= count( $vals ) ) {
		$str = sprintf( '%04d-%02d-%02d', $vals[0], $vals[1], $vals[2] );
	} else if ( count( $vals ) === 2 ) {
		$str = sprintf( '%04d-%02d', $vals[0], $vals[1] );
	} else if ( count( $vals ) === 1 ) {
		$str = sprintf( '%04d', $vals[0] );
	}
	return $str;
}


// Key with Postfix ------------------------------------------------------------

function get_post_meta_postfix( $post_id, $key, $postfixes ) {
	$vals = [];
	foreach ( $postfixes as $pf ) {
		$vals[ $pf ] = get_post_meta( $post_id, "{$key}_$pf", true );
	}
	return $vals;
}

function save_post_meta_postfix( $post_id, $key, $postfixes, $filter = null ) {
	foreach ( $postfixes as $pf ) {
		\st\field\save_post_meta( $post_id, "{$key}_$pf", $filter );
	}
}

function add_post_meta_input_postfix( $post_id, $key, $postfixes, $label, $type = 'text' ) {
	$vals = get_post_meta_postfix( $post_id, $key, $postfixes );
	output_input_row_postfix( $label, $key, $postfixes, $vals, $type );
}

function add_post_meta_textarea_postfix( $post_id, $key, $postfixes, $label ) {
	$vals = get_post_meta_postfix( $post_id, $key, $postfixes );
	output_textarea_row_postfix( $label, $key, $postfixes, $vals );
}

function output_input_row_postfix( $label, $key, $postfixes, $values, $type = 'text' ) {
?>
	<div style="margin-top:1rem;">
<?php
	foreach ( $postfixes as $pf ) {
		$_val = isset( $values[ $pf ] ) ? esc_attr( $values[ $pf ] ) : '';
		$ni = "{$key}_$pf";
?>
		<div>
			<label><?php echo esc_html( "$label [$pf]" ) ?>
			<input <?php esc_key_e( $ni ) ?> type="<?php echo esc_attr( $type ) ?>" value="<?php echo $_val ?>" size="64" style="width:100%;">
			</label>
		</div>
<?php
	}
?>
	</div>
<?php
}

function output_textarea_row_postfix( $label, $key, $postfixes, $values ) {
?>
	<div style="margin-top:1rem;">
<?php
	foreach ( $postfixes as $pf ) {
		$_val = isset( $values[ $pf ] ) ? esc_textarea( $values[ $pf ] ) : '';
		$ni = "{$key}_$pf";
?>
		<div>
			<label><?php echo esc_html( "$label [$pf]" ) ?>
			<textarea <?php esc_key_e( $ni ) ?> cols="64" rows="2" style="width:100%;"><?php echo $_val ?></textarea>
			</label>
		</div>
<?php
	}
?>
	</div>
<?php
}


// Custom Meta Box -------------------------------------------------------------

function add_rich_editor_meta_box( $key, $label, $screen, $settings = [] ) {
	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $settings ) {
			wp_nonce_field( $key, "{$key}_nonce" );
			$value = get_post_meta( $post->ID, $key, true );
			wp_editor( $value, $key, $settings );
		},
		$screen
	);
}

function save_rich_editor_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;

	save_post_meta( $post_id, $key, 'wp_kses_post' );
}

const TITLE_STYLE = 'padding:3px 8px;font-size:1.7em;line-height:100%;height:1.7em;width:100%;outline:none;margin:0 0 6px;background-color:#fff';

function add_title_content_meta_box( $key, $sub_key_title, $sub_key_content, $label, $screen ) {
	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $sub_key_title, $sub_key_content ) {
			wp_nonce_field( $key, "{$key}_nonce" );
			$title_placeholder = apply_filters( 'enter_title_here', __( 'Enter title here' ), $post );
			$title   = get_post_meta( $post->ID, $sub_key_title, true );
			$content = get_post_meta( $post->ID, $sub_key_content, true );
		?>
		<div class="st-field-title">
			<input style="<?php echo TITLE_STYLE ?>"
				type="text" size="30" spellcheck="true" autocomplete="off" placeholder="<?php echo $title_placeholder ?>"
				name="<?php echo $sub_key_title ?>" id="<?php echo $sub_key_title ?>"
				value="<?php echo esc_attr( $title ) ?>"
			>
		</div>
		<?php
			wp_editor( $content, $sub_key_content );
		},
		$screen
	);
}

function save_title_content_meta_box( $post_id, $key, $sub_key_title, $sub_key_content ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;

	save_post_meta( $post_id, $sub_key_title );
	save_post_meta( $post_id, $sub_key_content, 'wp_kses_post' );
}


// Multiple Post Meta ----------------------------------------------------------

function get_multiple_post_meta( $post_id, $base_key, $keys ) {
	$ret = [];
	$count = (int) get_post_meta( $post_id, $base_key, true );

	for ( $i = 0; $i < $count; $i += 1 ) {
		$bki = "{$base_key}_{$i}_";
		$set = [];
		foreach ( $keys as $key ) {
			$val = get_post_meta( $post_id, $bki . $key, true );
			$set[$key] = $val;
		}
		$ret[] = $set;
	}
	return $ret;
}

function get_multiple_post_meta_from_post( $base_key, $keys ) {
	$ret = [];
	$count = isset( $_POST[$base_key] ) ? (int) $_POST[$base_key] : 0;

	for ( $i = 0; $i < $count; $i += 1 ) {
		$bki = "{$base_key}_{$i}_";
		$set = [];
		foreach ( $keys as $key ) {
			$k = $bki . $key;
			$val = isset( $_POST[$k] ) ? $_POST[$k] : '';
			$set[$key] = $val;
		}
		$ret[] = $set;
	}
	return $ret;
}

function update_multiple_post_meta( $post_id, $base_key, $metas, $keys = null ) {
	$metas = array_values( $metas );
	$count = count( $metas );

	if ( $keys === null && $count > 0 ) {
		$keys = array_keys( $metas[0] );
	}

	$old_count = (int) get_post_meta( $post_id, $base_key, true );
	for ( $i = 0; $i < $old_count; $i += 1 ) {
		$bki = "{$base_key}_{$i}_";
		foreach ( $keys as $key ) {
			delete_post_meta( $post_id, $bki . $key );
		}
	}
	if ( $count === 0 ) {
		delete_post_meta( $post_id, $base_key );
		return;
	}
	update_post_meta( $post_id, $base_key, $count );
	for ( $i = 0; $i < $count; $i += 1 ) {
		$bki = "{$base_key}_{$i}_";
		$set = $metas[$i];
		foreach ( $keys as $key ) {
			update_post_meta( $post_id, $bki . $key, $set[$key] );
		}
	}
}


// Admin Columns ---------------------------------------------------------------

function set_admin_columns( $post_type, $all_columns, $sortable_columns = [] ) {
	$DEFAULT_COLUMNS = [
		'cb' => '<input type="checkbox" />',
		'title' => _x( 'Title', 'column name', 'default' ),
		'author' => __( 'Author', 'default' ),
		'date' => __( 'Date', 'default' ),
		'order' => __( 'Order', 'default' ),
	];
	$columns = [];
	$styles = [];
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
			if ( isset( $c['value'] ) && function_exists( $c['value'] ) ) {
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
			echo $fn( get_post_meta( $post_id, $column_name, true ) );
		}
	}, 10, 2 );

	if ( count( $sortable_columns ) > 0 ) set_admin_columns_sortable( $post_type, $sortable_columns );
}

function set_admin_columns_sortable( $post_type, $sortable_columns ) {
	add_filter( "manage_edit-{$post_type}_sortable_columns", function ( $cols ) use ( $sortable_columns ) {
		foreach ( $sortable_columns as $c ) {
			$tax = taxonomy_exists( $c ) ? 'taxonomy-' : '';
			$cols[ $tax . $c ] = $c;
		}
		return $cols;
	} );
	add_filter( 'request', function ( $vars ) use ( $sortable_columns ) {
		if ( ! isset( $vars['orderby'] ) ) return $vars;
		$key = $vars['orderby'];
		if ( in_array( $key, $sortable_columns, true ) && ! taxonomy_exists( $key ) ) {
			$vars = array_merge( $vars, [ 'meta_key' => $key, 'orderby' => 'meta_value' ] );
		}
		return $vars;
	} );
}
