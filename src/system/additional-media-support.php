<?php
/**
 * Custom PDF Thumbnail
 *
 * @package Stinc
 * @author Takuto Yanagida
 * @version 2022-01-10
 */

namespace st;

function enable_to_upload_svg() {
	add_filter(
		'ext2type',
		function ( $ext2types ) {
			array_push( $ext2types, array( 'image' => array( 'svg', 'svgz' ) ) );
			return $ext2types;
		}
	);
	add_filter(
		'upload_mimes',
		function ( $mimes ) {
			$mimes['svg']  = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
			return $mimes;
		}
	);
	add_filter(
		'getimagesize_mimes_to_exts',
		function ( $mime_to_ext ) {
			$mime_to_ext['image/svg+xml'] = 'svg';
			return $mime_to_ext;
		}
	);
}

function enable_pdf_thumbnail() {
	add_filter( 'ajax_query_attachments_args', '\st\cb_ajax_query_attachments_args', 11 );
	add_action( 'admin_footer-post-new.php', '\st\cb_override_attachment_filter' );
	add_action( 'admin_footer-post.php', '\st\cb_override_attachment_filter' );
}

function cb_ajax_query_attachments_args( $query ) {
	if ( isset( $query['post_mime_type'] ) && 'image_and_pdf' === $query['post_mime_type'] ) {
		$query['post_mime_type'] = array( 'image', 'application/pdf' );
	}
	return $query;
}

function cb_override_attachment_filter() {
	?>
	<script type="text/javascript">
		wp.media.view.AttachmentFilters.Uploaded.prototype.createFilters = function () {
			var type = this.model.get('type'),
				types = wp.media.view.settings.mimeTypes,
				uid = window.userSettings ? parseInt( window.userSettings.uid, 10 ) : 0,
				text;

			if (types && type) text = types[type];
			var l10n = wp.media.view.l10n;

			if (this.options.controller._state === 'featured-image') {
				this.filters = {
					all: {
						text: <?php echo "'" . __( 'Image' ) . ' & ' . __( 'PDF' ) . "'"; ?>,
						props: { type: 'image_and_pdf', uploadedTo: null, orderby: 'date', order: 'DESC' },
						priority: 10
					},
					image: {
						text: <?php echo "'" . __( 'Image' ) . "'"; ?>,
						props: { type: 'image', uploadedTo: null, orderby: 'date', order: 'DESC' },
						priority: 20
					},
					uploaded: {
						text:  l10n.uploadedToThisPost,
						props: { type: 'image_and_pdf', uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC' },
						priority: 30
					},
					unattached: {
						text:  l10n.unattached,
						props: { status: null, uploadedTo: 0, type: null, orderby: 'menuOrder', order: 'ASC' },
						priority: 50
					},
				};
			} else {
				this.filters = {
					all: {
						text:  text || l10n.allMediaItems,
						props: { uploadedTo: null, orderby: 'date', order: 'DESC', author: null },
						priority: 10
					},
					uploaded: {
						text:  l10n.uploadedToThisPost,
						props: { uploadedTo: wp.media.view.settings.post.id, orderby: 'menuOrder', order: 'ASC', author: null },
						priority: 20
					},
					unattached: {
						text:  l10n.unattached,
						props: { uploadedTo: 0, orderby: 'menuOrder', order: 'ASC', author: null },
						priority: 50
					}
				};
			}
			if ( uid ) {
				this.filters.mine = {
					text:  l10n.mine,
					props: { orderby: 'date', order: 'DESC', author: uid },
					priority: 50
				};
			}
		};
		wp.media.view.Modal.prototype.on('open', function () {
			jQuery('.media-modal').find('a.media-menu-item').click(function () {
				if (jQuery(this).html() === "<?php esc_html_e( 'Featured Image' ); ?>") {
					jQuery('select.attachment-filters option[value="all"]').attr('selected', true).parent().trigger('change');
				}
			} );
		});
		wp.media.featuredImage.frame().on('open', function () {
			jQuery('select.attachment-filters option[value="all"]').attr('selected', true).parent().trigger( 'change' ); // Change the default view to "Uploaded to this post".
		});
	</script>
	<?php
}
