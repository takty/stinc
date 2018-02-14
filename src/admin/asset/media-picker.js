/**
 *
 * Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-14
 *
 */

function mediaPickerInit(key, opt_ns) {
	var ns = opt_ns || 'st_media_picker';

	var count = document.getElementById(key);
	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	var itemSetElm    = document.getElementById(key + '_item_set');
	var items         = itemSetElm.getElementsByClassName(ns + '_item');
	var itemTemplate  = itemSetElm.getElementsByClassName(ns + '_item_template')[0];
	var itemInsTarget = itemSetElm.getElementsByClassName(ns + '_ins_target')[0];
	var addBtn        = itemSetElm.getElementsByClassName(ns + '_add')[0];

	jQuery('#' + key + '_item_set').sortable();
	jQuery('#' + key + '_item_set').sortable('option', {
		axis: 'y',
		containment: 'parent',
		cursor: 'move',
		handle: '.' + ns + '_title_handle',
		items: '> .' + ns + '_item',
		placeholder: ns + '_item_placeholder',
		update: function () {reorderItemIdNames();},
	});

	reorderItemIdNames();
	for (var i = 0; i < items.length; i += 1) assignEventListener(items[i]);
	count.value = items.length;

	var cm = null;
	addBtn.addEventListener('click', function (e) {
		e.preventDefault();
		if (!cm) {
			cm = createMedia(addBtn.innerText, true);
			cm.on('select', function () {
				var ms = cm.state().get('selection');
				ms.each(function (m) {
					var item = addNewItem(itemTemplate, itemInsTarget);
					var id = items.length - 1;
					setIdName(item, id);
					var nameElm = item.getElementsByClassName(ns + '_name')[0];
					setItem(key + '_' + id, m.toJSON(), nameElm);
				});
				count.value = items.length;
			});
		}
		cm.open();
	});

	function reorderItemIdNames() {
		for (var i = 0; i < items.length; i += 1) {
			setIdName(items[i], i);
		}
	}

	function addNewItem(itemTemplate, insertTarget) {
		var item = itemTemplate.cloneNode(true);
		item.classList.remove(ns + '_item_template');
		item.classList.add(ns + '_item');
		insertTarget.parentNode.insertBefore(item, insertTarget);
		assignEventListener(item);
		return item;
	}

	function setIdName(item, idx) {
		var keyIdx = key + '_' + idx;
		item.id = keyIdx;

		assignIdNameByClass(item, ns + '_id', keyIdx + '_id');
		assignIdNameByClass(item, ns + '_url', keyIdx + '_url');
		assignIdNameByClass(item, ns + '_title', keyIdx + '_title');
		assignIdNameByClass(item, ns + '_filename', keyIdx + '_filename');
		assignIdNameByClass(item, ns + '_delete', keyIdx + '_delete');

		var selBtn = item.getElementsByClassName(ns + '_select')[0];
		selBtn.setAttribute('data-id', idx);
	}

	function assignIdNameByClass(parent, cls, idName) {
		var elm = parent.getElementsByClassName(cls)[0];
		elm.id   = idName;
		elm.name = idName;
	}

	function assignEventListener(item) {
		var nameElm = item.getElementsByClassName(ns + '_name')[0];
		var selBtn = item.getElementsByClassName(ns + '_select')[0];
		var delBtn = item.getElementsByClassName(ns + '_delete')[0];

		var cm = null;
		selBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var id = e.target.getAttribute('data-id');
			if (!cm) {
				cm = createMedia(selBtn.innerText, false);
				cm.on('select', function () {
					var m = cm.state().get('selection').first();
					setItem(key + '_' + id, m.toJSON(), nameElm);
				});
			}
			cm.open();
		});
		delBtn.addEventListener('click', function (e) {
			if (e.target.checked) {
				item.classList.add(ns + '_item_deleted');
			} else {
				item.classList.remove(ns + '_item_deleted');
			}
		});
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
