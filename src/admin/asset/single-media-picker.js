/**
 *
 * Single Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-14
 *
 */


function st_single_media_picker_initialize_admin(key) {
	const NS = 'st-single-media-picker';

	const CLS_FILENAME     = NS + '-filename';
	const CLS_SEL          = NS + '-select';
	const CLS_ADD_ROW      = NS + '-add-row';
	const CLS_ADD          = NS + '-add';
	const CLS_DEL          = NS + '-delete';
	const CLS_ITEM         = NS + '-item';
	const CLS_MEDIA_OPENER = NS + '-media-opener';

	const STR_SEL = document.getElementsByClassName(CLS_SEL)[0].innerText;
	const STR_ADD = document.getElementsByClassName(CLS_ADD)[0].innerText;

	const id = key;

	const body         = document.querySelector('#' + id + ' + div');
	const item         = body.getElementsByClassName(CLS_ITEM)[0];
	const sel          = body.getElementsByClassName(CLS_SEL)[0];
	const addRow       = body.getElementsByClassName(CLS_ADD_ROW)[0];
	const add          = body.getElementsByClassName(CLS_ADD)[0];
	const del          = body.getElementsByClassName(CLS_DEL)[0];
	const media_opener = body.getElementsByClassName(CLS_MEDIA_OPENER)[0];

	if (document.getElementById(id + '_media').value) {
		item.style.display = '';
		addRow.style.display = 'none';
	} else {
		item.style.display = 'none';
		addRow.style.display = '';
	}
	assign_event_listener();


	// -------------------------------------------------------------------------


	function set_item(f) {
		document.getElementById(id + '_media').value           = f.id;
		document.getElementById(id + '_url').value             = f.url;
		document.getElementById(id + '_title').value           = f.title;
		document.getElementById(id + '_filename').value        = f.filename;
		body.getElementsByClassName(CLS_FILENAME)[0].innerText = f.filename;
	}

	function assign_event_listener() {
		del.addEventListener('click', () => {
			set_item({ id: '', url: '', title: '', filename: '' });
			item.style.display = 'none';
			addRow.style.display = '';
		});
		media_opener.addEventListener('click', (e) => {
			e.preventDefault();
			const url = document.getElementById(id + '_url').value;
			if (url) window.open(url);
		});
		function clicked(target, m) {
			set_item(m);
			item.style.display = '';
			addRow.style.display = 'none';
		}
		setMediaPicker(sel, false, clicked, { multiple: false, title: STR_SEL });
		setMediaPicker(add, false, clicked, { multiple: false, title: STR_ADD });
	}

}
