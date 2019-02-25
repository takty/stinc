/**
 *
 * Field (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-25
 *
 */


function st_field_media_picker_initialize_admin(key) {
	const NS = 'st-field-media-picker';
	init(document.getElementById(key + '-body'));

	function init(body) {
		const del = body.getElementsByClassName(NS + '-delete')[0];
		del.addEventListener('click', () => { set_item(null, { id: '', url: '', title: '' }); });
		const sel = body.getElementsByClassName(NS + '-select')[0];
		setMediaPicker(sel, false, set_item, { multiple: false, title: sel.innerText, media_id_input: key });
	}

	function set_item(dummy, f) {
		document.getElementById(key).value = f.id;
		document.getElementById(key + '_src').style.backgroundImage = 'url(' + f.url + ')';
		document.getElementById(key + '_title').value = f.title;
	}
}
