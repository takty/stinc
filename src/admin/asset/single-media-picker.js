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

	const CLS_NAME     = NS + '-name';
	const CLS_SEL      = NS + '-select';
	const CLS_DEL      = NS + '-delete';
	const CLS_ITEM = NS + '-item';
	const CLS_SEL_ROW  = NS + '-select-row';

	const name_media    = key + '_media';
	const name_url      = key + '_url';
	const name_title    = key + '_title';
	const name_filename = key + '_filename';

	const id = key;

	const body   = document.querySelector('#' + id + ' + div');
	const item   = body.getElementsByClassName(CLS_ITEM)[0];
	const name   = body.getElementsByClassName(CLS_NAME)[0];
	const selRow = body.getElementsByClassName(CLS_SEL_ROW)[0];
	const sel    = body.getElementsByClassName(CLS_SEL)[0];
	const del    = body.getElementsByClassName(CLS_DEL)[0];

	let cm = null;
	sel.addEventListener('click', (e) => {
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
	});
	del.addEventListener('click', () => {
		set_item({ id: '', url: '', title: '', filename: '' });
		item.style.display = 'none';
		selRow.style.display = '';
	});

	if (body.getElementById(name_media).value === '') {
		item.style.display = 'none';
		selRow.style.display = '';
	} else {
		item.style.display = '';
		selRow.style.display = 'none';
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
