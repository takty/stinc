<?php
/**
 * Custom Admin Editor
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-10
 */

namespace st;

function remove_unused_heading( $first_level = 2, $count = 3 ) {
	$hs = array_map(
		function ( $l ) {
			return "Heading $l=h$l";
		},
		range( $first_level, $first_level + $count - 1 )
	);
	add_filter(
		'tiny_mce_before_init',
		function ( $init_array ) use ( $hs ) {
			// Original from tinymce.min.js "Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre".
			$init_array['block_formats'] = 'Paragraph=p;' . implode( ';', $hs ) . ';Preformatted=pre';
			return $init_array;
		}
	);
}

function remove_taxonomy_metabox_adder_and_tabs( $taxonomies = false, $post_types = false ) {
	add_action(
		'admin_head',
		function () use ( $taxonomies, $post_types ) {
			global $pagenow, $post_type;

			if ( is_admin() && ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) ) {
				if ( false === $post_types || in_array( $post_type, $post_types, true ) ) {
					echo '<style type="text/css">';
					if ( false === $taxonomies ) {
						echo '.categorydiv div[id$="-adder"], .category-tabs{display:none;}';
						echo '.categorydiv div.tabs-panel{border:none;padding:0;}';
						echo '.categorychecklist{margin-top:4px;}';
					} else {
						foreach ( $taxonomies as $tax ) {
							echo "#$tax-adder,#$tax-tabs{display:none;}";
							echo "#$tax-all{border:none;padding:0;}";
							echo "#{$tax}checklist{margin-top:4px;}";
						}
					}
					echo '</style>';
				}
			}
		}
	);
}


// -----------------------------------------------------------------------------


function disable_taxonomy_metabox_sorting() {
	add_filter(
		'wp_terms_checklist_args',
		function ( $args ) {
			$args['checked_ontop'] = false;
			return $args;
		}
	);
}

function disable_table_resizing() {
	add_filter(
		'tiny_mce_before_init',
		function ( $mce_init ) {
			$mce_init['table_resize_bars'] = false;
			$mce_init['object_resizing']   = 'img';
			return $mce_init;
		}
	);
}

function enable_enter_title_here_label() {
	add_filter(
		'enter_title_here',
		function ( $enter_title_here, $post ) {
			$pto = get_post_type_object( $post->post_type );
			if ( isset( $pto->labels->enter_title_here ) && is_string( $pto->labels->enter_title_here ) ) {
				$enter_title_here = esc_html__( $pto->labels->enter_title_here );
			}
			return $enter_title_here;
		},
		10,
		2
	);
}
