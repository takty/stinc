/**
 *
 * Slide Show Admin (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-12-04
 *
 */


function st_slide_show_initialize_admin(key, is_dual = false) {
	const NS = 'st-slide-show';

	const CLS_TABLE           = NS + '-table';
	const CLS_ITEM            = NS + '-item';
	const CLS_ITEM_TEMP_IMG   = NS + '-item-template-img';
	const CLS_ITEM_TEMP_VIDEO = NS + '-item-template-video';
	const CLS_ITEM_PH         = NS + '-item-placeholder';
	const CLS_ITEM_DEL        = NS + '-item-deleted';
	const CLS_HANDLE          = NS + '-handle';
	const CLS_DEL             = NS + '-delete';
	const CLS_URL_OPENER      = NS + '-url-opener';
	const CLS_SEL_URL         = NS + '-select-url';
	const CLS_SEL_IMG         = NS + '-select-img';
	const CLS_SEL_IMG_SUB     = NS + '-select-img-sub';
	const CLS_TN_IMG          = NS + '-thumbnail-img';
	const CLS_TN_IMG_SUB      = NS + '-thumbnail-img-sub';
	const CLS_ADD_ROW         = NS + '-add-row';
	const CLS_ADD_IMG         = NS + '-add-img';
	const CLS_ADD_VIDEO       = NS + '-add-video';

	const CLS_MEDIA           = NS + '-media';
	const CLS_MEDIA_SUB       = NS + '-media-sub';
	const CLS_TYPE            = NS + '-type';
	const CLS_URL             = NS + '-url';
	const CLS_CAP             = NS + '-caption';
	const CLS_SEL_VIDEO       = NS + '-select-video';

	const STR_ADD = document.getElementsByClassName(CLS_ADD_IMG)[0].innerText;
	const STR_SEL = document.getElementsByClassName(CLS_SEL_URL)[0].innerText;

	const id     = key;
	const count  = document.getElementById(id);
	const body   = document.querySelector('#' + id + ' + div');

	const tbl      = body.getElementsByClassName(CLS_TABLE)[0];
	const items    = tbl.getElementsByClassName(CLS_ITEM);
	const tempImg = tbl.getElementsByClassName(CLS_ITEM_TEMP_IMG)[0];
	const tempVideo = tbl.getElementsByClassName(CLS_ITEM_TEMP_VIDEO)[0];
	const addRow   = tbl.getElementsByClassName(CLS_ADD_ROW)[0];
	const addImg   = tbl.getElementsByClassName(CLS_ADD_IMG)[0];
	const addVideos = tbl.getElementsByClassName(CLS_ADD_VIDEO);
	const addVideo = addVideos.length ? addVideos[0] : null;

	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

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
		set_id_name_by_class(it, CLS_MEDIA,  idi + '_media');
		set_id_name_by_class(it, CLS_TYPE,   idi + '_type');
		set_id_name_by_class(it, CLS_URL,    idi + '_url');
		set_id_name_by_class(it, CLS_DEL,    idi + '_delete');
		set_id_name_by_class(it, CLS_CAP,    idi + '_caption');
		set_id_name_by_class(it, CLS_TN_IMG, idi + '_thumbnail', true);
		set_idi(it, [CLS_SEL_URL, CLS_SEL_IMG, CLS_URL_OPENER, CLS_SEL_VIDEO], idi);

		if (is_dual) {
			set_id_name_by_class(it, CLS_MEDIA_SUB,  idi + '_media_sub');
			set_id_name_by_class(it, CLS_TN_IMG_SUB, idi + '_thumbnail_sub', true);
			set_idi(it, [CLS_SEL_IMG_SUB], idi);
		}
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

		it.getElementsByClassName(CLS_CAP)[0].value   = f.caption;
		it.getElementsByClassName(CLS_MEDIA)[0].value = f.id;
		it.getElementsByClassName(CLS_TN_IMG)[0].style.backgroundImage = "url('" + f.url + "')";

		it.classList.remove(CLS_ITEM_TEMP_IMG);
		it.classList.add(CLS_ITEM);
		tbl.insertBefore(it, addRow);
		assign_event_listener(it);
	}

	function add_new_item_video(f) {
		const it = tempVideo.cloneNode(true);

		it.getElementsByClassName(CLS_CAP)[0].value   = f.caption;
		it.getElementsByClassName(CLS_MEDIA)[0].value = f.id;
		it.getElementsByClassName(CLS_TN_IMG)[0].src = f.url;

		it.classList.remove(CLS_ITEM_TEMP_VIDEO);
		it.classList.add(CLS_ITEM);
		tbl.insertBefore(it, addRow);
		assign_event_listener(it);
	}

	function assign_event_listener(it) {
		const del       = it.getElementsByClassName(CLS_DEL)[0];
		const sel_url   = it.getElementsByClassName(CLS_SEL_URL)[0];
		const opener    = it.getElementsByClassName(CLS_URL_OPENER)[0];

		del.addEventListener('click', function (e) {
			if (e.target.checked) {
				it.classList.add(CLS_ITEM_DEL);
			} else {
				it.classList.remove(CLS_ITEM_DEL);
			}
		});
		opener.addEventListener('click', function (e) {
			e.preventDefault();
			const idi = opener.dataset.idi;
			const url = document.getElementById(idi + '_url').value;
			if (url) window.open(url);
		});
		setLinkPicker(sel_url, false, function (target, f) {
			const idi = sel_url.dataset.idi;
			document.getElementById(idi + '_url').value = f.url;
		});

		if (it.getElementsByClassName(CLS_TYPE)[0].value === 'image') {
			const sel_img = it.getElementsByClassName(CLS_SEL_IMG)[0];
			setMediaPicker(sel_img, false, function (target, f) {
				const idi = sel_img.dataset.idi;
				document.getElementById(idi + '_caption').value = f.caption;
				document.getElementById(idi + '_media').value = f.id;
				document.getElementById(idi + '_thumbnail').style.backgroundImage = 'url(' + f.url + ')';
			}, { multiple: false, type: 'image', title: STR_SEL });

			if (is_dual) {
				const sel_img_sub = it.getElementsByClassName(CLS_SEL_IMG_SUB)[0];
				setMediaPicker(sel_img_sub, false, function (target, f) {
					const idi = sel_img_sub.dataset.idi;
					document.getElementById(idi + '_media_sub').value = f.id;
					document.getElementById(idi + '_thumbnail_sub').style.backgroundImage = 'url(' + f.url + ')';
				}, { multiple: false, type: 'image', title: STR_SEL });
			}
		} else {
			const sel_video = it.getElementsByClassName(CLS_SEL_VIDEO)[0];
			setMediaPicker(sel_video, false, function (target, f) {
				const idi = sel_video.dataset.idi;
				document.getElementById(idi + '_caption').value = f.caption;
				document.getElementById(idi + '_media').value = f.id;
				document.getElementById(idi + '_thumbnail').src = f.url;
			}, { multiple: false, type: 'video', title: STR_SEL });
			const v = sel_video.getElementsByTagName('video')[0];
			v.loop = true;
			v.muted = true;
			sel_video.addEventListener('mouseenter', () => { v.play(); });
			sel_video.addEventListener('mouseleave', () => { v.pause(); });
		}
	}

}
