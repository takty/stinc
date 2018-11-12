/**
 *
 * Slide Show Admin (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-12
 *
 */

function st_slide_show_initialize_admin(key, is_dual) {
	if (is_dual === undefined) is_dual = false;

	const NS            = 'st-slide-show';

	const CLS_TABLE       = NS + '-table';
	const CLS_ITEM        = NS + '-item';
	const CLS_ITEM_TEMP   = NS + '-item-template';
	const CLS_HANDLE      = NS + '-handle';
	const CLS_ADD_ROW     = NS + '-add-row';
	const CLS_ADD         = NS + '-add';
	const CLS_DEL         = NS + '-delete';
	const CLS_URL         = NS + '-url';
	const CLS_URL_OPENER  = NS + '-url-opener';
	const CLS_SEL_URL     = NS + '-select-url';
	const CLS_SEL_IMG     = NS + '-select-img';
	const CLS_SEL_IMG_SUB = NS + '-select-img-sub';
	const CLS_TN_IMG      = NS + '-thumbnail-img';
	const CLS_TN_IMG_SUB  = NS + '-thumbnail-img-sub';
	const CLS_MEDIA       = NS + '-media';
	const CLS_MEDIA_SUB   = NS + '-media-sub';
	const CLS_CAP         = NS + '-caption';
	const CLS_ITEM_DEL    = NS + '-item-deleted';

	const id     = key;
	const id_hta = key + '-hidden-textarea';
	const id_hd  = key + '-hidden-div';

	const count  = document.getElementById(id);
	const body   = document.querySelector('#' + id + ' + div');

	const tbl    = body.getElementsByClassName(CLS_TABLE)[0];
	const items  = tbl.getElementsByClassName(CLS_ITEM);
	const temp   = tbl.getElementsByClassName(CLS_ITEM_TEMP)[0];
	const addRow = tbl.getElementsByClassName(CLS_ADD_ROW)[0];
	const add    = tbl.getElementsByClassName(CLS_ADD)[0];

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		axis: 'y',
		containment: 'parent',
		cursor: 'move',
		handle: '.' + CLS_HANDLE,
		items: '> .' + CLS_ITEM,
		placeholder: 'st-slide-show-item-placeholder',
		update: function () {reorder_item_ids();},
	});

	reorder_item_ids();
	for (let i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	let gp = null;
	add.addEventListener('click', function (e) {
		e.preventDefault();
		if (!gp) {
			gp = create_media(true);
			gp.on('select', function () {
				const ms = gp.state().get('selection');
				ms.each(function (m) {add_new_item(m.toJSON());});
				reorder_item_ids();
			});
		}
		gp.open();
	});

	function reorder_item_ids() {
		for (let i = 0; i < items.length; i += 1) {
			const media      = items[i].getElementsByClassName(CLS_MEDIA)[0];
			const caption    = items[i].getElementsByClassName(CLS_CAP)[0];
			const url        = items[i].getElementsByClassName(CLS_URL)[0];
			const url_opener = items[i].getElementsByClassName(CLS_URL_OPENER)[0];
			const del        = items[i].getElementsByClassName(CLS_DEL)[0];
			const thumbnail  = items[i].getElementsByClassName(CLS_TN_IMG)[0];
			const sel_url    = items[i].getElementsByClassName(CLS_SEL_URL)[0];
			const sel_img    = items[i].getElementsByClassName(CLS_SEL_IMG)[0];

			const idi = id + '_' + i;
			items[i].id                 = idi;
			media.id     = media.name   = idi + '_media';
			caption.id   = caption.name = idi + '_caption';
			url.id       = url.name     = idi + '_url';
			del.id       = del.name     = idi + '_delete';
			thumbnail.id                = idi + '_thumbnail';

			sel_url.setAttribute('data-idi', idi);
			sel_img.setAttribute('data-idi', idi);
			url_opener.setAttribute('data-idi', idi);

			if (is_dual) {
				const media_sub     = items[i].getElementsByClassName(CLS_MEDIA_SUB)[0];
				const thumbnail_sub = items[i].getElementsByClassName(CLS_TN_IMG_SUB)[0];
				const sel_img_sub   = items[i].getElementsByClassName(CLS_SEL_IMG_SUB)[0];

				media_sub.id     = media_sub.name = idi + '_media_sub';
				thumbnail_sub.id                  = idi + '_thumbnail_sub';

				sel_img_sub.setAttribute('data-idi', idi);
			}
		}
		count.value = items.length;
	}

	function add_new_item(f) {
		const item = temp.cloneNode(true);
		item.getElementsByClassName(CLS_CAP)[0].value = f.caption;
		item.getElementsByClassName(CLS_MEDIA)[0].value = f.id;
		item.getElementsByClassName(CLS_TN_IMG)[0].style.backgroundImage = "url('" + f.url + "')";
		item.classList.remove(CLS_ITEM_TEMP);
		item.classList.add(CLS_ITEM);
		tbl.insertBefore(item, addRow);
		assign_event_listener(item);
	}

	function assign_event_listener(item) {
		const del = item.getElementsByClassName(CLS_DEL)[0];
		const sel_url = item.getElementsByClassName(CLS_SEL_URL)[0];
		const sel_img = item.getElementsByClassName(CLS_SEL_IMG)[0];
		const url_opener = items[i].getElementsByClassName(CLS_URL_OPENER)[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				item.classList.add(CLS_ITEM_DEL);
			} else {
				item.classList.remove(CLS_ITEM_DEL);
			}
		});
		sel_url.addEventListener('click', function (e) {
			e.preventDefault();
			const idi = e.target.getAttribute('data-idi');
			open_link_picker(function (title, url) {
				document.getElementById(idi + '_url').value = url;
			});
		});
		let p = null;
		sel_img.addEventListener('click', function (e) {
			e.preventDefault();
			const idi = e.target.getAttribute('data-idi');
			if (!p) {
				p = create_media(false);
				p.on('select', function () {
					const f = p.state().get('selection').first().toJSON();
					document.getElementById(idi + '_caption').value = f.caption;
					document.getElementById(idi + '_media').value = f.id;
					document.getElementById(idi + '_thumbnail').style.backgroundImage = 'url(' + f.url + ')';
				});
			}
			p.open();
		});
		url_opener.addEventListener('click', function (e) {
			e.preventDefault();
			const idi = e.target.getAttribute('data-idi');
			const url_input = document.getElementById(idi + '_url');
			const url = url_input.value;
			if (url) window.open(url);
		});
		if (is_dual) {
			const sel_img_sub = item.getElementsByClassName(CLS_SEL_IMG_SUB)[0];
			let p_sub = null;
			sel_img_sub.addEventListener('click', function (e) {
				e.preventDefault();
				const idi = e.target.getAttribute('data-idi');
				if (!p_sub) {
					p_sub = create_media(false);
					p_sub.on('select', function () {
						const f = p_sub.state().get('selection').first().toJSON();
						document.getElementById(idi + '_media_sub').value = f.id;
						document.getElementById(idi + '_thumbnail_sub').style.backgroundImage = 'url(' + f.url + ')';
					});
				}
				p_sub.open();
			});
		}
	}

	function create_media(multiple) {
		return wp.media({
			title: document.getElementsByClassName(CLS_ADD)[0].innerText,
			library: {type: 'image'},
			frame: 'select',
			multiple: multiple,
		});
	}

	function open_link_picker(callback) {
		const ta = document.getElementById(id_hta);
		const d = document.getElementById(id_hd);
		let to = null;
		const toFn = function () {
			if (ta.value !== '') {
				d.innerHTML = ta.value;
				const a = d.getElementsByTagName('a')[0];
				callback(a.innerText, a.href);
				to = null;
				jQuery('#wp-link').find('.query-results').off('river-select', onSelectFn);
				return;
			}
			to = setTimeout(toFn, 100);
		}
		const onSelectFn = function (e, li) {
			jQuery('#wp-link-text').val(li.hasClass('no-title') ? '' : li.children('.item-title').text());
		};
		ta.value = '';
		to = setTimeout(toFn, 100);
		wpLink.open(id_hta);
		jQuery('#wp-link').find('.query-results').on('river-select', onSelectFn);
	}

}
