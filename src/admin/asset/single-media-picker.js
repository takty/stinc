/**
 *
 * Single Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-07-13
 *
 */


function singleMediaPickerInit(key, opt_ns) {
	var ns = opt_ns || 'st_single_media_picker';
	initItem(ns, key);

	function initItem(ns, key) {
		var itemElm = document.getElementById(key + '_item');
		var nameElm = itemElm.getElementsByClassName(ns + '_name')[0];
		var selBtns = itemElm.getElementsByClassName(ns + '_select');
		var delBtn = itemElm.getElementsByClassName(ns + '_delete')[0];

		var itemRow = itemElm.getElementsByClassName(ns + '_item_row')[0];
		var newSelRow = itemElm.getElementsByClassName(ns + '_new_select_row')[0];

		var cm = null;
		for (var i = 0; i < selBtns.length; i += 1) {
			var selBtn = selBtns[i];
			selBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (!cm) {
					cm = createMedia(selBtn.innerText, false);
					cm.on('select', function () {
						var m = cm.state().get('selection').first();
						setItem(key, m.toJSON(), nameElm);
						itemRow.style.display = '';
						newSelRow.style.display = 'none';
					});
				}
				cm.open();
			});
		}
		delBtn.addEventListener('click', function (e) {
			setItem(key, {id: '', url: '', title: '', filename: ''}, nameElm);
			itemRow.style.display = 'none';
			newSelRow.style.display = '';
		});

		if (document.getElementById(key + '_id').value === '') {
			itemRow.style.display = 'none';
			newSelRow.style.display = '';
		} else {
			itemRow.style.display = '';
			newSelRow.style.display = 'none';
		}
	}

	function setItem(key, f, nameElm) {
		setValueToId(key + '_id',       f.id);
		setValueToId(key + '_url',      f.url);
		setValueToId(key + '_title',    f.title);
		setValueToId(key + '_filename', f.filename);
		if (nameElm) nameElm.innerText = f.filename;
	}

	function setValueToId(id, value) {
		var elm = document.getElementById(id);
		if (elm) elm.value = value;
	}

	function createMedia(title, multiple) {
		return wp.media({
			title: title,
			library: {type: ''},
			frame: 'select',
			multiple: multiple,
		});
	}
}
