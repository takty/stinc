/**
 *
 * Link Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-14
 *
 */


function st_link_picker_initialize_admin(key, is_internal_only = false, max_count = false) {
	const NS = 'st-link-picker';

	const CLS_TABLE       = NS + '-table';
	const CLS_ITEM        = NS + '-item';
	const CLS_ITEM_TEMP   = NS + '-item-template';
	const CLS_ITEM_PH     = NS + '-item-placeholder';
	const CLS_ITEM_DEL    = NS + '-item-deleted';
	const CLS_HANDLE      = NS + '-handle';
	const CLS_DEL         = NS + '-delete';
	const CLS_URL_OPENER  = NS + '-url-opener';
	const CLS_SEL         = NS + '-select';
	const CLS_ADD_ROW     = NS + '-add-row';
	const CLS_ADD         = NS + '-add';

	const CLS_URL         = NS + '-url';
	const CLS_TITLE       = NS + '-title';
	const CLS_POST_ID     = NS + '-post-id';

	const STR_ADD = document.getElementsByClassName(CLS_ADD)[0].innerText;
	const STR_SEL = document.getElementsByClassName(CLS_SEL)[0].innerText;

	const id     = key;
	const count  = document.getElementById(id);
	const body   = document.querySelector('#' + id + ' + div');

	const tbl    = body.getElementsByClassName(CLS_TABLE)[0];
	const items  = tbl.getElementsByClassName(CLS_ITEM);
	const temp   = tbl.getElementsByClassName(CLS_ITEM_TEMP)[0];
	const addRow = tbl.getElementsByClassName(CLS_ADD_ROW)[0];
	const add    = tbl.getElementsByClassName(CLS_ADD)[0];

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		axis       : 'y',
		containment: 'parent',
		cursor     : 'move',
		handle     : '.' + CLS_HANDLE,
		items      : '> .' + CLS_ITEM,
		placeholder: CLS_ITEM_PH,
		update     : reorder_item_ids,
	});

	reorder_item_ids();
	for (var i = 0; i < items.length; i += 1) assign_event_listener(items[i]);
	if (max_count !== false && max_count <= items.length) addBtn.setAttribute('disabled', 'true');

	setLinkPicker(add, false, (target, l) => {
		add_new_item(l);
		reorder_item_ids();
		if (max_count !== false && max_count <= items.length) add.setAttribute('disabled', 'true');
	}, { isInternalOnly: is_internal_only, title: STR_ADD });


	// -------------------------------------------------------------------------


	function reorder_item_ids() {
		for (var i = 0; i < items.length; i += 1) set_item_id(i, items[i]);
		count.value = items.length;
	}

	function set_item_id(i, it) {
		const idi = id + '_' + i;
		it.id = idi;
		set_id_name_by_class(it, CLS_URL,     idi + '_url');
		set_id_name_by_class(it, CLS_TITLE,   idi + '_title');
		set_id_name_by_class(it, CLS_DEL,     idi + '_delete');
		set_id_name_by_class(it, CLS_POST_ID, idi + '_post_id');
		set_idi(it, [CLS_URL_OPENER], idi);
	}

	function set_id_name_by_class(parent, cls, id_name, only_id = false) {
		const elm = parent.getElementsByClassName(cls)[0];
		elm.id = id_name;
		if (!only_id) elm.name = id_name;
	}

	function set_idi(parent, clss, idi) {
		clss.forEach((cls) => {
			const elm = parent.getElementsByClassName(cls)[0];
			elm.setAttribute('data-idi', idi);
		});
	}

	function add_new_item(f) {
		const it = temp.cloneNode(true);

		it.getElementsByClassName(CLS_URL)[0].value = f.url;
		it.getElementsByClassName(CLS_TITLE)[0].value = f.title;
		if (is_internal_only) it.getElementsByClassName(CLS_URL)[0].readOnly = true;

		it.classList.remove(CLS_ITEM_TEMP);
		it.classList.add(CLS_ITEM);
		tbl.insertBefore(it, addRow);
		assign_event_listener(it);
	}

	function assign_event_listener(it) {
		const del    = it.getElementsByClassName(CLS_DEL)[0];
		const sel    = it.getElementsByClassName(CLS_SEL)[0];
		const opener = it.getElementsByClassName(CLS_URL_OPENER)[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				it.classList.add(CLS_ITEM_DEL);
			} else {
				it.classList.remove(CLS_ITEM_DEL);
			}
		});
		opener.addEventListener('click', function (e) {
			e.preventDefault();
			const idi = e.target.getAttribute('data-idi');
			const url = document.getElementById(idi + '_url').value;
			if (url) window.open(url);
		});
		setLinkPicker(sel, false, false, { isInternalOnly: is_internal_only, parentGen: 2, title: STR_SEL });
	}

}
