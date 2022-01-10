<?php
/**
 * Sticky for Custom Post Types
 *
 * @package Stinc
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2022-01-10
 */

namespace st\sticky;

/**
 * Disables embedded sticky post behavior.
 */
function disable_embedded_sticky_post_behavior() {
	add_action(
		'pre_get_posts',
		function ( $query ) {
			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}
			$query->set( 'ignore_sticky_posts', '1' );  // Only for embedded 'post' type.
		}
	);
}


// -----------------------------------------------------------------------------


const PMK_STICKY = '_sticky';

$_stinc_sticky_post_types = array();

/**
 * Makes custom post type sticky.
 *
 * @param mixed $post_type_s Post type(s).
 */
function make_custom_post_type_sticky( $post_type_s ) {
	global $_stinc_sticky_post_types;
	if ( count( $_stinc_sticky_post_types ) === 0 ) {
		_set_action_post_submitbox_misc_actions();
		_set_action_post_class();
	}
	$post_types = is_array( $post_type_s ) ? $post_type_s : array( $post_type_s );
	foreach ( $post_types as $pt ) {
		_add_action_save_post( $pt );
	}
	foreach ( $post_types as $pt ) {
		$_stinc_sticky_post_types[] = $pt;
	}
}


// -----------------------------------------------------------------------------


/**
 * Sets action 'post_submitbox_misc_actions'.
 *
 * @access private
 */
function _set_action_post_submitbox_misc_actions() {
	global $_stinc_sticky_post_types;
	add_action(
		'post_submitbox_misc_actions',
		function ( $post ) use ( &$_stinc_sticky_post_types ) {
			if ( ! in_array( $post->post_type, $_stinc_sticky_post_types, true ) ) {
				return;
			}
			wp_nonce_field( '_sticky', '_sticky_nonce' );
			$sticky = get_post_meta( get_the_ID(), PMK_STICKY, true );
			?>
			<div class="misc-pub-section">
				<span style="margin-left: 18px;">
					<label>
						<input type="checkbox" name="_sticky" id="_sticky"<?php echo $sticky ? ' checked' : '';  // phpcs:ignore ?>>
						<?php echo esc_html_e( 'Make this post sticky' ); ?>
					</label>
				</span>
			</div>
			<?php
		}
	);
}

/**
 * Sets action 'post_class'.
 *
 * @access private
 */
function _set_action_post_class() {
	global $_stinc_sticky_post_types;
	add_filter(
		'post_class',
		function ( $classes, $class, $post_id ) use ( &$_stinc_sticky_post_types ) {
			if ( is_admin() ) {
				return $classes;
			}
			if ( ! in_array( get_post_type( $post_id ), $_stinc_sticky_post_types, true ) ) {
				return $classes;
			}
			$is_sticky = get_post_meta( $post_id, PMK_STICKY, true );
			if ( $is_sticky ) {
				$classes[] = 'sticky';
			}
			return $classes;
		},
		10,
		3
	);
}

/**
 * Sets action 'save_post'.
 *
 * @access private
 *
 * @param string $post_type Post type.
 */
function _add_action_save_post( string $post_type ) {
	add_action(
		"save_post_$post_type",
		function ( $post_id ) {
			if ( ! isset( $_POST['_sticky_nonce'] ) ) {
				return;
			}
			// phpcs:disable
			if ( ! wp_verify_nonce( $_POST['_sticky_nonce'], '_sticky' ) ) {
				return;
			}
			// phpcs:enable
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			update_post_meta( $post_id, PMK_STICKY, isset( $_POST[ PMK_STICKY ] ) );
		}
	);
}
