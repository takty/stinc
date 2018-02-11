/**
 *
 * Slideshow (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-05-02
 *
 */

function st_slideshow_init(key) {
	var tb     = document.getElementById(key + '_tbody');
	var items  = tb.getElementsByClassName('st_slideshow_item');
	var temp   = tb.getElementsByClassName('st_slideshow_item_template')[0];
	var addRow = tb.getElementsByClassName('st_slideshow_add_row')[0];
	var count  = document.getElementById(key);

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	jQuery('#' + key + '_tbody').sortable();
	jQuery('#' + key + '_tbody').sortable('option', {
		axis: 'y',
		containment: 'parent',
		cursor: 'move',
		handle: '.st_slideshow_handle',
		items: '> .st_slideshow_item',
		placeholder: 'st_slideshow_item_placeholder',
		update: function () {reorder_item_ids();},
	});

	reorder_item_ids();
	for (var i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	var gp = null;
	var add = tb.getElementsByClassName('st_slideshow_add')[0];
	add.addEventListener('click', function (e) {
		e.preventDefault();
		if (!gp) {
			gp = create_media(true);
			gp.on('select', function () {
				var ms = gp.state().get('selection');
				ms.each(function (m) {add_new_item(m.toJSON());});
				reorder_item_ids();
			});
		}
		gp.open();
	});

	function reorder_item_ids() {
		for (var i = 0; i < items.length; i += 1) {
			items[i].id = key + '_' + i;
			var media = items[i].getElementsByClassName('st_slideshow_media')[0];
			media.id   = key + '_' + i + '_media';
			media.name = key + '_' + i + '_media';
			var caption = items[i].getElementsByClassName('st_slideshow_caption')[0];
			caption.id   = key + '_' + i + '_caption';
			caption.name = key + '_' + i + '_caption';
			var url = items[i].getElementsByClassName('st_slideshow_url')[0];
			url.id   = key + '_' + i + '_url';
			url.name = key + '_' + i + '_url';
			var del = items[i].getElementsByClassName('st_slideshow_delete')[0];
			del.id   = key + '_' + i + '_delete';
			del.name = key + '_' + i + '_delete';

			var thumbnail = items[i].getElementsByClassName('st_slideshow_thumbnail_img')[0];
			thumbnail.id = key + '_' + i + '_thumbnail_img';

			var sel_img = items[i].getElementsByClassName('st_slideshow_select_img')[0];
			sel_img.setAttribute('data-id', i);
			var sel_url = items[i].getElementsByClassName('st_slideshow_select_url')[0];
			sel_url.setAttribute('data-id', i);
		}
		count.value = items.length;
	}

	function add_new_item(f) {
		var item = temp.cloneNode(true);
		item.getElementsByClassName('st_slideshow_caption')[0].value = f.caption;
		item.getElementsByClassName('st_slideshow_media')[0].value = f.id;
		item.getElementsByClassName('st_slideshow_thumbnail_img')[0].style.backgroundImage = 'url(' + f.url + ')';
		item.classList.remove('st_slideshow_item_template');
		item.classList.add('st_slideshow_item');
		tb.insertBefore(item, addRow);
		assign_event_listener(item);
	}

	function assign_event_listener(item) {
		var del = item.getElementsByClassName('st_slideshow_delete')[0];
		var sel_url = item.getElementsByClassName('st_slideshow_select_url')[0];
		var sel_img = item.getElementsByClassName('st_slideshow_select_img')[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				e.target.parentNode.parentNode.parentNode.classList.add('st_slideshow_item_deleted');
			} else {
				e.target.parentNode.parentNode.parentNode.classList.remove('st_slideshow_item_deleted');
			}
		});
		sel_url.addEventListener('click', function (e) {
			e.preventDefault();
			var id = e.target.getAttribute('data-id');
			open_link_picker(function (title, url) {
				document.getElementById(key + '_' + id + '_url').value = url;
			});
		});
		var p = null;
		sel_img.addEventListener('click', function (e) {
			e.preventDefault();
			var id = e.target.getAttribute('data-id');
			if (!p) {
				p = create_media(false);
				p.on('select', function () {
					var f = p.state().get('selection').first().toJSON();
					document.getElementById(key + '_' + id + '_caption').value = f.caption;
					document.getElementById(key + '_' + id + '_media').value = f.id;
					document.getElementById(key + '_' + id + '_thumbnail_img').style.backgroundImage = 'url(' + f.url + ')';
				});
			}
			p.open();
		});
	}

	function create_media(multiple) {
		return wp.media({
			title: document.getElementsByClassName('st_slideshow_add')[0].innerText,
			library: {type: 'image'},
			frame: 'select',
			multiple: multiple,
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
	}

}
