/**
 *
 * Retrop: XLSX Saver (js)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-01-24
 *
 */


var RETROP = RETROP ? RETROP : {};
RETROP['saveFile'] = (function () {

	function saveFile(jsonStructs, fileName, chunkSelector, onFinished) {
		var structs = JSON.parse(jsonStructs);

		var data = [];
		let i = 0;
		while (true) {
			const c = document.querySelector(chunkSelector + i);
			if (!c) break;
			const chunk = JSON.parse(c.value);
			if (data.length === 0) data.push(structs);
			for (let rec of chunk) data.push(Object.values(rec));
			i += 1;
		}

		var wb = XLSX.utils.book_new();
		var ws = XLSX.utils.aoa_to_sheet(data);

		XLSX.utils.book_append_sheet(wb, ws);
		XLSX.writeFile(wb, fileName);

		onFinished(true);
	}

	return saveFile;
})();
