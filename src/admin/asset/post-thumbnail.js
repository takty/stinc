/**
 *
 * Custom Post Thumbnail (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-26
 *
 */

function st_post_thumbnail_init(key) {
	const NS = 'st-post-thumbnail';

	const CLS_SEL = NS + '-select';
	const CLS_DEL = NS + '-delete';
	const CLS_IMG = NS + '-img';

	const STR_SEL = document.getElementsByClassName(CLS_SEL)[0].innerText;

	const id = key;
	const body = document.getElementById(id);

	const sel = body.getElementsByClassName(CLS_SEL)[0];
	const del = body.getElementsByClassName(CLS_DEL)[0];
	const tn = body.getElementsByClassName(CLS_IMG)[0];

	setMediaPicker(sel, false, function (target, f) {
		set_item(f);
		del.style.visibility = '';
	}, { multiple: false, type: 'image', title: STR_SEL });

	// var gp = null;
	// add.addEventListener('click', function (e) {
	// 	e.preventDefault();
	// 	if (!gp) {
	// 		gp = create_media(false);
	// 		gp.on('select', function () {
	// 			var ms = gp.state().get('selection');
	// 			ms.each(function (m) {set_item(m.toJSON());});
	// 			del.style.visibility = '';
	// 		});
	// 	}
	// 	gp.open();
	// });
	del.addEventListener('click', function (e) {
		set_item({url: '', id: ''});
		del.style.visibility = 'hidden';
	});

	if (document.getElementById(id + '_media').value === '') {
		del.style.visibility = 'hidden';
	}

	function set_item(f) {
		document.getElementById(id + '_media').value = f.id;

		if (f.url === '') {
			tn.style.backgroundImage = '';
			tn.style.paddingBottom = '0';
		} else {
			tn.style.backgroundImage = 'url(' + f.url + ')';
			tn.style.paddingBottom = '66.66%';
		}
	}

	// function create_media(multiple) {
	// 	return wp.media({
	// 		title: STR_SEL,
	// 		library: {type: ''},
	// 		frame: 'select',
	// 		multiple: multiple,
	// 	});
	// }

}
