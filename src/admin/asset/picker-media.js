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

	var postId = $('#post_ID').val();
	var cm = null;
	elm.addEventListener('click', function (e) {
		e.preventDefault();
		if (!cm) {
			wp.media.view.AttachmentsBrowser = AttachmentsBrowserCustom;
			cm = create_media(postId, e.target.innerText, false);
			cm.on('select', function () {
				var f = cm.state().get('selection').first();
				var fileJson = f.toJSON();
				var parent = get_parent(e.target, opts.parentGen);
				set_item(parent, cls, fileJson);
				if (fn) fn(e.target, fileJson);
			});
			cm.on('close', function () {
				wp.media.view.AttachmentsBrowser = AttachmentsBrowserOrig;
			});
		}
		cm.open();
	});

	function get_parent(elm, gen) {
		while (0 < gen-- && elm.parentNode) elm = elm.parentNode;
		return elm;
	}

	function set_item(parent, cls, f) {
		set_value_to_cls(parent, cls + '-id',       f.id);
		set_value_to_cls(parent, cls + '-url',      f.url);
		set_value_to_cls(parent, cls + '-title',    f.title);
		set_value_to_cls(parent, cls + '-filename', f.filename);
	}

	function set_value_to_cls(parent, cls, value) {
		var elms = parent.getElementsByClassName(cls);
		for (var i = 0; i < elms.length; i += 1) {
			if (elms[i] instanceof HTMLInputElement) {
				elms[i].value = value;
			} else {
				elms[i].innerText = value;
			}
		}
	}

	function create_media(postId, title, multiple) {
		wp.media.model.settings.post.id = postId;
		wp.media.view.settings.post.id  = postId;

 		var media = wp.media({
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
	var MediaLibraryUploadedFilter = wp.media.view.AttachmentFilters.extend({
		createFilters: function() {
			var filters = {};
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

	var AttachmentsBrowserOrig = wp.media.view.AttachmentsBrowser;
	var AttachmentsBrowserCustom = AttachmentsBrowserOrig.extend({
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
