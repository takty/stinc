/**
 *
 * Link Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-10-05
 *
 */

function st_link_picker_init(key, is_internal_only) {
	var tb     = document.getElementById(key + '_tbody');
	var items  = tb.getElementsByClassName('st_link_picker_item');
	var temp   = tb.getElementsByClassName('st_link_picker_item_template')[0];
	var addRow = tb.getElementsByClassName('st_link_picker_add_row')[0];
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
		placeholder: 'st_link_picker_item_placeholder',
		update: function () {reorder_item_ids();},
	});

	reorder_item_ids();
	for (var i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	var add = tb.getElementsByClassName('st_link_picker_add')[0];
	add.addEventListener('click', function (e) {
		e.preventDefault();
		open_link_picker(function (title, url) {
			add_new_item({title: title, url: url});
			reorder_item_ids();
		});
	});

	function reorder_item_ids() {
		for (var i = 0; i < items.length; i += 1) {
			items[i].id = key + '_' + i;
			var title = items[i].getElementsByClassName('st_link_picker_title')[0];
			title.id   = key + '_' + i + '_title';
			title.name = key + '_' + i + '_title';
			var url = items[i].getElementsByClassName('st_link_picker_url')[0];
			url.id   = key + '_' + i + '_url';
			url.name = key + '_' + i + '_url';
			var del = items[i].getElementsByClassName('st_link_picker_delete')[0];
			del.id   = key + '_' + i + '_delete';
			del.name = key + '_' + i + '_delete';
			var sel = items[i].getElementsByClassName('st_link_picker_select')[0];
			sel.setAttribute('data-id', i);
		}
		count.value = items.length;
	}

	function add_new_item(f) {
		var item = temp.cloneNode(true);
		item.getElementsByClassName('st_link_picker_title')[0].value = f.title;
		item.getElementsByClassName('st_link_picker_url')[0].value = f.url;
		if (is_internal_only) item.getElementsByClassName('st_link_picker_url')[0].readonly = true;
		item.classList.remove('st_link_picker_item_template');
		item.classList.add('st_link_picker_item');
		tb.insertBefore(item, addRow);
		assign_event_listener(item);
	}

	function assign_event_listener(item) {
		var del = item.getElementsByClassName('st_link_picker_delete')[0];
		var sel = item.getElementsByClassName('st_link_picker_select')[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				e.target.parentNode.parentNode.parentNode.classList.add('st_link_picker_item_deleted');
			} else {
				e.target.parentNode.parentNode.parentNode.classList.remove('st_link_picker_item_deleted');
			}
		});
		sel.addEventListener('click', function (e) {
			e.preventDefault();
			var id = e.target.getAttribute('data-id');
			open_link_picker(function (title, url) {
				document.getElementById(key + '_' + id + '_title').value = title;
				document.getElementById(key + '_' + id + '_url').value = url;
			});
		});
	}

	function open_link_picker(callback) {
		var ta = document.getElementById(key + '_hidden_textarea');
		var d = document.getElementById(key + '_hidden_div');
		var to = null;
		var toFn = function () {
			if (ta.value !== '') {
				d.innerHTML = ta.value;
				var a = d.getElementsByTagName('a')[0];
				callback(a.innerText, a.href);
				to = null;
				jQuery('#wp-link').find('.query-results').off('river-select', onSelectFn);
				return;
			}
			to = setTimeout(toFn, 100);
		}
		var onSelectFn = function (e, li) {
			jQuery('#wp-link-text').val(li.hasClass('no-title') ? '' : li.children('.item-title').text());
		};
		ta.value = '';
		to = setTimeout(toFn, 100);
		wpLink.open(key + '_hidden_textarea');
		jQuery('#wp-link').find('.query-results').on('river-select', onSelectFn);


		// for is_internal_only behavior

		if (is_internal_only) {
			jQuery('#link-options').hide();
			jQuery('#wplink-link-existing-content').hide();
			var qrs = document.querySelectorAll('#link-selector .query-results');
			for (var i = 0; i < qrs.length; i += 1) {
				qrs[i].style.top = '48px';
			}
		}
	}

}
