<?php
namespace st\sticky;
/**
 *
 * Sticky for Custom Post Types
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-11
 *
 */


const PMK_STICKY = '_sticky';


function make_custom_post_type_sticky( $post_types ) {
	add_action( 'post_submitbox_misc_actions', function ( $post ) use ( $post_types, $key_prefix ) {
		if ( ! in_array( $post->post_type, $post_types, true ) ) return;
		wp_nonce_field( '_sticky', '_sticky_nonce' );
		$sticky = get_post_meta( get_the_ID(), PMK_STICKY, true );
?>
		<div class="misc-pub-section">
			<span style="margin-left: 18px;">
				<label>
					<input type="checkbox" name="<?php echo $key_prefix ?>sticky" id="<?php echo $key_prefix ?>sticky"<?php echo $sticky ? ' checked' : '' ?>/>
					<?php echo __( 'Make this post sticky' ) ?>
				</label>
			</span>
		</div>
<?php
	} );
	foreach ( $post_types as $post_type ) {
		add_action( "save_post_$post_type", function ( $post_id ) use ( $key_prefix ) {
			if ( ! isset( $_POST['_sticky_nonce'] ) ) return;
			if ( ! wp_verify_nonce( $_POST['_sticky_nonce'], '_sticky' ) ) return;
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			update_post_meta( $post_id, PMK_STICKY, isset( $_POST[ PMK_STICKY ] ) );
		} );
	}
}
