/**
 *
 * Link Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-05-16
 *
 */


document.addEventListener('DOMContentLoaded', function () {
	const elms = document.querySelectorAll('*[data-picker="link"]');
	for (let i = 0; i < elms.length; i += 1) {
		setLinkPicker(elms[i]);
	}
});

function setLinkPicker(elm, cls = false, fn = null, opts = {}) {
	if (cls === false) cls = 'link';
	opts = Object.assign({ isInternalOnly: false, isLinkTargetAllowed: false, parentGen: 1, postType: null }, opts);

	const postTypeSpec = [''];
	setupPostTypeSpecification(postTypeSpec);

	elm.addEventListener('click', function (e) {
		if (elm.getAttribute('disabled')) return;
		e.preventDefault();
		createLink(function (f) {
			const parent = getParent(e.target, opts.parentGen);
			setItem(parent, cls, f);
			if (fn) fn(e.target, f);
		}, opts.isInternalOnly, opts.isLinkTargetAllowed, opts.postType);
	});

	function getParent(elm, gen) {
		while (0 < gen-- && elm.parentNode) elm = elm.parentNode;
		return elm;
	}

	function setItem(parent, cls, f) {
		setValueToCls(parent, cls + '-url', f.url);
		setValueToCls(parent, cls + '-title', f.title);
		setValueToCls(parent, cls + '-post-id', '');
	}

	function setValueToCls(parent, cls, value) {
		const elms = parent.getElementsByClassName(cls);
		for (let i = 0; i < elms.length; i += 1) {
			if (elms[i] instanceof HTMLInputElement) {
				elms[i].value = value;
			} else if (elms[i].tagName === 'A') {
				elms[i].setAttribute('href', value);
			} else {
				elms[i].innerText = value;
			}
		}
	}

	function createLink(callbackFunc, isInternalOnly, isLinkTargetAllowed, postType) {
		const id = 'picker-link-ta' + (0 | (Math.random() * 8191));
		const ta = document.createElement('textarea');
		ta.style.display = 'none';
		ta.id = id;
		document.body.appendChild(ta);

		const scan = function () {
			if (ta.value === '') return false;
			const f = readAnchorLink(ta);
			jQuery('#wp-link').find('.query-results').off('river-select', onSelect);
			document.body.removeChild(ta);
			callbackFunc(f);
			postTypeSpec[0] = null;
			return true;
		}
		const onSelect = function (e, li) {
			const val = (li.hasClass('no-title')) ? '' : li.children('.item-title').text();
			jQuery('#wp-link-text').val(val);
		};
		executeTimeoutFunc(scan, 100);
		postTypeSpec[0] = postType;
		wpLink.open(id);
		jQuery('#wp-link').find('.query-results').on('river-select', onSelect);

		if (isInternalOnly) {
			jQuery('#link-options').hide();
			jQuery('#wplink-link-existing-content').hide();
			const qrs = document.querySelectorAll('#link-selector .query-results');
			for (let i = 0; i < qrs.length; i += 1) {
				qrs[i].style.top = '48px';
			}
		} else if (!isLinkTargetAllowed) {
			jQuery('#link-options > .link-target').hide();
			const qrs = document.querySelectorAll('#link-selector .query-results');
			for (let i = 0; i < qrs.length; i += 1) {
				qrs[i].style.top = '177px';
			}
		}
	}

	function readAnchorLink(ta) {
		const d = document.createElement('div');
		d.innerHTML = ta.value;
		const a = d.getElementsByTagName('a')[0];
		return { url: a.href, title: a.innerText };
	}

	function executeTimeoutFunc(func, time) {
		const toFunc = function () {
			if (!func()) setTimeout(toFunc, time);
		}
		setTimeout(toFunc, time);
	}

	function setupPostTypeSpecification(postType) {
		$.ajaxSetup({
			beforeSend: function (jqXHR, d) {
				if (!d.data) return true;
				if (!postType[0]) return true;
				$.each(d.data.split('&'), function (i, p) {
					const kv = p.split('=');
					if (kv[0] === 'action' && kv[1] === 'wp-link-ajax') {
						d.data += '&link_picker_pt=' + postType[0];
					}
				});
				return true;
			}
		});
	}

}
