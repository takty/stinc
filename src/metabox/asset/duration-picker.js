/**
 *
 * Duration Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-22
 *
 */


function st_duration_picker_initialize_admin(label_year) {
	document.addEventListener('DOMContentLoaded', () => {
		var cms = document.querySelectorAll('.flatpickr-current-month > .cur-month');
		for (var i = 0; i < cms.length; i += 1) {
			var span = document.createElement('span');
			span.innerText = label_year;
			cms[i].parentElement.insertBefore(span, cms[i].nextSibling);
		}
	});
}
