/**
 * GenForm Admin JavaScript (Vanilla JS)
 *
 * Handles interactive elements in the WordPress administration area,
 * such as the submission preview modal, custom confirmations, and AJAX actions.
 */

(function () {
	'use strict';

	let confirmPromise = null;

	/**
	 * Escape HTML entities to prevent XSS in innerHTML.
	 */
	const escapeHtml = (str) => {
		const div = document.createElement('div');
		div.textContent = String(str);
		return div.innerHTML;
	};

	/**
	 * Modal Helpers
	 */
	const openModal = (selector) => {
		const modal = document.querySelector(selector);
		if (!modal) return;
		modal.classList.remove('gfm-hidden');
		modal.offsetHeight; // Force reflow
		modal.classList.add('show');
	};

	const closeModal = (selector) => {
		const modal = document.querySelector(selector);
		if (!modal) return;
		modal.classList.remove('show');
		setTimeout(() => {
			if (!modal.classList.contains('show')) {
				modal.classList.add('gfm-hidden');
			}
		}, 300);
	};

	/**
	 * Custom Confirmation Modal logic
	 */
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

	/**
	 * Global Spinner & HUD Notice
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
		spinner.classList.add('show');
	};

	const hideSpinner = () => {
		const spinner = document.getElementById('gfm-global-spinner');
		if (spinner) spinner.classList.remove('show');
	};

	const showNotice = (msg) => {
		const notice = document.createElement('div');
		notice.className = 'gfm-builder-notice';
		notice.textContent = msg;
		document.body.appendChild(notice);
		setTimeout(() => notice.classList.add('show'), 10);
		setTimeout(() => {
			notice.classList.remove('show');
			setTimeout(() => notice.remove(), 400);
		}, 3000);
	};

	/**
	 * AJAX Fetch Wrapper
	 */
	const gfmFetch = async (action, data = {}) => {
		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', genform.nonce);
		for (const key in data) formData.append(key, data[key]);

		try {
			const response = await fetch(genform.ajax_url, { method: 'POST', body: formData });
			return await response.json();
		} catch (error) {
			console.error('GenForm AJAX Error:', error);
			return { success: false, data: { message: 'Network error occurred.' } };
		}
	};

	// Export helpers to window for other scripts (like form-builder.js)
	window.gfmAdmin = { openModal, closeModal, showSpinner, hideSpinner, showNotice, gfmConfirm };

	/**
	 * Main Administration Event Controller
	 */
	const init = () => {
		document.addEventListener('click', async function (e) {
			const target = e.target;

			// 1. Eye Icon: View Details Modal
			const viewBtn = target.closest('.gfm-view-entry');
			if (viewBtn) {
				e.preventDefault();
				let data, metadata;
				try {
					data = JSON.parse(viewBtn.dataset.payload || '{}');
					metadata = JSON.parse(viewBtn.dataset.metadata || '{}');
				} catch (err) {
					console.error('Payload parse error', err);
					return;
				}

				const entryId = viewBtn.closest('tr')?.querySelector('input[name="entry[]"]')?.value || '';
				const formName = viewBtn.dataset.form || 'Form';

				const headerTitle = document.querySelector('.gfm-modal-header h3');
				if (headerTitle) {
					headerTitle.innerHTML = `<span>${genform.i18n.entry_details || 'Entry Detail'} <span class="gfm-badge-id">#${entryId}</span></span><span class="gfm-modal-subtitle">${formName}</span>`;
				}

				let html = `<div class="gfm-modal-data-wrapper"><div class="gfm-modal-section-title"><span class="dashicons dashicons-database"></span> Submission Data</div><div class="gfm-details-modern-list">`;
				for (const [label, value] of Object.entries(data)) {
					const val = Array.isArray(value) ? value.map(escapeHtml).join(', ') : escapeHtml(value);
					html += `<div class="gfm-detail-item"><div class="gfm-detail-label">${escapeHtml(label)}</div><div class="gfm-detail-value">${val || '—'}</div></div>`;
				}
				html += `</div>`;

				// Meta info
				html += `<div class="gfm-system-info-box"><div class="gfm-modal-section-title"><span class="dashicons dashicons-admin-generic"></span> Meta Information</div><div class="gfm-system-grid">`;
				if (metadata.ip) html += `<div class="gfm-system-row"><span class="dashicons dashicons-networking"></span> <span>IP: <code>${escapeHtml(metadata.ip)}</code></span></div>`;
				if (metadata.browser) html += `<div class="gfm-system-row"><span class="dashicons dashicons-desktop"></span> <span>Device: <strong>${escapeHtml(metadata.browser)}</strong> on <strong>${escapeHtml(metadata.os || 'Unknown')}</strong></span></div>`;
				if (metadata.url) html += `<div class="gfm-system-row"><span class="dashicons dashicons-admin-links"></span> <span>Source: <a href="${escapeHtml(metadata.url)}" target="_blank" rel="noopener noreferrer" class="gfm-source-link">${escapeHtml(metadata.url)}</a></span></div>`;
				html += `</div></div></div>`;

				const body = document.getElementById('gfm-modal-body');
				if (body) body.innerHTML = html;
				openModal('#gfm-entry-modal');

				// Mark as Read
				const dot = viewBtn.closest('tr')?.querySelector('.gfm-unread-dot-badge');
				if (dot) {
					const res = await gfmFetch('genform_mark_as_read', { entry_id: entryId });
					if (res.success) {
						dot.classList.add('gfm-removing');
						setTimeout(() => dot.remove(), 300);
					}
				}
				return;
			}

			// 2. Trash Simple (Entries List)
			const trashBtn = target.closest('.gfm-action-trash-simple');
			if (trashBtn) {
				e.preventDefault();
				if (await gfmConfirm('Move to Trash?', 'The record will be moved to the trash list.')) {
					const url = new URL(trashBtn.href);
					const entryId = url.searchParams.get('entry');
					showSpinner();
					const res = await gfmFetch('genform_trash_entry', { entry_id: entryId });
					hideSpinner();
					if (res.success) {
						trashBtn.closest('tr').classList.add('gfm-removing');
						setTimeout(() => { showNotice('Entry moved to trash.'); setTimeout(() => window.location.reload(), 1000); }, 500);
					}
				}
				return;
			}

			// 3. Delete Form (Forms List)
			const deleteFormBtn = target.closest('.button-link-delete');
			if (deleteFormBtn) {
				e.preventDefault();
				if (await gfmConfirm('Delete Form?', 'Warning: This will permanently remove the form and ALL its entries.')) {
					const url = new URL(deleteFormBtn.href);
					const formId = url.searchParams.get('form_id');
					showSpinner();
					const res = await gfmFetch('genform_delete_form', { form_id: formId });
					hideSpinner();
					if (res.success) {
						deleteFormBtn.closest('tr').classList.add('gfm-removing');
						setTimeout(() => { showNotice('Form deleted.'); setTimeout(() => window.location.reload(), 1000); }, 500);
					}
				}
				return;
			}

			// 4. Delete Entry Permanent (Trash View)
			const delPermBtn = target.closest('.gfm-delete-entry-permanent');
			if (delPermBtn) {
				e.preventDefault();
				if (await gfmConfirm('Delete Permanently?', 'This entry will be wiped from the database forever.')) {
					showSpinner();
					const res = await gfmFetch('genform_delete_entry', { entry_id: delPermBtn.dataset.id });
					hideSpinner();
					if (res.success) {
						delPermBtn.closest('tr').classList.add('gfm-removing');
						setTimeout(() => { showNotice('Entry deleted.'); setTimeout(() => window.location.reload(), 1000); }, 500);
					}
				}
				return;
			}

			// 5. Copy Shortcode
			const copyBtn = target.closest('.gfm-copy-btn');
			if (copyBtn) {
				const text = copyBtn.dataset.code;
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(() => {
						copyBtn.classList.replace('dashicons-admin-page', 'dashicons-yes');
						setTimeout(() => copyBtn.classList.replace('dashicons-yes', 'dashicons-admin-page'), 2000);
					});
				} else {
					// Fallback for older browsers
					const input = document.createElement('textarea');
					input.value = text;
					input.style.position = 'fixed';
					input.style.opacity = '0';
					document.body.appendChild(input);
					input.select();
					document.execCommand('copy');
					document.body.removeChild(input);
					copyBtn.classList.replace('dashicons-admin-page', 'dashicons-yes');
					setTimeout(() => copyBtn.classList.replace('dashicons-yes', 'dashicons-admin-page'), 2000);
				}
				return;
			}

			// 6. Confirmation Modal Actions
			if (target.id === 'gfm-confirm-ok') {
				closeModal('#gfm-confirm-modal');
				if (confirmPromise) { confirmPromise(true); confirmPromise = null; }
				return;
			}
			if (target.id === 'gfm-confirm-cancel') {
				closeModal('#gfm-confirm-modal');
				if (confirmPromise) { confirmPromise(false); confirmPromise = null; }
				return;
			}

			// 7. Modal General Close
			if (target.closest('.gfm-close-modal') || target.classList.contains('gfm-modal')) {
				const modal = target.closest('.gfm-modal');
				if (modal) closeModal('#' + modal.id);
			}
		});
	};

	// Robust initialization
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

