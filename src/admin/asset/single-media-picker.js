/**
 *
 * Single Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-12
 *
 */


function st_single_media_picker_initialize_admin(key) {
	const NS = 'st-single-media-picker';

	const CLS_NAME         = NS + '-name';
	const CLS_SEL_ROW      = NS + '-select-row';
	const CLS_SEL          = NS + '-select';
	const CLS_DEL          = NS + '-delete';
	const CLS_ITEM         = NS + '-item';
	const CLS_MEDIA_OPENER = NS + '-media-opener';

	const name_media    = key + '_media';
	const name_url      = key + '_url';
	const name_title    = key + '_title';
	const name_filename = key + '_filename';

	const id = key;

	const body         = document.querySelector('#' + id + ' + div');
	const item         = body.getElementsByClassName(CLS_ITEM)[0];
	const name         = body.getElementsByClassName(CLS_NAME)[0];
	const selRow       = body.getElementsByClassName(CLS_SEL_ROW)[0];
	const sels         = body.getElementsByClassName(CLS_SEL);
	const del          = body.getElementsByClassName(CLS_DEL)[0];
	const media_opener = body.getElementsByClassName(CLS_MEDIA_OPENER)[0];

	let cm = null;
	function on_click_sel(e) {
		e.preventDefault();
		if (!cm) {
			cm = create_media(false);
			cm.on('select', () => {
				const m = cm.state().get('selection').first();
				set_item(m.toJSON());
				item.style.display = '';
				selRow.style.display = 'none';
			});
		}
		cm.open();
	}
	for(let i = 0; i < sels.length; i += 1) sels[i].addEventListener('click', on_click_sel);
	del.addEventListener('click', () => {
		set_item({ id: '', url: '', title: '', filename: '' });
		item.style.display = 'none';
		selRow.style.display = '';
	});
	media_opener.addEventListener('click', (e) => {
		e.preventDefault();
		const url_input = document.getElementById(name_url);
		const url = url_input.value;
		if (url) window.open(url);
	});

	if (document.getElementById(name_media).value) {
		item.style.display = '';
		selRow.style.display = 'none';
	} else {
		item.style.display = 'none';
		selRow.style.display = '';
	}

	function set_item(f) {
		set_val_to_id(name_media,    f.id);
		set_val_to_id(name_url,      f.url);
		set_val_to_id(name_title,    f.title);
		set_val_to_id(name_filename, f.filename);
		name.innerText = f.filename;
	}

	function set_val_to_id(id, value) {
		const elm = document.getElementById(id);
		if (elm) elm.value = value;
	}

	function create_media(multiple) {
		return wp.media({
			title: body.getElementsByClassName(CLS_SEL)[0].innerText,
			library: {type: ''},
			frame: 'select',
			multiple: multiple,
		});
	}
}
