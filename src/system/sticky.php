<?php
namespace st\sticky;
/**
 *
 * Sticky for Custom Post Types
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-12
 *
 */


const PMK_STICKY = '_sticky';

$_post_types = [];


function make_custom_post_type_sticky( $post_type_s ) {
	global $_post_types;
	if ( count( $_post_types ) === 0 ) {
		_set_action_post_submitbox_misc_actions();
		_set_action_post_class();
	}
	$post_types = is_array( $post_type_s ) ? $post_type_s : [ $post_type_s ];
	foreach ( $post_types as $pt ) {
		_add_action_save_post( $pt );
	}
	array_merge( $_post_types, $post_types );
}


// -----------------------------------------------------------------------------


function _set_action_post_submitbox_misc_actions() {
	global $_post_types;
	add_action( 'post_submitbox_misc_actions', function ( $post ) use ( &$_post_types ) {
		if ( ! in_array( $post->post_type, $_post_types, true ) ) return;

		wp_nonce_field( '_sticky', '_sticky_nonce' );
		$sticky = get_post_meta( get_the_ID(), PMK_STICKY, true );
?>
		<div class="misc-pub-section">
			<span style="margin-left: 18px;">
				<label>
					<input type="checkbox" name="_sticky" id="_sticky"<?php echo $sticky ? ' checked' : '' ?>/>
					<?php echo __( 'Make this post sticky' ) ?>
				</label>
			</span>
		</div>
<?php
	} );
}

function _set_action_post_class() {
	global $_post_types;
	add_action( 'post_class', function ( $classes, $class, $post_id ) use ( &$_post_types ) {
		if ( is_admin() ) return $classes;
		if ( ! in_array( get_post_type( $post_id ), $_post_types, true ) ) return $classes;
		$is_sticky = get_post_meta( $post_id, PMK_STICKY, true );
		if ( $is_sticky ) $classes[] = 'sticky';
		return $classes;
	} );
}

function _add_action_save_post( $post_type ) {
	add_action( "save_post_$post_type", function ( $post_id ) {
		if ( ! isset( $_POST['_sticky_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST['_sticky_nonce'], '_sticky' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		update_post_meta( $post_id, PMK_STICKY, isset( $_POST[ PMK_STICKY ] ) );
	} );
}
