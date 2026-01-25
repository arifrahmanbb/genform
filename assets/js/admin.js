/**
 * GenForm Admin JavaScript
 *
 * Handles interactive elements in the WordPress administration area,
 * such as the submission preview modal and the copy shortcode feature.
 */

(function ($) {
	'use strict';

	$(document).ready(function () {

		/**
		 * Entries: View Details Modal logic.
		 */
		$(document).on('click', '.gfm-view-entry', function (e) {
			e.preventDefault();
			const $btn = $(this);
			const data = $btn.data('payload');
			const metadataRaw = $btn.data('metadata') || '{}';
			const $row = $btn.closest('tr');
			const entryId = $row.find('input[name="entry[]"]').val();

			let metadata = {};
			try {
				metadata = typeof metadataRaw === 'string' ? JSON.parse(metadataRaw) : metadataRaw;
			} catch (ex) {
				console.error('Metadata parsing failed:', ex);
			}

			// Update the modal header with the entry ID.
			$('.gfm-modal-header h3').text(`${genform.i18n.entry_details || 'Entry'} #${entryId}`);

			// Build the submission data table rows.
			let html = `<div class="gfm-modal-section-title">Submission Data</div>`;
			html += `<table class="gfm-details-table">`;
			for (const [label, value] of Object.entries(data)) {
				const displayValue = Array.isArray(value) ? value.join(', ') : value;
				html += `<tr><th class="gfm-label-col">${label}</th><td>${displayValue}</td></tr>`;
			}
			html += `</table>`;

			// Build the system info section.
			html += `<div class="gfm-system-info-box">
				<div class="gfm-modal-section-title">System Info</div>
				<div class="gfm-system-grid">`;

			if (metadata.ip) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-networking"></span><span>IP: <code>${metadata.ip}</code></span></div>`;
			}
			if (metadata.browser || metadata.os) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-desktop"></span><span>Device: <strong>${metadata.browser || 'Unknown'}</strong> on <strong>${metadata.os || 'Unknown'}</strong></span></div>`;
			}
			if (metadata.url) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-admin-links"></span><span>Source: <a href="${metadata.url}" target="_blank">${metadata.url}</a></span></div>`;
			}

			html += `</div></div>`;

			// Update content and show the modal.
			$('#gfm-modal-body').html(html);
			$('#gfm-entry-modal').removeClass('gfm-hidden').hide().fadeIn(200);

			// Mark as read indicator (AJAX update).
			const $dot = $row.find('.gfm-unread-dot-badge');
			if ($dot.length) {
				$.post(genform.ajax_url, {
					action: 'genform_mark_as_read',
					entry_id: entryId,
					nonce: genform.nonce
				}, (res) => {
					if (res.success) {
						$dot.fadeOut(300, function () { $(this).remove(); });
					}
				});
			}
		});

		/**
		 * Copy Shortcode to Clipboard logic.
		 */
		$(document).on('click', '.gfm-copy-btn', function () {
			const code = $(this).data('code');
			const $temp = $("<input>").val(code);

			$("body").append($temp);
			$temp.select();
			document.execCommand("copy");
			$temp.remove();

			// provide visual feedback.
			$(this).removeClass('dashicons-admin-page').addClass('dashicons-yes');
			setTimeout(() => $(this).removeClass('dashicons-yes').addClass('dashicons-admin-page'), 2000);
		});

		/**
		 * Deletion confirmation triggers.
		 */
		$(document).on('click', '.gfm-action-trash-simple', (e) => {
			if (!confirm('Move to trash?')) e.preventDefault();
		});

		$(document).on('click', '.gfm-delete-entry-permanent', function (e) {
			e.preventDefault();
			if (!confirm('Delete permanently?')) return;

			const $btn = $(this);
			$.post(genform.ajax_url, {
				action: 'genform_delete_entry',
				entry_id: $btn.data('id'),
				nonce: genform.nonce
			}, (res) => {
				if (res.success) {
					$btn.closest('tr').fadeOut(() => window.location.reload());
				}
			});
		});

		/**
		 * Modal closing events.
		 */
		$('.gfm-close-modal').on('click', () => $('#gfm-entry-modal').fadeOut(200));

		$(window).on('click', (e) => {
			if ($(e.target).is('#gfm-entry-modal')) {
				$('#gfm-entry-modal').fadeOut(200);
			}
		});
	});

})(jQuery);
