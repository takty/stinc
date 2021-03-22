<?php
namespace st\rich_editor;
/**
 *
 * Rich Editor Metabox
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-02-27
 *
 */


require_once __DIR__ . '/../system/field.php';


function add_rich_editor_meta_box( $key, $label, $screen, $context = 'advanced', $opts = array() ) {
	$priority = isset( $opts['priority'] ) ? $opts['priority'] : 'default';
	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $opts ) {
			wp_nonce_field( $key, "{$key}_nonce" );
			$value = get_post_meta( $post->ID, $key, true );
			wp_editor( $value, $key, $opts );
		},
		$screen, $context, $priority
	);
}

function save_rich_editor_meta_box( $post_id, $key ) {
	if ( ! isset( $_POST["{$key}_nonce"] ) ) return;
	if ( ! wp_verify_nonce( $_POST["{$key}_nonce"], $key ) ) return;

	\st\field\save_post_meta_with_wp_filter( $post_id, $key, 'content_save_pre' );
}

function add_title_content_meta_box( $key, $sub_key_title, $sub_key_content, $label, $screen ) {
	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key, $sub_key_title, $sub_key_content ) {
			wp_nonce_field( $key, "{$key}_nonce" );
			wp_enqueue_style( 'stinc-field' );
			$title_placeholder = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
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

	\st\field\save_post_meta_with_wp_filter( $post_id, $sub_key_title,     'title_save_pre' );
	\st\field\save_post_meta_with_wp_filter( $post_id, $sub_key_content, 'content_save_pre' );
}
