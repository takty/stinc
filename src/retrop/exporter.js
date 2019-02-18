/**
 *
 * Retrop: XLSX Saver (js)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-02-18
 *
 */


document.addEventListener('DOMContentLoaded', () => {
	const btn = document.getElementById('download');
	if (!btn) return;

	const st = document.getElementById('retrop-structs').value;
	const fn = document.getElementById('retrop-filename').value;

	btn.addEventListener('click', () => {
		btn.classList.add('disabled');
		RETROP.saveFile(st, fn, '#retrop-chunk-', (success) => {
			document.getElementById('retrop-' + (success ? 'success' : 'failure')).style.display = 'block';
		});
	});
});

const RETROP = RETROP ? RETROP : {};
RETROP['saveFile'] = (function () {

	function saveFile(jsonStructs, fileName, chunkSelector, onFinished) {
		const structs = JSON.parse(jsonStructs);

		const data = [];
		let i = 0;
		while (true) {
			const c = document.querySelector(chunkSelector + i);
			if (!c) break;
			const chunk = JSON.parse(c.value);
			if (data.length === 0) data.push(structs);
			for (let rec of chunk) data.push(Object.values(rec));
			i += 1;
		}

		const wb = XLSX.utils.book_new();
		const ws = XLSX.utils.aoa_to_sheet(data);

		XLSX.utils.book_append_sheet(wb, ws);
		XLSX.writeFile(wb, fileName);

		onFinished(true);
	}

	return saveFile;
})();
