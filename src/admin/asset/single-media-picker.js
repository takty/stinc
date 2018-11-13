/**
 *
 * Single Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-13
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

	const name_media    = key + '_media';
	const name_url      = key + '_url';
	const name_title    = key + '_title';
	const name_filename = key + '_filename';

	const id = key;

	const body         = document.querySelector('#' + id + ' + div');
	const item         = body.getElementsByClassName(CLS_ITEM)[0];
	const filename     = body.getElementsByClassName(CLS_FILENAME)[0];
	const sel          = body.getElementsByClassName(CLS_SEL)[0];
	const addRow       = body.getElementsByClassName(CLS_ADD_ROW)[0];
	const add          = body.getElementsByClassName(CLS_ADD)[0];
	const del          = body.getElementsByClassName(CLS_DEL)[0];
	const media_opener = body.getElementsByClassName(CLS_MEDIA_OPENER)[0];

	function clicked(target, m) {
		set_item(m);
		item.style.display = '';
		addRow.style.display = 'none';
	}
	setMediaPicker(sel, false, clicked, { multiple: false, title: STR_SEL });
	setMediaPicker(add, false, clicked, { multiple: false, title: STR_ADD });

	del.addEventListener('click', () => {
		set_item({ id: '', url: '', title: '', filename: '' });
		item.style.display = 'none';
		addRow.style.display = '';
	});
	media_opener.addEventListener('click', (e) => {
		e.preventDefault();
		const url_input = document.getElementById(name_url);
		const url = url_input.value;
		if (url) window.open(url);
	});

	if (document.getElementById(name_media).value) {
		item.style.display = '';
		addRow.style.display = 'none';
	} else {
		item.style.display = 'none';
		addRow.style.display = '';
	}

	function set_item(f) {
		set_val_to_id(name_media,    f.id);
		set_val_to_id(name_url,      f.url);
		set_val_to_id(name_title,    f.title);
		set_val_to_id(name_filename, f.filename);
		filename.innerText = f.filename;
	}

	function set_val_to_id(id, value) {
		const elm = document.getElementById(id);
		if (elm) elm.value = value;
	}

}
