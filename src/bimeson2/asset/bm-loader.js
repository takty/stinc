/**
 *
 * Bimeson File Loader
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-01
 *
 */


var BIMESON = {};
BIMESON['loadFiles'] = (function () {

	var KEY_BODY = '_body';

	function loadFiles(urls, resSelector, onFinished) {
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
				if (sheet) processSheet(sheet, items);
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

	function processSheet(sheet, retItems) {
		var range = XLSX.utils.decode_range(sheet['!ref']);
		var x0 = range.s.c, x1 = Math.min(range.e.c, 40) + 1;
		var y0 = range.s.r, y1 = range.e.r + 1;

		var colCount = 0, colToKey = {};
		for (var x = x0; x < x1; x += 1) {
			var cell = sheet[XLSX.utils.encode_cell({c: x, r: y0})];
			if (!cell || cell.w === '') break;
			var key = (cell.w + '').toLowerCase();
			colCount += 1;
			colToKey[x] = key;
		}
		x1 = x0 + colCount;

		for (var y = y0 + 1; y < y1; y += 1) {  // skip header
			var item = {};
			for (var x = x0; x < x1; x += 1) {
				var cell = sheet[XLSX.utils.encode_cell({c: x, r: y})];
				var key = colToKey[x];
				if (key === KEY_BODY || key.indexOf(KEY_BODY + '_') === 0) {
					if (cell && cell.h && cell.h.length > 0) {
						var text = cell.h.replace(/<\/?span("[^"]*"|'[^']*'|[^'">])*>/g, '');  // remove automatically inserted 'span' tag.
						text = text.replace(/<br\/>/g, '<br />');
						text = text.replace(/&#x000d;&#x000a;/g, '<br />');
						item[key] = text;
					}
				} else if (key[0] === '_') {
					if (cell && cell.w && cell.w.length > 0) {
						item[key] = cell.w;
					}
				} else {
					if (cell && cell.w && cell.w.length > 0) {
						var vals = cell.w.split(/\s*,\s*/);
						item[key] = vals.slice(0);  // make a clone
						item[key] = item[key].map(function (x) {return x.trim();});
					}
				}
			}
			retItems.push(item);
		}
	}

	return loadFiles;
})();
