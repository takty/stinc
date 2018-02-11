/**
 *
 * Background Images (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-01-04
 *
 */

function st_background_image_initialize_admin(key) {
	var NS            = 'st-background-image';

	var CLS_TABLE     = NS + '-table';
	var CLS_ITEM      = NS + '-item';
	var CLS_ITEM_TEMP = NS + '-item-template';
	var CLS_HANDLE    = NS + '-handle';
	var CLS_ADD_ROW   = NS + '-add-row';
	var CLS_ADD       = NS + '-add';
	var CLS_DEL       = NS + '-delete';
	var CLS_SEL_IMG   = NS + '-select-img';
	var CLS_TN_IMG    = NS + '-thumbnail-img';
	var CLS_MEDIA     = NS + '-media';
	var CLS_ITEM_DEL  = NS + '-item-deleted';

	var id     = key;
	var id_hta = key + '-hidden-textarea';
	var id_hd  = key + '-hidden-div';

	var count  = document.getElementById(id);
	var body   = document.querySelector('#' + id + ' + div');

	var tbl    = body.getElementsByClassName(CLS_TABLE)[0];
	var items  = tbl.getElementsByClassName(CLS_ITEM);
	var temp   = tbl.getElementsByClassName(CLS_ITEM_TEMP)[0];
	var addRow = tbl.getElementsByClassName(CLS_ADD_ROW)[0];

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		containment: 'parent',
		cursor: 'move',
		handle: '.' + CLS_HANDLE,
		items: '> .' + CLS_ITEM,
		placeholder: 'st_background_image_item_placeholder',
		update: function () {reorder_item_ids();},
	});

	reorder_item_ids();
	for (var i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	var gp = null;
	var add = tbl.getElementsByClassName(CLS_ADD)[0];
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
			var media     = items[i].getElementsByClassName(CLS_MEDIA)[0];
			var del       = items[i].getElementsByClassName(CLS_DEL)[0];
			var thumbnail = items[i].getElementsByClassName(CLS_TN_IMG)[0];
			var sel_img   = items[i].getElementsByClassName(CLS_SEL_IMG)[0];

			var idi = id + '_' + i;
			items[i].id               = idi;
			media.id     = media.name = idi + '_media';
			del.id       = del.name   = idi + '_delete';
			thumbnail.id              = idi + '_thumbnail';

			sel_img.setAttribute('data-id', i);
		}
		count.value = items.length;
	}

	function add_new_item(f) {
		var item = temp.cloneNode(true);
		item.getElementsByClassName(CLS_MEDIA)[0].value = f.id;
		item.getElementsByClassName(CLS_TN_IMG)[0].style.backgroundImage = "url('" + f.url + "')";
		item.classList.remove(CLS_ITEM_TEMP);
		item.classList.add(CLS_ITEM);
		tbl.insertBefore(item, addRow);
		assign_event_listener(item);
	}

	function assign_event_listener(item) {
		var del = item.getElementsByClassName(CLS_DEL)[0];
		var sel_img = item.getElementsByClassName(CLS_SEL_IMG)[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				item.classList.add(CLS_ITEM_DEL);
			} else {
				item.classList.remove(CLS_ITEM_DEL);
			}
		});
		var p = null;
		sel_img.addEventListener('click', function (e) {
			e.preventDefault();
			var idi = id + '_' + e.target.getAttribute('data-id');
			if (!p) {
				p = create_media(false);
				p.on('select', function () {
					var f = p.state().get('selection').first().toJSON();
					document.getElementById(idi + '_media').value = f.id;
					document.getElementById(idi + '_thumbnail').style.backgroundImage = 'url(' + f.url + ')';
				});
			}
			p.open();
		});
	}

	function create_media(multiple) {
		return wp.media({
			title: document.getElementsByClassName(CLS_ADD)[0].innerText,
			library: {type: 'image'},
			frame: 'select',
			multiple: multiple,
		});
	}

}
