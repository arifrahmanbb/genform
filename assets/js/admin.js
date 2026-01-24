/**
 * GenForm Admin JavaScript
 *
 * Finalized admin-side functionality with clean UX logic.
 */

(function ($) {
	'use strict';

	$(document).ready(function () {

		// Entries: View Details (Final Minimalist UX).
		$(document).on('click', '.gfm-view-entry', function (e) {
			e.preventDefault();
			const $btn = $(this);
			const data = $btn.data('payload');
			const metadata = $btn.data('metadata') || {};
			const $row = $btn.closest('tr');
			const entryId = $row.find('input[name="entry[]"]').val();

			// 1. Update Modal Header.
			$('.gfm-modal-header h3').text(`${genform.i18n.entry_details || 'Entry Details'} - #${entryId}`);

			// 2. Build Submission Data using Human-readable labels.
			let html = '<div class="gfm-modal-section-title">' + (genform.i18n.submission_data || 'Submission Data') + '</div>';
			html += '<table class="gfm-details-table">';
			for (const [label, value] of Object.entries(data)) {
				const displayValue = Array.isArray(value) ? value.join(', ') : value;
				html += `<tr><th class="gfm-label-col">${label}</th><td class="gfm-value-col">${displayValue}</td></tr>`;
			}
			html += '</table>';

			// 3. System Information Section (Moved data from table here).
			html += '<div class="gfm-system-info-box">';
			html += '<div class="gfm-modal-section-title">' + (genform.i18n.system_info || 'System Information') + '</div>';
			html += '<div class="gfm-system-grid">';

			// Device & IP.
			html += `<div class="gfm-system-row"><span class="dashicons dashicons-desktop"></span> <span>Device: <strong>${metadata.browser || 'Unknown'}</strong> on <strong>${metadata.os || 'Unknown'}</strong></span></div>`;
			html += `<div class="gfm-system-row"><span class="dashicons dashicons-networking"></span> <span>IP Address: <code>${metadata.ip || 'Unknown'}</code></span></div>`;

			// Source.
			if (metadata.url) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-admin-links"></span> <span>Source: <a href="${metadata.url}" target="_blank" class="gfm-source-link">${metadata.url}</a></span></div>`;
			}

			// PRO Upgrade Loop (Marketing).
			html += '<div class="gfm-pro-marketing-row"><span class="dashicons dashicons-location"></span> <span class="gfm-pro-label">Location: </span><span class="gfm-blurred-text">London, United Kingdom</span> <span class="gfm-pro-lock-badge">PRO ONLY</span></div>';

			html += '</div></div>';

			$('#gfm-modal-body').html(html);
			$('#gfm-entry-modal').css('display', 'flex').hide().fadeIn(200);

			// 4. Mark as Read (AJAX) - Corrected status dot selector.
			const $dot = $row.find('.gfm-unread-dot-badge');
			if ($dot.length > 0) {
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

		// Deletion confirmation logic for hover actions.
		$(document).on('click', '.gfm-action-trash-simple', function (e) {
			if (!confirm(genform.i18n.confirm_delete || 'Move this entry to trash?')) {
				e.preventDefault();
			}
		});

		// Permanent Deletion logic.
		$(document).on('click', '.gfm-delete-entry-permanent', function (e) {
			e.preventDefault();
			if (!confirm(genform.i18n.confirm_delete || 'Permanently delete this entry?')) return;

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

		// Modal Interactions.
		$('.gfm-close-modal').on('click', () => $('#gfm-entry-modal').fadeOut(200));
		$(window).on('click', (e) => { if ($(e.target).is('#gfm-entry-modal')) $('#gfm-entry-modal').fadeOut(200); });

	});

})(jQuery);
