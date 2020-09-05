<?php
namespace st\editor_style;
/**
 *
 * Editor Styles (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-09-04
 *
 * TinyMCE Advanced Setting:
 * {"settings":{"toolbar_1":"formatselect,bold,italic,underline,strikethrough,superscript,subscript,bullist,numlist,alignleft,aligncenter,alignright,link,unlink","toolbar_2":"undo,redo,styleselect,removeformat,forecolor,backcolor,blockquote","toolbar_3":"","toolbar_4":"","toolbar_classic_block":"formatselect,bold,italic,blockquote,bullist,numlist,alignleft,aligncenter,alignright,link,forecolor,backcolor,table,wp_help","toolbar_block":"core\/bold,core\/italic,core\/link,tadv\/removeformat","toolbar_block_side":[],"panels_block":"","options":"menubar_block,menubar,merge_toolbars,advlist","plugins":"table,advlist"},"admin_settings":{"options":"hybrid_mode,classic_paragraph_block,no_autop","disabled_editors":""}}
 *
 */


require_once __DIR__ . '/../util/url.php';


function initialize( $url_to = false, $row_index = 2, $opts = [] ) {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) return;

	if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
	$url_to = untrailingslashit( $url_to );

	add_filter( 'mce_external_plugins', function ( $plugins ) use ( $url_to ) {
		$plugins['columns'] = $url_to . '/asset/editor-command.min.js';
		return $plugins;
	} );
	add_filter( "mce_buttons_$row_index", function ( $buttons ) {
		$buttons[] = 'styleselect';
		$buttons[] = 'column_2';
		$buttons[] = 'column_3';
		$buttons[] = 'column_4';
		return $buttons;
	}, 10 );

	_add_style_formats( $opts );
	_add_quick_tags();
}

function _add_style_formats( $opts ) {
	add_filter( 'tiny_mce_before_init', function ( $settings ) use ( $opts ) {
		$ls = array_merge( [
			'button'          => 'リンク・ボタン',
			'frame'           => '囲み',
			'frame-alt'       => '囲み・他',
			'tab-page'        => 'タブ・ページ',
			'pseudo-tab-page' => '擬似タブ・ページ',
			'clear'           => 'フロート解除',
		], isset( $opts['labels'] ) ? $opts['labels'] : [] );

		$formats = [];
		if ( isset( $settings['style_formats'] ) ) {
			$formats = json_decode( $settings['style_formats'] );
		}
		$formats = array_merge( $formats, [
			[
				'title'    => $ls['button'],
				'selector' => 'a',
				'classes'  => 'button'
			],
			[
				'title'   => $ls['frame'],
				'block'   => 'div',
				'classes' => 'frame',
				'wrapper' => true
			],
			[
				'title'   => $ls['frame-alt'],
				'block'   => 'div',
				'classes' => 'frame-alt',
				'wrapper' => true
			],
			[
				'title'   => $ls['tab-page'],
				'block'   => 'div',
				'classes' => 'tab-page',
				'wrapper' => true
			],
			[
				'title'   => $ls['pseudo-tab-page'],
				'block'   => 'div',
				'classes' => 'pseudo-tab-page',
				'wrapper' => true
			],
			[
				'title'   => $ls['clear'],
				'block'   => 'div',
				'classes' => 'clear'
			]
		] );
		if ( isset( $opts['formats'] ) ) $formats = array_merge( $formats, $opts['formats'] );
		$settings['style_formats'] = json_encode( $formats );
		return $settings;
	} );
}

function _add_quick_tags() {
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
