/**
 *
 * Custom Post Thumbnail (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-09-10
 *
 */

function st_post_thumbnail_init(key) {
	var tb = document.getElementById(key + '_body');

	var gp = null;
	var add = tb.getElementsByClassName('st_post_thumbnail_select')[0];
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
	var del = tb.getElementsByClassName('st_post_thumbnail_delete')[0];
	del.addEventListener('click', function (e) {
		set_item({url: '', filename: '', id: ''});
		del.style.visibility = 'hidden';
	});

	if (document.getElementById(key + '_id').value === '') {
		del.style.visibility = 'hidden';
	}

	function set_item(f) {
		document.getElementById(key + '_id').value = f.id;
		var tn = document.getElementById(key + '_image');
		if (f.url === '') {
			tn.style.backgroundImage = '';
			tn.style.paddingBottom = '0';
		} else {
			tn.style.backgroundImage = 'url(' + f.url + ')';
			tn.style.paddingBottom = '66.66%';
		}
	}

	function create_media(multiple) {
		return wp.media({
			title: document.getElementsByClassName('st_post_thumbnail_select')[0].innerText,
			library: {type: ''},
			frame: 'select',
			multiple: multiple,
		});
	}

}
