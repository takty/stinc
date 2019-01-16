<?php
namespace st\sticky;

/**
 *
 * Sticky for Custom Post Types
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-15
 *
 */


function make_custom_post_type_sticky( $post_types, $key_prefix = '_' ) {
	add_action( 'post_submitbox_misc_actions', function ( $post ) use ( $post_types, $key_prefix ) {
		if ( ! in_array( $post->post_type, $post_types, true ) ) return;
		wp_nonce_field( "{$key_prefix}sticky", "{$key_prefix}sticky_nonce" );
		$sticky = get_post_meta( get_the_ID(), "{$key_prefix}sticky", true );
?>
		<div class="misc-pub-section">
			<span style="margin-left: 18px;">
				<label><input type="checkbox" name="<?= $key_prefix ?>sticky" id="<?= $key_prefix ?>sticky"<?= $sticky ? ' checked' : '' ?>/><?= __( 'Make this post sticky' ) ?></label>
			</span>
		</div>
<?php
	} );
	foreach ( $post_types as $post_type ) {
		add_action( "save_post_$post_type", function ( $post_id ) use ( $key_prefix ) {
			if ( ! isset( $_POST["{$key_prefix}sticky_nonce"] ) ) return;
			if ( ! wp_verify_nonce( $_POST["{$key_prefix}sticky_nonce"], "{$key_prefix}sticky" ) ) return;
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
			update_post_meta( $post_id, "{$key_prefix}sticky", isset( $_POST["{$key_prefix}sticky"] ) );
		} );
	}
}

function get_sticky_posts( $args ) {
	if ( ! isset( $args['meta_query'] ) ) $args['meta_query'] = [];
	$args['meta_query'][] = ['key' => '_sticky', 'value' => '1'];
	return get_posts( $args );
}
