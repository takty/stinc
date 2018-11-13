/**
 *
 * Media Picker (JS)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-14
 *
 */


document.addEventListener('DOMContentLoaded', function () {
	const elms = document.querySelectorAll('*[data-picker="media"]');
	for (let i = 0; i < elms.length; i += 1) {
		setMediaPicker(elms[i]);
	}
});

function setMediaPicker(elm, cls, fn, opts = {parentGen: 1}) {
	if (cls === undefined || cls === false) cls = 'media';

	const postId = $('#post_ID').val();
	let cm = null;
	elm.addEventListener('click', function (e) {
		e.preventDefault();
		if (!cm) {
			wp.media.view.AttachmentsBrowser = AttachmentsBrowserCustom;
			cm = createMedia(postId, e.target.innerText, false);
			cm.on('select', function () {
				const f = cm.state().get('selection').first();
				const fileJson = f.toJSON();
				const parent = getParent(e.target, opts.parentGen);
				setItem(parent, cls, fileJson);
				if (fn) fn(e.target, fileJson);
			});
			cm.on('close', function () {
				wp.media.view.AttachmentsBrowser = AttachmentsBrowserOrig;
			});
		}
		cm.open();
	});

	function getParent(elm, gen) {
		while (0 < gen-- && elm.parentNode) elm = elm.parentNode;
		return elm;
	}

	function setItem(parent, cls, f) {
		setValueToCls(parent, cls + '-id',       f.id);
		setValueToCls(parent, cls + '-url',      f.url);
		setValueToCls(parent, cls + '-title',    f.title);
		setValueToCls(parent, cls + '-filename', f.filename);
	}

	function setValueToCls(parent, cls, value) {
		const elms = parent.getElementsByClassName(cls);
		for (let i = 0; i < elms.length; i += 1) {
			if (elms[i] instanceof HTMLInputElement) {
				elms[i].value = value;
			} else {
				elms[i].innerText = value;
			}
		}
	}

	function createMedia(postId, title, multiple) {
		wp.media.model.settings.post.id = postId;
		wp.media.view.settings.post.id  = postId;

 		const media = wp.media({
			title   : title,
			library : {type: ''},
			frame   : 'select',
			multiple: multiple,
		});
		// For attatching uploaded file to post
		media.uploader.options.uploader.params.post_id = postId;
		return media;
	}

	/*
	 * Tha following enables our media picker selectable 'Uploaded to this post'.
	 * https://cobbledco.de/adding-your-own-filter-to-the-media-uploader/
	 */
	const MediaLibraryUploadedFilter = wp.media.view.AttachmentFilters.extend({
		createFilters: function() {
			const filters = {};
			filters.all = {
				text:  wp.media.view.l10n.allMediaItems,
				props: {
					status    : null,
					type      : null,
					uploadedTo: null,
					orderby   : 'date',
					order     : 'DESC'
				},
				priority: 10
			};
			filters.uploaded = {
				text:  wp.media.view.l10n.uploadedToThisPost,
				props: {
					status    : null,
					type      : null,
					uploadedTo: wp.media.view.settings.post.id,
					orderby   : 'menuOrder',
					order     : 'ASC'
				},
				priority: 20
			};
			filters.unattached = {
				text:  wp.media.view.l10n.unattached,
				props: {
					status    : null,
					type      : null,
					uploadedTo: 0,
					orderby   : 'menuOrder',
					order     : 'ASC'
				},
				priority: 50
			};
			this.filters = filters;
		}
	});

	const AttachmentsBrowserOrig = wp.media.view.AttachmentsBrowser;
	const AttachmentsBrowserCustom = AttachmentsBrowserOrig.extend({
		createToolbar: function() {
			AttachmentsBrowserOrig.prototype.createToolbar.call( this );
			this.toolbar.set(
				'mediaLibraryUploadedFilter',
				new MediaLibraryUploadedFilter({
					controller: this.controller,
					model:      this.collection.props,
					priority:   -100
				}).render()
			);
		}
	});
}
