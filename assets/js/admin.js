/**
 * GenForm Admin JavaScript (Vanilla JS)
 *
 * Handles interactive elements in the WordPress administration area,
 * such as the submission preview modal, custom confirmations, and AJAX actions.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/**
		 * Modal Helpers
		 */
		const openModal = (selector) => {
			const modal = document.querySelector(selector);
			if (!modal) return;

			modal.classList.remove('gfm-hidden');
			modal.style.display = 'flex';
			modal.style.opacity = '0';

			// Trigger fade in
			setTimeout(() => {
				modal.style.transition = 'opacity 0.2s';
				modal.style.opacity = '1';
				modal.classList.add('show');
			}, 10);
		};

		const closeModal = (selector) => {
			const modal = document.querySelector(selector);
			if (!modal) return;

			modal.classList.remove('show');
			modal.style.opacity = '0';

			setTimeout(() => {
				modal.style.display = 'none';
				modal.classList.add('gfm-hidden');
			}, 200);
		};

		/**
		 * Custom Confirmation Modal
		 */
		let confirmPromise = null;
		const gfmConfirm = (title, desc = 'This action cannot be undone.') => {
			const titleEl = document.getElementById('gfm-confirm-title');
			const descEl = document.getElementById('gfm-confirm-desc');

			if (titleEl) titleEl.textContent = title;
			if (descEl) descEl.textContent = desc;

			openModal('#gfm-confirm-modal');

			return new Promise((resolve) => {
				confirmPromise = resolve;
			});
		};

		const confirmOk = document.getElementById('gfm-confirm-ok');
		if (confirmOk) {
			confirmOk.addEventListener('click', () => {
				closeModal('#gfm-confirm-modal');
				if (confirmPromise) confirmPromise(true);
			});
		}

		const confirmCancel = document.getElementById('gfm-confirm-cancel');
		if (confirmCancel) {
			confirmCancel.addEventListener('click', () => {
				closeModal('#gfm-confirm-modal');
				if (confirmPromise) confirmPromise(false);
			});
		}

		/**
		 * Helper to show a global spinner loader.
		 */
		const showSpinner = () => {
			let spinner = document.getElementById('gfm-global-spinner');
			if (!spinner) {
				spinner = document.createElement('div');
				spinner.id = 'gfm-global-spinner';
				spinner.className = 'gfm-spinner-overlay';
				spinner.innerHTML = '<div class="gfm-spinner"></div>';
				document.body.appendChild(spinner);
			}
			spinner.style.display = 'flex';
			spinner.style.opacity = '1';
		};

		/**
		 * Helper to hide the shared spinner.
		 */
		const hideSpinner = () => {
			const spinner = document.getElementById('gfm-global-spinner');
			if (spinner) {
				spinner.style.opacity = '0';
				setTimeout(() => { spinner.style.display = 'none'; }, 200);
			}
		};

		/**
		 * Shows a temporary HUD notification.
		 */
		const showNotice = (msg) => {
			const id = 'gfm-notice-' + Date.now();
			const notice = document.createElement('div');
			notice.id = id;
			notice.className = 'gfm-builder-notice';
			notice.textContent = msg;
			document.body.appendChild(notice);

			setTimeout(() => notice.classList.add('show'), 100);
			setTimeout(() => {
				notice.classList.remove('show');
				setTimeout(() => notice.remove(), 300);
			}, 3000);
		};

		// Export helpers to window for builder usage.
		window.gfmAdmin = { openModal, closeModal, showSpinner, hideSpinner, showNotice, gfmConfirm };

		/**
		 * AJAX Fetch Wrapper
		 */
		const gfmFetch = async (action, data = {}) => {
			const formData = new FormData();
			formData.append('action', action);
			formData.append('nonce', genform.nonce);

			for (const key in data) {
				formData.append(key, data[key]);
			}

			try {
				const response = await fetch(genform.ajax_url, {
					method: 'POST',
					body: formData
				});
				return await response.json();
			} catch (error) {
				console.error('GenForm AJAX Error:', error);
				return { success: false, data: { message: 'Network error occurred.' } };
			}
		};

		/**
		 * Entries: View Details Modal logic.
		 */
		document.addEventListener('click', function (e) {
			const btn = e.target.closest('.gfm-view-entry');
			if (!btn) return;

			e.preventDefault();
			const data = JSON.parse(btn.dataset.payload || '{}');
			const metadata = JSON.parse(btn.dataset.metadata || '{}');
			const row = btn.closest('tr');
			const checkbox = row.querySelector('input[name="entry[]"]');
			const entryId = checkbox ? checkbox.value : '';

			const formName = btn.dataset.form || 'Form';

			// Update the modal header
			const headerTitle = document.querySelector('.gfm-modal-header h3');
			if (headerTitle) {
				headerTitle.innerHTML = `
                    <span>${genform.i18n.entry_details || 'Entry Detail'} <span class="gfm-badge-id">#${entryId}</span></span>
                    <span class="gfm-modal-subtitle">${formName}</span>
                `;
			}

			// Build submission data list
			let html = `<div class="gfm-modal-data-wrapper">`;
			html += `<div class="gfm-modal-section-title"><span class="dashicons dashicons-database"></span> Submission Data</div>`;
			html += `<div class="gfm-details-modern-list">`;
			for (const [label, value] of Object.entries(data)) {
				const displayValue = Array.isArray(value) ? value.join(', ') : value;
				html += `<div class="gfm-detail-item">
                    <div class="gfm-detail-label">${label}</div>
                    <div class="gfm-detail-value">${displayValue || '—'}</div>
                </div>`;
			}
			html += `</div>`;

			// Build system info section
			html += `<div class="gfm-system-info-box">
                <div class="gfm-modal-section-title"><span class="dashicons dashicons-admin-generic"></span> Meta Information</div>
                <div class="gfm-system-grid">`;

			if (metadata.ip) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-networking"></span> <span>IP: <code>${metadata.ip}</code></span></div>`;
			}
			if (metadata.browser || metadata.os) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-desktop"></span> <span>Device: <strong>${metadata.browser || 'Unknown'}</strong> on <strong>${metadata.os || 'Unknown'}</strong></span></div>`;
			}
			if (metadata.url) {
				html += `<div class="gfm-system-row"><span class="dashicons dashicons-admin-links"></span> <span>Source: <a href="${metadata.url}" target="_blank" class="gfm-source-link">${metadata.url}</a></span></div>`;
			}
			html += `</div></div></div>`;

			const modalBody = document.getElementById('gfm-modal-body');
			if (modalBody) modalBody.innerHTML = html;

			openModal('#gfm-entry-modal');

			// Mark as read indicator
			const dot = row.querySelector('.gfm-unread-dot-badge');
			if (dot) {
				gfmFetch('genform_mark_as_read', { entry_id: entryId }).then(res => {
					if (res.success) {
						dot.style.transition = 'opacity 0.3s';
						dot.style.opacity = '0';
						setTimeout(() => dot.remove(), 300);
					}
				});
			}
		});

		/**
		 * Copy Shortcode logic.
		 */
		document.addEventListener('click', function (e) {
			const btn = e.target.closest('.gfm-copy-btn');
			if (!btn) return;

			const code = btn.dataset.code;
			const input = document.createElement('input');
			input.value = code;
			document.body.appendChild(input);
			input.select();
			document.execCommand('copy');
			document.body.removeChild(input);

			btn.classList.remove('dashicons-admin-page');
			btn.classList.add('dashicons-yes');
			setTimeout(() => {
				btn.classList.remove('dashicons-yes');
				btn.classList.add('dashicons-admin-page');
			}, 2000);
		});

		/**
		 * Deletion Triggers (Vanilla JS)
		 */
		document.addEventListener('click', async function (e) {

			// 1. Move to Trash
			const trashBtn = e.target.closest('.gfm-action-trash-simple');
			if (trashBtn) {
				e.preventDefault();
				const confirmed = await gfmConfirm('Move to Trash?', 'The record will be moved to the trash list.');
				if (!confirmed) return;

				const url = new URL(trashBtn.getAttribute('href'), window.location.origin);
				const entryId = url.searchParams.get('entry');

				showSpinner();
				const res = await gfmFetch('genform_trash_entry', { entry_id: entryId });
				hideSpinner();

				if (res.success) {
					const row = trashBtn.closest('tr');
					row.style.transition = 'opacity 0.5s';
					row.style.opacity = '0';
					setTimeout(() => {
						showNotice('Entry moved to trash.');
						setTimeout(() => window.location.reload(), 2000);
					}, 500);
				}
			}

			// 2. Delete Form
			const deleteFormBtn = e.target.closest('.button-link-delete');
			if (deleteFormBtn) {
				const href = deleteFormBtn.getAttribute('href');
				if (!href || !href.includes('form_id')) return;

				e.preventDefault();
				const confirmed = await gfmConfirm('Delete Form?', 'Warning: This will permanently remove the form and ALL its entries.');
				if (!confirmed) return;

				const url = new URL(href, window.location.origin);
				const formId = url.searchParams.get('form_id');

				showSpinner();
				const res = await gfmFetch('genform_delete_form', { form_id: formId });
				hideSpinner();

				if (res.success) {
					const row = deleteFormBtn.closest('tr');
					row.style.transition = 'opacity 0.5s';
					row.style.opacity = '0';
					setTimeout(() => {
						showNotice('Form deleted successfully.');
						setTimeout(() => window.location.reload(), 2000);
					}, 500);
				}
			}

			// 3. Delete Entry Permanent
			const deleteEntryBtn = e.target.closest('.gfm-delete-entry-permanent');
			if (deleteEntryBtn) {
				e.preventDefault();
				const confirmed = await gfmConfirm('Delete Permanently?', 'This entry will be wiped from the database forever.');
				if (!confirmed) return;

				const entryId = deleteEntryBtn.dataset.id;
				showSpinner();
				const res = await gfmFetch('genform_delete_entry', { entry_id: entryId });
				hideSpinner();

				if (res.success) {
					const row = deleteEntryBtn.closest('tr');
					row.style.transition = 'opacity 0.5s';
					row.style.opacity = '0';
					setTimeout(() => {
						showNotice('Entry deleted successfully.');
						setTimeout(() => window.location.reload(), 2000);
					}, 500);
				}
			}
		});

		/**
		 * Modal closing events
		 */
		document.addEventListener('click', function (e) {
			if (e.target.closest('.gfm-close-modal')) {
				const modal = e.target.closest('.gfm-modal');
				if (modal) closeModal('#' + modal.id);
			}

			if (e.target.classList.contains('gfm-modal')) {
				closeModal('#' + e.target.id);
			}
		});
	});

})();
