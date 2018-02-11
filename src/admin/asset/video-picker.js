/**
 *
 * Video Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-07-07
 *
 */

function st_video_picker_init(key) {
	var tb = document.getElementById(key + '_body');

	var gp = null;
	var add = tb.getElementsByClassName('st_video_picker_select')[0];
	add.addEventListener('click', function (e) {
		e.preventDefault();
		if (!gp) {
			gp = create_media(false);
			gp.on('select', function () {
				var ms = gp.state().get('selection');
				ms.each(function (m) {set_item(m.toJSON());});
				del.style.visibility = '';
			});
		}
		gp.open();
	});
	var del = tb.getElementsByClassName('st_video_picker_delete')[0];
	del.addEventListener('click', function (e) {
		set_item({url: '', filename: ''});
		del.style.visibility = 'hidden';
	});

	if (document.getElementById(key + '_url').value === '') {
		del.style.visibility = 'hidden';
	}

	function set_item(f) {
		document.getElementById(key + '_url').value = f.url;
		document.getElementById(key + '_title').value = f.filename;
		tb.getElementsByClassName('st_video_picker_title')[0].innerText = f.filename;
	}

	function create_media(multiple) {
		return wp.media({
			title: document.getElementsByClassName('st_video_picker_select')[0].innerText,
			library: {type: ''},
			frame: 'select',
			multiple: multiple,
		});
	}

}
