<?php
namespace st\editor_style;
/**
 *
 * Editor Styles (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-08
 *
 * TinyMCE Advanced Setting:
 * {"settings":{"toolbar_1":"formatselect,bold,italic,underline,strikethrough,superscript,subscript,bullist,numlist,alignleft,aligncenter,alignright,link,unlink","toolbar_2":"undo,redo,styleselect,removeformat,forecolor,backcolor","toolbar_3":"","toolbar_4":"","toolbar_classic_block":"formatselect,bold,italic,blockquote,bullist,numlist,alignleft,aligncenter,alignright,link,forecolor,backcolor,table,wp_help","toolbar_block":"core\/bold,core\/italic,core\/link,tadv\/removeformat","toolbar_block_side":[],"panels_block":"","options":"menubar_block,menubar,merge_toolbars,advlist","plugins":"table,advlist"},"admin_settings":{"options":"hybrid_mode,classic_paragraph_block,no_autop","disabled_editors":""}}
 *
 */


require_once __DIR__ . '/../tag/url.php';


function initialize( $url_to = false, $row_index = 2 ) {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) return;

	if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
	$url_to = untrailingslashit( $url_to );

	add_filter( 'mce_external_plugins', function ( $plugins ) use ( $url_to ) {
		$plugins['columns'] = $url_to . '/asset/editor-command.min.js';
		return $plugins;
	} );

	add_filter( 'tiny_mce_before_init', function ( $settings ) {
		$formats = [];
		if ( isset( $settings['style_formats'] ) ) {
			$formats = json_decode( $settings['style_formats'] );
		}
		$formats = array_merge( $formats, [
			[
				'title'    => 'リンク・ボタン',
				'selector' => 'a',
				'classes'  => 'button'
			],
			[
				'title'   => '囲み',
				'block'   => 'div',
				'classes' => 'frame',
				'wrapper' => true
			],
			[
				'title'   => '囲み・他',
				'block'   => 'div',
				'classes' => 'frame-alt',
				'wrapper' => true
			],
			[
				'title'   => 'タブ・ページ',
				'block'   => 'div',
				'classes' => 'tab-page',
				'wrapper' => true
			],
			[
				'title'   => '擬似タブ・ページ',
				'block'   => 'div',
				'classes' => 'pseudo-tab-page',
				'wrapper' => true
			],
			[
				'title'   => 'フロート解除',
				'block'   => 'div',
				'classes' => 'clear'
			]
		] );
		$settings['style_formats'] = json_encode( $formats );
		return $settings;
	} );

	add_filter( "mce_buttons_$row_index", function ( $buttons ) {
		$buttons[] = 'styleselect';
		$buttons[] = 'column_2';
		$buttons[] = 'column_3';
		$buttons[] = 'column_4';
		return $buttons;
	}, 10 );

	add_quick_tags();
}

function add_quick_tags() {
	add_action( 'admin_print_footer_scripts', function () {
		if ( wp_script_is( 'quicktags' ) ) {
?>
		<script>
		QTags.addButton('qt-small', 'small', '<small>', '</small>');
		QTags.addButton('qt-h4', 'h4', '<h4>', '</h4>');
		QTags.addButton('qt-h5', 'h5', '<h5>', '</h5>');
		QTags.addButton('qt-h6', 'h6', '<h6>', '</h6>');
		</script>
<?php
		}
	} );
}
