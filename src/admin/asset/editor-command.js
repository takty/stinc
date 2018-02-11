/**
 * Editor Commands for TinyMCE
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-12-15
 *
 */


(function() {
	tinymce.create('tinymce.plugins.columns', {
		init : function (ed, url) {
			ed.addCommand('column_2', function () {
				var str = '<div class="column-2"><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div></div>&nbsp;';
				ed.execCommand('mceInsertRawHTML', false, str);
			});
			ed.addCommand('column_3', function () {
				var str = '<div class="column-3"><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div></div>&nbsp;';
				ed.execCommand('mceInsertRawHTML', false, str);
			});
			ed.addCommand('column_4', function () {
				var str = '<div class="column-4"><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div><div><p>&nbsp;</p></div></div>&nbsp;';
				ed.execCommand('mceInsertRawHTML', false, str);
			});
			ed.addButton('column_2', {
				title: '2段組',
				cmd:   'column_2',
				image: url + '/img/icon-column-2.png'
			});
			ed.addButton('column_3', {
				title: '3段組',
				cmd:   'column_3',
				image: url + '/img/icon-column-3.png'
			});
			ed.addButton('column_4', {
				title: '4段組',
				cmd:   'column_4',
				image: url + '/img/icon-column-4.png'
			});
		}
	});
	tinymce.PluginManager.add('columns', tinymce.plugins.columns);
})();
