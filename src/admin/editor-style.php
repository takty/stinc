<?php
namespace st\editor_style;

/**
 *
 * Editor Styles (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-12-12
 *
 * TinyMCE Advanced Setting:
 * {"settings":{"toolbar_1":"formatselect,bold,italic,blockquote,bullist,numlist,alignleft,aligncenter,alignright,link,unlink","toolbar_2":"undo,redo,underline,strikethrough,superscript,subscript,wp_page,table,backcolor,styleselect","toolbar_3":"","toolbar_4":"","options":"menubar,advlist","plugins":"table,advlist"},"admin_settings":{"options":"no_autop","disabled_editors":""}}
 *
 */


function initialize() {
	if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
		add_filter( 'mce_external_plugins', function ( $plugins  ) {
			$plugins['columns'] = get_template_directory_uri() .'/lib/stinc/admin/asset/editor-command.min.js';
			return $plugins;
		} );
		add_filter( 'tiny_mce_before_init', function ( $settings ) {
			$formats = [];
			if ( isset( $settings['style_formats'] )) {
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
					'title'   => 'タブ・ページ',
					'block'   => 'div',
					'classes' => 'tab-page',
					'wrapper' => true
				],
				[
					'title'   => '疑似タブ・ページ',
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
		add_filter( 'mce_buttons_2', function ( $buttons ) {
			$buttons[] = 'styleselect';
			$buttons[] = 'column_2';
			$buttons[] = 'column_3';
			$buttons[] = 'column_4';
			return $buttons;
		}, 10 );
	}
}
