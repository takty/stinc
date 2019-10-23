<?php
namespace st;
/**
 *
 * Multi-Language Site with Single Site (Taxonomy)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-23
 *
 */


class Multilang_Taxonomy {

	private $_core;
	private $_key_term_name = '';
	private $_key_term_desc = '';
	private $_tax_with_desc = [];
	private $_tax_with_def_lang_sname = [];

	public function __construct( $core,  $key_prefix = '_' ) {
		$this->_core = $core;
		$this->_key_term_name = $key_prefix . 'name_';
		$this->_key_term_desc = $key_prefix . 'description_';

		add_filter( 'single_cat_title',  [ $this, '_cb_single_term_title' ] );
		add_filter( 'single_tag_title',  [ $this, '_cb_single_term_title' ] );
		add_filter( 'single_term_title', [ $this, '_cb_single_term_title' ] );
	}

	public function add_taxonomy( $taxonomy_s, $opt = false ) {
		if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];
		if ( is_array( $opt ) ) {
			$has_desc = isset( $opt['has_description'] ) ? $opt['has_description'] : false;
			$has_def_lang_sname = isset( $opt['has_default_lang_singular_name'] ) ? $opt['has_default_lang_singular_name'] : false;
		} else {
			$has_desc = $opt;
			$has_def_lang_sname = false;
		}

		foreach ( $taxonomy_s as $t ) {
			add_action( "{$t}_edit_form_fields", [ $this, '_cb_term_edit_form_fields' ], 10, 2 );
			add_action( 'edited_'.$t, [ $this, '_cb_edited_term' ], 10, 2 );
		}
		if ( $has_desc ) {
			$this->_tax_with_desc = array_merge( $this->_tax_with_desc, $taxonomy_s );
		}
		if ( $has_def_lang_sname ) {
			$this->_tax_with_def_lang_sname = array_merge( $this->_tax_with_def_lang_sname, $taxonomy_s );
		}
	}


	// -------------------------------------------------------------------------


	public function get_term_name( $term, $singular = false, $lang = false ) {
		if ( $lang === false ) $lang = $this->_core->get_site_lang();
		if ( $lang === $this->_core->get_default_site_lang() ) {
			if ( $singular ) {
				$key_s  = $this->_key_term_name . $lang . '_s';
				$name_s = get_term_meta( $term->term_id, $key_s, true );
				if ( ! empty( $name_s ) ) return $name_s;
			}
			return $term->name;
		}
		$key    = $this->_key_term_name . $lang;
		$key_s  = $key . '_s';
		$name   = get_term_meta( $term->term_id, $key, true );
		$name_s = get_term_meta( $term->term_id, $key_s, true );

		if ( empty( $name ) && empty( $name_s ) ) return $term->name;
		if ( $singular ) {
			if ( ! empty( $name_s ) ) return $name_s;
			return $name;
		}
		if ( ! empty( $name ) ) return $name;
		return $name_s;
	}

	public function term_description( $term_id = 0, $taxonomy, $lang = false ) {
		if ( ! $term_id && ( is_tax() || is_tag() || is_category() ) ) {
			$t = get_queried_object();
			$term_id  = $t->term_id;
			$taxonomy = $t->taxonomy;
		}
		if ( $lang === false ) $lang = $this->_core->get_site_lang();

		$key = $this->_key_term_desc . $lang;
		$desc = get_term_meta( $term_id, $key, true );
		if ( empty( $desc ) ) return \term_description( $term_id, $taxonomy );
		return $desc;
	}

	public function get_term_list( $taxonomy, $before = '', $sep = '', $after = '', $add_link = true, $args = [] ) {
		return \st\get_term_list( $taxonomy, $before, $sep, $after, $add_link, $args );
	}

	public function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true ) {
		return \st\get_the_term_list( $post_id, $taxonomy, $before, $sep, $after, $add_link );
	}

	public function get_term_names( $taxonomy, $singular = false, $lang = false, $args = [] ) {
		return \st\get_term_names( $taxonomy, $singular, $lang, $args );
	}

	public function get_the_term_names( $post_id = 0, $taxonomy, $singular = false, $lang = false  ) {
		return \st\get_the_term_names( $post_id, $taxonomy, $singular, $lang );
	}


	// Private Functions -------------------------------------------------------


	public function _cb_single_term_title() {  // PRIVATE
		$term = get_queried_object();
		if ( ! $term ) return;
		return $this->get_term_name( $term );
	}

	public function _cb_term_edit_form_fields( $term, $taxonomy ) {  // PRIVATE
		$t_meta = get_term_meta( $term->term_id );
		$label_base = esc_html_x( 'Name', 'term name', 'default' );

		$has_desc = in_array( $taxonomy, $this->_tax_with_desc, true );
		if ( $has_desc ) {
			$label_desc_base = esc_html__( 'Description' );
		}
		$has_def_lang_sname = in_array( $taxonomy, $this->_tax_with_def_lang_sname, true );
		if ( $has_def_lang_sname ) {
			$lang   = $this->_core->get_default_site_lang();
			$label  = "$label_base [$lang]";
			$id_s   = $this->_key_term_name . $lang . '_s';
			$name_s = $this->_key_term_name . "array_s[$id_s]";
			$val_s  = isset( $t_meta[$id_s] ) ? esc_attr( $t_meta[$id_s][0] ) : '';
?>
			<tr class="form-field">
				<th><label for="<?php echo $id_s ?>"><?php echo $label ?> (Singular Form)</label></th>
				<td>
					<input type="text" name="<?php echo $name_s ?>" id="<?php echo $id_s ?>" size="40" value="<?php echo $val_s ?>" />
				</td>
			</tr>
<?php
		}
		foreach ( $this->_core->get_site_langs( false ) as $lang ) {
			$label  = "$label_base [$lang]";
			$id     = $this->_key_term_name . $lang;
			$id_s   = $id . '_s';
			$name   = $this->_key_term_name . "array[$id]";
			$name_s = $this->_key_term_name . "array_s[$id_s]";
			$val    = isset( $t_meta[$id] )   ? esc_attr( $t_meta[$id][0] )   : '';
			$val_s  = isset( $t_meta[$id_s] ) ? esc_attr( $t_meta[$id_s][0] ) : '';
?>
			<tr class="form-field">
				<th style="padding-bottom: 6px;"><label for="<?php echo $id ?>"><?php echo $label ?></label></th>
				<td style="padding-bottom: 6px;">
					<input type="text" name="<?php echo $name ?>" id="<?php echo $id ?>" size="40" value="<?php echo $val ?>" />
				</td>
			</tr>
			<tr class="form-field">
				<th style="padding-top: 6px;"><label for="<?php echo $id_s ?>"><?php echo $label ?> (Singular Form)</label></th>
				<td style="padding-top: 6px;">
					<input type="text" name="<?php echo $name_s ?>" id="<?php echo $id_s ?>" size="40" value="<?php echo $val_s ?>" />
				</td>
			</tr>
<?php
			if ( $has_desc ) {
				$label_desc = $label_desc_base . " ($lang)";
				$desc_id    = $this->_key_term_desc . $lang;
				$desc_name  = $this->_key_term_desc . "array[$desc_id]";
				$desc_val   = isset( $t_meta[$desc_id] ) ? esc_html( $t_meta[$desc_id][0] ) : '';
?>
			<tr class="form-field term-description-wrap">
				<th scope="row"><label for="<?php echo $desc_id ?>"><?php echo $label_desc ?></label></th>
				<td><textarea name="<?php echo $desc_name ?>" id="<?php echo $desc_id ?>" rows="5" cols="50" class="large-text"><?php echo $desc_val ?></textarea></td>
			</tr>
<?php
			}
		}
	}

	public function _cb_edited_term( $term_id, $taxonomy ) {  // PRIVATE
		if ( isset( $_POST[$this->_key_term_name . 'array'] ) ) {
			foreach ( $_POST[$this->_key_term_name . 'array'] as $key => $val ) {
				$this->_delete_or_update_term_meta( $term_id, $key, $val );
			}
		}
		if ( isset( $_POST[$this->_key_term_name . 'array_s'] ) ) {
			foreach ( $_POST[$this->_key_term_name . 'array_s'] as $key => $val ) {
				$this->_delete_or_update_term_meta( $term_id, $key, $val );
			}
		}
		if ( isset( $_POST[$this->_key_term_desc . 'array'] ) ) {
			foreach ( $_POST[$this->_key_term_desc . 'array'] as $key => $val ) {
				$this->_delete_or_update_term_meta( $term_id, $key, $val );
			}
		}
	}

	private function _delete_or_update_term_meta( $term_id, $key, $val ) {
		if ( empty( $val ) ) return delete_term_meta( $term_id, $key );
		return update_term_meta( $term_id, $key, $val );
	}

}
