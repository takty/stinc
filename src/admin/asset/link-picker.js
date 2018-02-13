/**
 *
 * Link Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-13
 *
 */


function st_link_picker_init(key, is_internal_only) {
	var ns = 'st_link_picker';

	var tb     = document.getElementById(key + '_tbody');
	var items  = tb.getElementsByClassName(ns + '_item');
	var temp   = tb.getElementsByClassName(ns + '_item_template')[0];
	var addRow = tb.getElementsByClassName(ns + '_add_row')[0];
	var count  = document.getElementById(key);

	if (typeof is_internal_only === 'undefined') is_internal_only = false;

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	jQuery('#' + key + '_tbody').sortable();
	jQuery('#' + key + '_tbody').sortable('option', {
		axis: 'y',
		containment: 'parent',
		cursor: 'move',
		handle: '.st_link_picker_title_handle',
		items: '> .st_link_picker_item',
		placeholder: ns + '_item_placeholder',
		update: function () {reorder_item_ids();},
	});

	reorder_item_ids();
	for (var i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	var add = tb.getElementsByClassName(ns + '_add')[0];
	setLinkPicker(add, false, function (e, f) {
		add_new_item(f);
		reorder_item_ids();
	}, {isInternalOnly: is_internal_only});

	function reorder_item_ids() {
		for (var i = 0; i < items.length; i += 1) {
			items[i].id = key + '_' + i;
			var title = items[i].getElementsByClassName(ns + '_title')[0];
			title.id   = key + '_' + i + '_title';
			title.name = key + '_' + i + '_title';
			var url = items[i].getElementsByClassName(ns + '_url')[0];
			url.id   = key + '_' + i + '_url';
			url.name = key + '_' + i + '_url';
			var del = items[i].getElementsByClassName(ns + '_delete')[0];
			del.id   = key + '_' + i + '_delete';
			del.name = key + '_' + i + '_delete';
			var sel = items[i].getElementsByClassName(ns + '_select')[0];
			sel.setAttribute('data-id', i);
		}
		count.value = items.length;
	}

	function add_new_item(f) {
		var item = temp.cloneNode(true);
		item.getElementsByClassName(ns + '_title')[0].value = f.title;
		item.getElementsByClassName(ns + '_url')[0].value = f.url;
		if (is_internal_only) item.getElementsByClassName(ns + '_url')[0].readonly = true;
		item.classList.remove(ns + '_item_template');
		item.classList.add(ns + '_item');
		tb.insertBefore(item, addRow);
		assign_event_listener(item);
	}

	function assign_event_listener(item) {
		var delBtn = item.getElementsByClassName(ns + '_delete')[0];
		var selBtn = item.getElementsByClassName(ns + '_select')[0];

		setLinkPicker(selBtn, false, false, {isInternalOnly: is_internal_only, parentGen: 2});
		delBtn.addEventListener('click', function (e) {
			if (e.target.checked) {
				item.classList.add(ns + '_item_deleted');
			} else {
				item.classList.remove(ns + '_item_deleted');
			}
		});
	}

}
