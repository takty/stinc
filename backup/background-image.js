/**
 *
 * Background Images (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-08-11
 *
 */


function st_background_image_initialize_admin(key) {
	const NS = 'st-background-image';

	const CLS_TABLE           = NS + '-table';
	const CLS_ITEM            = NS + '-item';
	const CLS_ITEM_TEMP_IMG   = NS + '-item-template-img';
	const CLS_ITEM_TEMP_VIDEO = NS + '-item-template-video';
	const CLS_ITEM_PH         = NS + '-item-placeholder';
	const CLS_ITEM_DEL        = NS + '-item-deleted';
	const CLS_HANDLE          = NS + '-handle';
	const CLS_DEL             = NS + '-delete';
	const CLS_SEL_IMG         = NS + '-select-img';
	const CLS_SEL_VIDEO       = NS + '-select-video';
	const CLS_TN_IMG          = NS + '-thumbnail-img';
	const CLS_ADD_ROW         = NS + '-add-row';
	const CLS_ADD_IMG         = NS + '-add-img';
	const CLS_ADD_VIDEO       = NS + '-add-video';

	const CLS_TYPE     = NS + '-type';
	const CLS_MEDIA    = NS + '-media';
	const CLS_TITLE    = NS + '-title';
	const CLS_FILENAME = NS + '-filename';

	const STR_ADD = document.getElementsByClassName(CLS_ADD_IMG)[0].innerText;

	const id    = key;
	const count = document.getElementById(id);
	const body  = document.querySelector('#' + id + ' + div');

	const tbl       = body.getElementsByClassName(CLS_TABLE)[0];
	const items     = tbl.getElementsByClassName(CLS_ITEM);
	const tempImg   = tbl.getElementsByClassName(CLS_ITEM_TEMP_IMG)[0];
	const tempVideo = tbl.getElementsByClassName(CLS_ITEM_TEMP_VIDEO)[0];
	const addRow    = tbl.getElementsByClassName(CLS_ADD_ROW)[0];
	const addImg    = body.getElementsByClassName(CLS_ADD_IMG)[0];
	const addVideos = body.getElementsByClassName(CLS_ADD_VIDEO);
	const addVideo  = addVideos.length ? addVideos[0] : null;

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	jQuery(tbl).sortable();
	jQuery(tbl).sortable('option', {
		containment: 'parent',
		cursor     : 'move',
		handle     : '.' + CLS_HANDLE,
		items      : '> .' + CLS_ITEM,
		placeholder: CLS_ITEM_PH,
		revert     : true,
		tolerance  : 'pointer',
		update     : reorder_item_ids,
	});

	reorder_item_ids();
	for (let i = 0; i < items.length; i += 1) assign_event_listener(items[i]);

	setMediaPicker(addImg, false, (target, ms) => {
		ms.forEach((m) => { add_new_item_image(m); });
		reorder_item_ids();
	}, { multiple: true, type: 'image', title: STR_ADD });
	if (addVideo) {
		setMediaPicker(addVideo, false, (target, ms) => {
			ms.forEach((m) => { add_new_item_video(m); });
			reorder_item_ids();
		}, { multiple: true, type: 'video', title: STR_ADD });
	}


	// -------------------------------------------------------------------------


	function reorder_item_ids() {
		for (let i = 0; i < items.length; i += 1) set_item_id(i, items[i]);
		count.value = items.length;
	}

	function set_item_id(i, it) {
		const idi = id + '_' + i;
		it.id = idi;
		set_id_name_by_class(it, CLS_MEDIA,    idi + '_media');
		set_id_name_by_class(it, CLS_TITLE,    idi + '_title');
		set_id_name_by_class(it, CLS_FILENAME, idi + '_filename');
		set_id_name_by_class(it, CLS_TYPE,     idi + '_type');
		set_id_name_by_class(it, CLS_DEL,      idi + '_delete');
		set_id_name_by_class(it, CLS_TN_IMG,   idi + '_thumbnail', true);
		set_idi(it, [CLS_SEL_IMG, CLS_SEL_VIDEO], idi);
	}

	function set_id_name_by_class(parent, cls, id_name, only_id = false) {
		const elms = parent.getElementsByClassName(cls);
		if (elms.length === 0) return;
		const elm = elms[0];
		elm.id = id_name;
		if (!only_id) elm.name = id_name;
	}

	function set_idi(parent, clss, idi) {
		clss.forEach((cls) => {
			const elms = parent.getElementsByClassName(cls);
			if (elms.length === 0) return;
			const elm = elms[0];
			elm.setAttribute('data-idi', idi);
		});
	}

	function add_new_item_image(f) {
		const it = tempImg.cloneNode(true);
		it.getElementsByClassName(CLS_TN_IMG)[0].style.backgroundImage = "url('" + f.url + "')";
		set_new_item(it, f);

		it.classList.remove(CLS_ITEM_TEMP_IMG);
		it.classList.add(CLS_ITEM);
		tbl.insertBefore(it, addRow);
		assign_event_listener(it);
	}

	function add_new_item_video(f) {
		const it = tempVideo.cloneNode(true);
		it.getElementsByClassName(CLS_TN_IMG)[0].src = f.url;
		set_new_item(it, f);

		it.classList.remove(CLS_ITEM_TEMP_VIDEO);
		it.classList.add(CLS_ITEM);
		tbl.insertBefore(it, addRow);
		assign_event_listener(it);
	}

	function set_new_item(it, f) {
		it.getElementsByClassName(CLS_MEDIA)[0].value        = f.id;
		it.getElementsByClassName(CLS_FILENAME)[0].innerText = f.filename;

		if (f.title.length < f.filename.length && f.filename.indexOf(f.title) === 0) {
			it.getElementsByClassName(CLS_TITLE)[0].innerText = '';
			it.getElementsByClassName(CLS_TN_IMG)[0].parentElement.setAttribute('title', f.filename);
		} else {
			it.getElementsByClassName(CLS_TITLE)[0].innerText = f.title;
			it.getElementsByClassName(CLS_TN_IMG)[0].parentElement.setAttribute('title', f.title + '\n' + f.filename);
		}
	}

	function assign_event_listener(it) {
		const del = it.getElementsByClassName(CLS_DEL)[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				it.classList.add(CLS_ITEM_DEL);
			} else {
				it.classList.remove(CLS_ITEM_DEL);
			}
		});
		if (it.getElementsByClassName(CLS_TYPE)[0].value === 'image') {
			const sel_img = it.getElementsByClassName(CLS_SEL_IMG)[0];
			setMediaPicker(sel_img, false, function (target, f) {
				const idi = sel_img.dataset.idi;
				document.getElementById(idi + '_thumbnail').style.backgroundImage = 'url(' + f.url + ')';
				set_item(idi, f);
			}, { multiple: false, type: 'image', title: STR_ADD });
		} else {
			const sel_video = it.getElementsByClassName(CLS_SEL_VIDEO)[0];
			setMediaPicker(sel_video, false, function (target, f) {
				const idi = sel_video.dataset.idi;
				document.getElementById(idi + '_thumbnail').src = f.url;
				set_item(idi, f);
			}, { multiple: false, type: 'video', title: STR_ADD });
			const v = sel_video.getElementsByTagName('video')[0];
			v.loop  = true;
			v.muted = true;
			sel_video.addEventListener('mouseenter', () => { v.play(); });
			sel_video.addEventListener('mouseleave', () => { v.pause(); });
		}
	}

	function set_item(idi, f, pf = '') {
		document.getElementById(idi + '_media'    + pf).value     = f.id;
		document.getElementById(idi + '_filename' + pf).innerText = f.filename;

		if (0 < f.title.length && f.title.length < f.filename.length && f.filename.indexOf(f.title) === 0) {
			document.getElementById(idi + '_title' + pf).innerText = '';
		} else {
			document.getElementById(idi + '_title' + pf).innerText = f.title;
		}
	}

}
