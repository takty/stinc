/**
 *
 * Retrop: XLSX Loader (js)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-28
 *
 */


document.addEventListener('DOMContentLoaded', () => {
	const lf = document.getElementById('retrop-load-files');
	if (lf) {
		const jsonStructs = document.getElementById('retrop-structs').value;
		const url = document.getElementById('retrop-url').value;
		RETROP.loadFiles(jsonStructs, [url], '#retrop-items', (successAll) => {
			if (successAll) document.form.submit.disabled = false;
			else document.getElementById('error').style.display = 'block';
		});
	}
	const ar = document.getElementById('ajax-request-url');
	if (ar) {
		const btn = document.getElementsByName('submit-ajax')[0];
		btn.addEventListener('click', () => {
			btn.classList.add('disabled');
			RETROP.ajaxSendItems(ar.value, '#retrop-item', (msg) => {
				document.getElementById('response-msgs').innerHTML += msg.msg;
			}, (success) => {
				if (success) {
					document.getElementById('success').style.display = 'block';
				} else {
					document.getElementById('error').style.display = 'block';
				}
			});
		});
	}
});


// -----------------------------------------------------------------------------


var RETROP = RETROP ? RETROP : {};
RETROP['loadFiles'] = (function () {

	function loadFiles(jsonStructs, urls, resSelector, onFinished) {
		var structs = JSON.parse(jsonStructs);
		var recCount = 0;
		var successCount = 0;
		var items = [];

		if (urls.length === 0) {
			var res = document.querySelector(resSelector);
			if (res) res.value = '';
			console.log('Complete filtering (No data)');
			onFinished();
			return;
		}

		urls.forEach(function (url) {
			console.log('Requesting file...');
			var req = new XMLHttpRequest();
			req.open('GET', url, true);
			req.responseType = 'arraybuffer';
			req.onload = makeListener(url, req);
			req.send();
		});

		function makeListener(url, req) {
			return function (e) {
				if (!req.response) {
					console.log('Did not receive file (' + url + ')');
					return;
				}
				console.log('Received file: ' + req.response.byteLength + ' bytes (' + url + ')');
				if (process(req.response)) successCount += 1;
				if (++recCount === urls.length) finished(recCount === successCount);
			};
		}

		function process(response) {
			var data = new Uint8Array(response);
			var arr = new Array();
			for (var i = 0, I = data.length; i < I; i += 1) arr[i] = String.fromCharCode(data[i]);
			var bstr = arr.join('');

			try {
				var book = XLSX.read(bstr, {type:'binary'});
				var sheetName = book.SheetNames[0];
				var sheet = book.Sheets[sheetName];
				if (sheet) processSheet(sheet, items, structs);
				console.log('Finish filtering file');
				return true;
			} catch (e) {
				console.log('Error while filtering file');
				return false;
			}
		}

		function finished(successAll) {
			var res = document.querySelector(resSelector);
			if (res) res.value = JSON.stringify(items);
			console.log('Complete filtering (' + items.length + ' items)');
			onFinished(successAll);
		}
	}


	// -------------------------------------------------------------------------


	function processSheet(sheet, retItems, structs) {
		var range = XLSX.utils.decode_range(sheet['!ref']);
		var x0 = range.s.c, x1 = Math.min(range.e.c, 40) + 1;
		var y0 = range.s.r, y1 = range.e.r + 1;

		var colCount = 0, colToKey = {};
		for (var x = x0; x < x1; x += 1) {
			var cell = sheet[XLSX.utils.encode_cell({c: x, r: y0})];
			if (!cell || cell.w === '') {
				colToKey[x] = false;
			} else {
				colToKey[x] = normalizeKey(cell.w + '', true);
			}
			colCount += 1;
		}
		x1 = x0 + colCount;

		for (var y = y0 + 1; y < y1; y += 1) {  // skip header
			var item = {};
			var count = 0;
			for (var x = x0; x < x1; x += 1) {
				var cell = sheet[XLSX.utils.encode_cell({c: x, r: y})];
				var key = colToKey[x];
				if (key === false) continue;
				if (!structs[key]) continue;
				var type = structs[key].type;

				if (type === 'post_content' || (type === 'post_meta' && structs[key].filter === 'post_content')) {
					if (cell && cell.w && cell.w.length > 0) {
						var text = cell.w.replace(/<\/?span("[^"]*"|'[^']*'|[^'">])*>/g, '');  // remove automatically inserted 'span' tag.
						text = text.replace(/<br\/>/g, '<br />');
						text = text.replace(/&#x000d;&#x000a;/g, '<br />');
						item[key] = text;
						count += 1;
					}
				} else if (type === 'post_title' || type === 'post_meta' || type === 'post_date' || type === 'post_date_gmt' || type === 'thumbnail_url') {
					if (cell && cell.w && cell.w.length > 0) {
						item[key] = cell.w;
						count += 1;
					}
				} else if (type === 'term') {
					if (cell && cell.w && cell.w.length > 0) {
						var vals = cell.w.split(/\s*,\s*/);
						item[key] = vals.map((x) => { return normalizeKey(x, false); });
						count += 1;
					}
				}
			}
			if (0 < count) retItems.push(item);
		}
	}

	function normalizeKey(str, isKey) {
		str = str.replace(/[Ａ-Ｚａ-ｚ０-９]/g, (s) => { return String.fromCharCode(s.charCodeAt(0) - 0xFEE0); });
		str = str.replace(/[_＿]/g, '_');
		str = str.replace(/[\-‐―ー]/g, '-');
		str = str.replace(/[^A-Za-z0-9\-\_]/g, '');
		str = str.toLowerCase();
		str = str.trim();
		if (0 < str.length && !isKey) {
			if (str[0] === '_' || str[0] === '-') str = str.replace(/^[_\-]+/, '');
			str = str.replace('_', '-');
		}
		return str;
	}

	return loadFiles;
})();


// -----------------------------------------------------------------------------


RETROP['ajaxSendItems'] = (function () {

	let _resSelector;
	let _onReceiveOne;
	let _onFinished;
	let _ajaxUrl;

	function ajaxSendItems(url, resSelector, onReceiveOne, onFinished) {
		_ajaxUrl      = url;
		_resSelector  = resSelector;
		_onReceiveOne = onReceiveOne;
		_onFinished   = onFinished;
		console.log('ajaxSendItems: ' + _ajaxUrl);
		request(0);
	}

	function request(idx) {
		const di = document.querySelector(_resSelector + '-' + idx);
		if (!di) {
			notifyFinished();
			_onFinished(true);
			return;
		}
		const data = di.value;
		const req = new XMLHttpRequest();

		req.onreadystatechange = () => {
			if (req.readyState === 4) {
				if ((200 <= req.status && req.status < 300)) {
					onReceived(idx, JSON.parse(req.response));
				} else {
					console.log('Ajax Error!');
					_onFinished(false);
				}
			}
		};
		req.open('POST', _ajaxUrl);
		req.setRequestHeader('content-type', 'application/json');
		req.send(data);
		console.log('sendRequest: ' + idx);
	}

	function onReceived(idx, msg) {
		console.log('ajaxReceived: ' + idx);
		_onReceiveOne(msg);
		request(idx + 1);
	}

	function notifyFinished() {
		const req = new XMLHttpRequest();
		req.open('POST', _ajaxUrl);
		req.setRequestHeader('content-type', 'application/json');
		req.send('finished');
	}

	return ajaxSendItems;
})();
