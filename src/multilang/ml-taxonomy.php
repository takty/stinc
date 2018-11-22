<?php
namespace st;

/**
 *
 * Multi-Language Site with Single Site (Taxonomy)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-22
 *
 */


class Multilang_Taxonomy {

	private $_core;
	private $_key_term_name = '';
	private $_key_term_desc = '';
	private $_taxonomies_with_desc = [];

	public function __construct( $core,  $key_prefix = '_' ) {
		$this->_core = $core;
		$this->_key_term_name = $key_prefix . 'name_';
		$this->_key_term_desc = $key_prefix . 'description_';

		add_filter( 'single_cat_title',  [ $this, '_cb_single_term_title' ] );
		add_filter( 'single_tag_title',  [ $this, '_cb_single_term_title' ] );
		add_filter( 'single_term_title', [ $this, '_cb_single_term_title' ] );
	}

	public function add_taxonomy( $taxonomy_s, $with_description = false ) {
		if ( ! is_array( $taxonomy_s ) ) $taxonomy_s = [ $taxonomy_s ];

		foreach ( $taxonomy_s as $t ) {
			add_action( "{$t}_edit_form_fields", [ $this, '_cb_term_edit_form_fields' ], 10, 2 );
			add_action( 'edited_'.$t, [ $this, '_cb_edited_term' ], 10, 2 );
		}
		if ( $with_description ) {
			$this->_taxonomies_with_desc = array_merge( $this->_taxonomies_with_desc, $taxonomy_s );
		}
	}

	public function get_term_name( $term, $singular = false, $lang = false ) {
		if ( $lang === false ) $lang = $this->_core->get_site_lang();
		if ( $lang === $this->_core->get_default_site_lang() ) return $term->name;

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
		$ts = empty( $args ) ? get_terms( $taxonomy ) : get_terms( $taxonomy, $args );
		if ( is_wp_error( $ts ) ) return $ts;
		if ( empty( $ts ) ) return false;

		global $wp_query;
		$term = $wp_query->queried_object;

		$links = [];
		foreach ( $ts as $t ) {
			$current = ( $term && $term instanceof WP_Term && $term->term_id === $t->term_id ) ? 'current ' : '';
			if ( $add_link ) {
				$link = get_term_link( $t, $taxonomy );
				if ( is_wp_error( $link ) ) return $link;
				$links[] = '<a href="' . esc_url( $link ) . '" rel="tag" class="' . $current . $taxonomy . '-' . $t->slug . '">' . esc_html( $this->get_term_name( $t ) ) . '</a>';
			} else {
				$links[] = '<span class="' . $current . $taxonomy . '-' . $t->slug . '">' . esc_html( $this->get_term_name( $t ) ) . '</span>';
			}
		}
		$term_links = apply_filters( "term_links-{$taxonomy}", $links );
		return $before . join( $sep, $term_links ) . $after;
	}

	public function get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '', $add_link = true ) {
		$ts = get_the_terms( $post_id, $taxonomy );
		if ( is_wp_error( $ts ) ) return $ts;
		if ( empty( $ts ) ) return false;

		$links = [];
		foreach ( $ts as $t ) {
			if ( $add_link ) {
				$link = get_term_link( $t, $taxonomy );
				if ( is_wp_error( $link ) ) return $link;
				$links[] = '<a href="' . esc_url( $link ) . '" rel="tag" class="' . $taxonomy . '-' . $t->slug . '">' . esc_html( $this->get_term_name( $t ) ) . '</a>';
			} else {
				$links[] = '<span class="' . $taxonomy . '-' . $t->slug . '">' . esc_html( $this->get_term_name( $t ) ) . '</span>';
			}
		}
		$term_links = apply_filters( "term_links-{$taxonomy}", $links );
		return $before . join( $sep, $term_links ) . $after;
	}

	public function get_the_term_names( $post_id = 0, $taxonomy, $singular = false ) {
		$ts = get_the_terms( $post_id, $taxonomy );
		if ( ! is_array( $ts ) ) return [];

		$tns = [];
		foreach ( $ts as $t ) $tns[] = $this->get_term_name( $t, $singular );
		return $tns;
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

		$with_description = in_array( $taxonomy, $this->_taxonomies_with_desc, true );
		if ( $with_description ) {
			$label_desc_base = esc_html( __( 'Description' ) );
		}
		foreach ( $this->_core->get_site_langs( false ) as $lang ) {
			$id     = $this->_key_term_name . $lang;
			$id_s   = $id . '_s';
			$label  = $label_base . " ($lang)";
			$name   = $this->_key_term_name . "array[$id]";
			$name_s = $this->_key_term_name . "array_s[$id_s]";
			$val    = isset( $t_meta[$id] )   ? esc_attr( $t_meta[$id][0] )   : '';
			$val_s  = isset( $t_meta[$id_s] ) ? esc_attr( $t_meta[$id_s][0] ) : '';
?>
			<tr class="form-field">
				<th style="padding-bottom: 6px;"><label for="<?=$id?>"><?=$label?></label></th>
				<td style="padding-bottom: 6px;">
					<input type="text" name="<?=$name?>" id="<?=$id?>" size="40" value="<?=$val?>" />
				</td>
			</tr>
			<tr class="form-field">
				<th style="padding-top: 6px;"><label for="<?=$id_s?>"><?=$label?> (Singular Form)</label></th>
				<td style="padding-top: 6px;">
					<input type="text" name="<?=$name_s?>" id="<?=$id_s?>" size="40" value="<?=$val_s?>" />
				</td>
			</tr>
<?php
			if ( $with_description ) {
				$label_desc = $label_desc_base . " ($lang)";
				$desc_id    = $this->_key_term_desc . $lang;
				$desc_name  = $this->_key_term_desc . "array[$desc_id]";
				$desc_val   = isset( $t_meta[$desc_id] ) ? esc_html( $t_meta[$desc_id][0] ) : '';
?>
			<tr class="form-field term-description-wrap">
				<th scope="row"><label for="<?=$desc_id?>"><?=$label_desc?></label></th>
				<td><textarea name="<?=$desc_name?>" id="<?=$desc_id?>" rows="5" cols="50" class="large-text"><?=$desc_val?></textarea></td>
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
