/**
 *
 * Link Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-14
 *
 */


function initializeLinkPicker(key, isInternalOnly = false, maxCount = false) {
	var NS = 'st-link-picker';

	var ID_ITEM_SET     = key + '-item-set';

	var CLS_ITEM        = NS + '-item';
	var CLS_ITEM_TEMP   = NS + '-item-template';
	var CLS_ITEM_PH     = NS + '-item-placeholder';
	var CLS_ADD         = NS + '-add';
	var CLS_ITEM_DEL    = NS + '-item-deleted';

	var CLS_HANDLE      = NS + '-handle';
	var CLS_SEL         = NS + '-select';

	var CLS_URL         = NS + '-url';
	var CLS_TITLE       = NS + '-title';
	var CLS_DEL         = NS + '-delete';
	var CLS_POST_ID     = NS + '-post-id';

	var count = document.getElementById(key);
	if (count.tagName !== 'INPUT') console.error('The key or id conflicts.');

	var itemSetElm   = document.getElementById(ID_ITEM_SET);
	var items        = itemSetElm.getElementsByClassName(CLS_ITEM);
	var itemTemplate = itemSetElm.getElementsByClassName(CLS_ITEM_TEMP)[0];
	var addBtn       = itemSetElm.getElementsByClassName(CLS_ADD)[0];

	jQuery('#' + ID_ITEM_SET).sortable();
	jQuery('#' + ID_ITEM_SET).sortable('option', {
		axis       : 'y',
		containment: 'parent',
		cursor     : 'move',
		handle     : '.' + CLS_HANDLE,
		items      : '> .' + CLS_ITEM,
		placeholder: CLS_ITEM_PH,
		update     : function () {reorderItems();},
	});

	reorderItems();
	for (var i = 0; i < items.length; i += 1) assignEventListener(items[i]);
	if (maxCount !== false && maxCount <= items.length) addBtn.setAttribute('disabled', 'true');

	setLinkPicker(addBtn, false, function (e, f) {
		addNewItem(f);
		reorderItems();
		if (maxCount !== false && maxCount <= items.length) addBtn.setAttribute('disabled', 'true');
	}, {isInternalOnly: isInternalOnly});

	function addNewItem(f) {
		var item = itemTemplate.cloneNode(true);
		item.classList.remove(CLS_ITEM_TEMP);
		item.classList.add(CLS_ITEM);

		item.getElementsByClassName(CLS_URL)[0].value   = f.url;
		item.getElementsByClassName(CLS_TITLE)[0].value = f.title;
		if (isInternalOnly) item.getElementsByClassName(CLS_URL)[0].readOnly = true;

		itemSetElm.insertBefore(item, itemSetElm.lastElementChild);
		assignEventListener(item);
	}

	function reorderItems() {
		for (var i = 0; i < items.length; i += 1) {
			var keyIdx = key + '_' + i;
			items[i].id = keyIdx;
			assignIdNameByClass(items[i], CLS_URL,     keyIdx + '_url');
			assignIdNameByClass(items[i], CLS_TITLE,   keyIdx + '_title');
			assignIdNameByClass(items[i], CLS_DEL,     keyIdx + '_delete');
			assignIdNameByClass(items[i], CLS_POST_ID, keyIdx + '_post_id');
		}
		count.value = items.length;
	}

	function assignIdNameByClass(parent, cls, idName) {
		var elm = parent.getElementsByClassName(cls)[0];
		elm.id   = idName;
		elm.name = idName;
	}

	function assignEventListener(item) {
		var delBtn = item.getElementsByClassName(CLS_DEL)[0];
		var selBtn = item.getElementsByClassName(CLS_SEL)[0];

		setLinkPicker(selBtn, false, false, {isInternalOnly: isInternalOnly, parentGen: 2});
		delBtn.addEventListener('click', function (e) {
			if (e.target.checked) {
				item.classList.add(CLS_ITEM_DEL);
			} else {
				item.classList.remove(CLS_ITEM_DEL);
			}
		});
	}

}
