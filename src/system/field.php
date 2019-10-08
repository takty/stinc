<?php
namespace st\field;

/**
 *
 * Custom Field Utilities
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-08
 *
 */


add_action( 'admin_enqueue_scripts', function () {
	$url_to = \st\get_file_uri( __DIR__ );
	$url_to = untrailingslashit( $url_to );
	wp_register_script( 'picker-media', $url_to . '/../admin/asset/lib/picker-media.min.js', [], 1.0, true );
	wp_register_script( 'stinc-field', $url_to . '/asset/field.min.js', ['picker-media'], 1.0, true );
	wp_register_style( 'stinc-field', $url_to . '/asset/field.min.css' );
} );


// -----------------------------------------------------------------------------


function get_post_meta_date( $post_id, $key ) {
	$ml = \st\Multilang::get_instance();

	$val = mb_trim( get_post_meta( $post_id, $key, true ) );
	$val = mysql2date( $ml->get_date_format(), $val );

	return $val;
}

function get_post_meta_lines( $post_id, $key ) {
	$ml = \st\Multilang::get_instance();

	$val  = mb_trim( get_post_meta( $post_id, $key, true ) );
	$vals = explode( "\n", $val );
	$vals = array_map( 'mb_trim', $vals );
	$vals = array_filter( $vals, function ( $e ) { return ! empty( $e ); } );
	$vals = array_values( $vals );

	return $vals;
}


// -----------------------------------------------------------------------------


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

function save_post_meta_with_wp_filter( $post_id, $key, $filter_name = null, $default = null ) {
	$val = isset( $_POST[ $key ] ) ? $_POST[ $key ] : null;
	if ( $filter_name !== null && $val !== null ) {
		$val = apply_filters( $filter_name, $val );
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

function add_separator() {
	output_separator();
}

function add_post_meta_input( $post_id, $key, $label, $type = 'text' ) {
	$val = get_post_meta( $post_id, $key, true );
	output_input_row( $label, $key, $val, $type );
}

function add_post_meta_textarea( $post_id, $key, $label, $rows = 2 ) {
	$val = get_post_meta( $post_id, $key, true );
	output_textarea_row( $label, $key, $val, $rows );
}

function add_post_meta_rich_editor( $post_id, $key, $label, $settings = [] ) {
	$val = get_post_meta( $post_id, $key, true );
	output_rich_editor_row( $label, $key, $val, $settings );
}

function add_post_meta_checkbox( $post_id, $key, $label ) {
	$val = get_post_meta( $post_id, $key, true );
	output_checkbox_row( $label, $key, $val === 'on' );
}

function add_post_meta_related_term_select( $post_id, $key, $label, $taxonomy, $field = 'slug' ) {
	$val = get_post_meta( $post_id, $key, true );
	$terms = get_the_terms( $post_id, $taxonomy );
	output_term_select_row( $label, $key, $terms, $val, $field );
}

function output_separator() {
	wp_enqueue_style( 'stinc-field' );
?>
	<hr class="stinc-field-separator">
<?php
}

function output_input_row( $label, $key, $val, $type = 'text' ) {
	wp_enqueue_style( 'stinc-field' );
	$val = isset( $val ) ? esc_attr( $val ) : '';
?>
	<div class="stinc-field-single">
		<label>
			<span><?php echo esc_html( $label ) ?></span>
			<input <?php name_id( $key ) ?> type="<?php echo esc_attr( $type ) ?>" value="<?php echo $val ?>" size="64">
		</label>
	</div>
<?php
}

function output_textarea_row( $label, $key, $val, $rows = 2 ) {
	wp_enqueue_style( 'stinc-field' );
	$val = isset( $val ) ? esc_attr( $val ) : '';
?>
	<div class="stinc-field-single">
		<label>
			<span><?php echo esc_html( $label ) ?></span>
			<textarea <?php name_id( $key ) ?> cols="64" rows="<?php echo $rows ?>"><?php echo $val ?></textarea>
		</label>
	</div>
<?php
}

function output_rich_editor_row( $label, $key, $val, $settings = [] ) {
	wp_enqueue_style( 'stinc-field' );
	$cls = '';
	if ( isset( $settings['media_buttons'] ) && $settings['media_buttons'] === false ) {
		$cls = ' no-media-button';
	}
?>
	<div class="stinc-field-rich-editor<?php echo $cls ?>">
		<label><?php echo esc_html( $label ) ?></label>
		<?php wp_editor( $val, $key, $settings ); ?>
	</div>
<?php
}

function output_checkbox_row( $label, $key, $chekced = false ) {
	wp_enqueue_style( 'stinc-field' );
?>
	<div class="stinc-field-single">
		<label>
			<span><?php echo esc_html( $label ) ?></span>
			<span class="checkbox"><input <?php name_id( $key ) ?> type="checkbox" <?php echo $chekced ? 'checked' : '' ?>></span>
		</label>
	</div>
<?php
}

function output_term_select_row( $label, $key, $taxonomy_or_terms, $cur_val, $field = 'slug' ) {
	wp_enqueue_style( 'stinc-field' );
	$terms = is_array( $taxonomy_or_terms ) ? $taxonomy_or_terms : get_terms( $taxonomy_or_terms );
	if ( ! is_array( $terms ) ) $terms = [];
?>
	<div class="stinc-field-single">
		<label>
			<span><?php echo esc_html( $label ) ?></span>
			<select name="<?php echo esc_attr( $key ) ?>">
<?php
	foreach ( $terms as $t ) {
		$_name = esc_html( $t->name );
		$val = get_term_field( $t, $field );
		$_val = esc_attr( $val );
		echo "<option value=\"{$_val}\"" . selected( $val, $cur_val, false ) . ">{$_name}</option>";
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

function name_id( $key ) {
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
	wp_enqueue_style( 'stinc-field' );
?>
	<div class="stile-field-group">
<?php
	foreach ( $postfixes as $pf ) {
		$_val = isset( $values[ $pf ] ) ? esc_attr( $values[ $pf ] ) : '';
		$ni = "{$key}_$pf";
?>
		<div class="stinc-field-single">
			<label>
				<span><?php echo esc_html( "$label [$pf]" ) ?></span>
				<input <?php name_id( $ni ) ?> type="<?php echo esc_attr( $type ) ?>" value="<?php echo $_val ?>" size="64">
			</label>
		</div>
<?php
	}
?>
	</div>
<?php
}

function output_textarea_row_postfix( $label, $key, $postfixes, $values, $rows = 2 ) {
	wp_enqueue_style( 'stinc-field' );
?>
	<div class="stile-field-group">
<?php
	foreach ( $postfixes as $pf ) {
		$_val = isset( $values[ $pf ] ) ? esc_textarea( $values[ $pf ] ) : '';
		$ni = "{$key}_$pf";
?>
		<div class="stinc-field-single">
			<label>
				<span><?php echo esc_html( "$label [$pf]" ) ?></span>
				<textarea <?php name_id( $ni ) ?> cols="64" rows="<?php echo $rows ?>"><?php echo $_val ?></textarea>
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

	save_post_meta_with_wp_filter( $post_id, $key, 'content_save_pre' );
}

function add_title_content_meta_box( $key, $sub_key_title, $sub_key_content, $label, $screen ) {
	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $sub_key_title, $sub_key_content ) {
			wp_nonce_field( $key, "{$key}_nonce" );
			wp_enqueue_style( 'stinc-field' );
			$title_placeholder = apply_filters( 'enter_title_here', __( 'Enter title here' ), $post );
			$title   = get_post_meta( $post->ID, $sub_key_title, true );
			$content = get_post_meta( $post->ID, $sub_key_content, true );
		?>
		<div class="stinc-field-title">
			<input
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

	save_post_meta_with_wp_filter( $post_id, $sub_key_title,     'title_save_pre' );
	save_post_meta_with_wp_filter( $post_id, $sub_key_content, 'content_save_pre' );
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


// Media Picker ----------------------------------------------------------------


function add_post_meta_media_picker( $post_id, $key, $label, $settings = [] ) {
	$val = get_post_meta( $post_id, $key, true );
	output_media_picker_row( $label, $key, $val, $settings );
}

function output_media_picker_row( $label, $key, $media_id = 0, $settings = [] ) {
	wp_enqueue_script( 'stinc-field' );
	wp_enqueue_style( 'stinc-field' );

	$_src = '';
	$_title = '';
	if ( $media_id ) {
		$ais = wp_get_attachment_image_src( $media_id, 'small' );
		$_src = ( $ais !== false ) ? esc_attr( $ais[0] ) : '';
		$p = get_post( $media_id );
		if ( $p ) $_title = esc_html( $p->post_title );
	}
?>
		<div id="<?php echo "{$key}-body" ?>" class="stinc-field-media-picker">
			<label><?php echo esc_html( $label ) ?></label>
			<div>
				<div>
					<a href="javascript:void(0);" style="background-image:url('<?php echo $_src ?>');" <?php name_id( "{$key}_src" ) ?> class="button stinc-field-media-picker-select"></a>
				</div>
				<div>
					<input type="text" disabled <?php name_id( "{$key}_title" ) ?> value="<?php echo $_title ?>">
					<a href="javascript:void(0);" class="stinc-field-media-picker-delete"><?php _e( 'Remove', 'default' ); ?></a>
				</div>
			</div>
			<input type="hidden" <?php name_id( $key ) ?> value="<?php echo $media_id ?>" />
			<script>window.addEventListener('load', function () { stinc_field_media_picker_initialize_admin('<?php echo $key ?>'); });</script>
		</div>
	<?php
}
