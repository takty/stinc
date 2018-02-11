<?php
namespace st\page_section;

/**
 *
 * Page Section
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-07-08
 *
 */


function initialize() {
	add_action( 'admin_menu', function () {
		\st\page_section\_setup_admin();
	} );
}

function _setup_admin() {
	$post_id = _get_post_id();

	$pt = get_post_meta( $post_id, '_wp_page_template', TRUE );
	if ( empty( $pt ) ) return;
	$pt_bn = basename( $pt );
	if ( $pt_bn !== 'page-section.php' && $pt_bn !== 'page-multisection.php' ) return;
	_setup_section_template_selector();
	if ( $pt_bn !== 'page-multisection.php' ) _setup_section_selector();

	$sct = get_post_meta( $post_id, '_st_section_template', TRUE );
	if ( ! empty( $sct ) && $sct !== 'default' ) {
		$sct = str_replace( '.php', '-admin.php', $sct );
		$path = get_parent_theme_file_path( $sct );
		if ( file_exists( $path ) ) {
			require_once $path;
			if ( function_exists( '\setup_section_content_template_admin' ) ) {
				\setup_section_content_template_admin();
			}
		}
	}
}

function _setup_section_template_selector() {
	$key = 'section_template_selector';
	$label = 'Section Template Selector';

	add_meta_box(
		$key . '_mb', $label,
		function ( $post ) use ( $key ) {
			wp_nonce_field( $key, $key . '_nonce' );
			\st\page_section\_output_html( $key, $post );
		},
		'page', 'side'
	);
	add_action( 'save_post_page', function ( $post_id ) use ( $key ) {
		if ( ! isset( $_POST[$key . '_nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_POST[$key . '_nonce'], $key ) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		update_post_meta( $post_id, '_st_section_template', $_POST['section_template'] );
	} );
}

function _output_html( $key, $post ) {
	$sct = get_post_meta( $post->ID, '_st_section_template', TRUE );
	$template = ! empty( $sct ) ? $sct : false;
?>
<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="section_template"><?php _e( 'Template' ); ?></label></p>
<select name="section_template" id="section_template">
	<?php section_template_dropdown( $template ); ?>
</select>
<?php
}

function _setup_section_selector() {

}

function section_template_dropdown( $default = '' ) {
	$templates = get_section_templates();
	ksort( $templates );
	foreach ( array_keys( $templates ) as $template ) {
		$selected = selected( $default, $templates[ $template ], false );
		echo "\n\t<option value='" . $templates[ $template ] . "' $selected>$template</option>";
	}
}

function get_section_templates() {
	$section_templates = [];
	$files = (array) wp_get_theme()->get_files( 'php', 1 );

	foreach ( $files as $file => $full_path ) {
		if ( ! preg_match( '|Section Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) {
			continue;
		}
		$section_templates[_cleanup_header_comment( $header[1] )] = $file;
	}
	return $section_templates;
}

function _get_post_id() {
	$post_id = '';
	if ( isset( $_GET['post'] ) || isset( $_POST['post_ID'] ) ) {
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post_ID'];
	}
	return intval( $post_id );
}

function get_section_content_template() {
	global $post;
	$pt = get_post_meta( $post->ID, '_st_section_template', TRUE );
	if ( ! empty( $pt ) && $pt !== 'default' ) {
		$pt = basename( $pt, '.php' );
		$path = 'template-parts/' . $pt;
		if ( file_exists( get_template_directory() . '/' . $path . '.php' ) ) {
			get_template_part( $path );
			return;
		}
	}
	get_template_part( 'template-parts/section', '' );
}

function the_background_class() {
	global $post;
	$type = get_post_meta( $post->ID, '_background_option_type', TRUE );
	if ( empty( $type ) ) $type = 'pattern';
	echo $type;
}

function expand_child_pages() {
	$ps = \st\get_child_pages();

	global $post;
	foreach ( $ps as $p ) {
		$post = $p;
		setup_postdata( $post );
		get_page_layout_content();
	}
	wp_reset_postdata();
}
