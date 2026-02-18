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

	const showNotice = (msg, type = 'success') => {
		const notice = document.createElement('div');
		notice.className = `gfm-builder-notice gfm-notice-${type}`;
		const icon = type === 'error' ? 'warning' : 'yes-alt';
		notice.innerHTML = `<span class="dashicons dashicons-${icon}"></span> ${escapeHtml(msg)}`;
		document.body.appendChild(notice);
		setTimeout(() => notice.classList.add('show'), 10);
		setTimeout(() => {
			notice.classList.remove('show');
			setTimeout(() => notice.remove(), 400);
		}, 3500);
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

	// ── Template Library Helpers ─────────────────────────────────

	/** Field type → Dashicons icon map. */
	const fieldIconMap = {
		text: 'dashicons-editor-textcolor',
		email: 'dashicons-email',
		textarea: 'dashicons-editor-paragraph',
		number: 'dashicons-performance',
		select: 'dashicons-arrow-down-alt2',
		radio: 'dashicons-marker',
		checkbox: 'dashicons-yes-alt',
		date: 'dashicons-calendar',
		url: 'dashicons-admin-links',
		tel: 'dashicons-phone',
	};

	/** Active template index for the preview modal. */
	let activeTemplateIndex = null;

	/**
	 * Render the detailed preview body for a template.
	 */
	const renderPreview = (tpl) => {
		const body = document.getElementById('gfm-preview-body');
		const title = document.getElementById('gfm-preview-title');
		if (!body || !title) return;

		title.textContent = tpl.name;

		let html = `<div class="gfm-preview-info"><p class="gfm-preview-desc">${escapeHtml(tpl.description)}</p></div>`;

		html += `<div class="gfm-preview-fields-title"><span class="dashicons dashicons-editor-ul"></span> Fields (${tpl.fields.length})</div>`;
		html += `<div class="gfm-preview-fields-list">`;

		tpl.fields.forEach((field) => {
			const icon = fieldIconMap[field.type] || 'dashicons-admin-generic';
			const badge = field.required
				? `<span class="gfm-preview-field-required">Required</span>`
				: `<span class="gfm-preview-field-optional">Optional</span>`;

			let extra = '';
			if (field.options && field.options.length) {
				extra = ` — ${field.options.length} options`;
			}

			html += `<div class="gfm-preview-field-item">
				<div class="gfm-preview-field-icon"><span class="dashicons ${escapeHtml(icon)}"></span></div>
				<div class="gfm-preview-field-info">
					<strong>${escapeHtml(field.label)}</strong>
					<span>${escapeHtml(field.type)}${extra}</span>
				</div>
				${badge}
			</div>`;
		});

		html += `</div>`;

		// Settings summary
		if (tpl.settings) {
			html += `<div class="gfm-preview-settings">`;
			html += `<div class="gfm-preview-fields-title"><span class="dashicons dashicons-admin-settings"></span> Settings</div>`;
			if (tpl.settings.submit_text) {
				html += `<div class="gfm-preview-setting-row"><span class="dashicons dashicons-button"></span> <span>Button: <strong>${escapeHtml(tpl.settings.submit_text)}</strong></span></div>`;
			}
			if (tpl.settings.success_message) {
				html += `<div class="gfm-preview-setting-row"><span class="dashicons dashicons-yes-alt"></span> <span>Success: <strong>${escapeHtml(tpl.settings.success_message)}</strong></span></div>`;
			}
			if (tpl.settings.gdpr_enabled) {
				html += `<div class="gfm-preview-setting-row"><span class="dashicons dashicons-shield"></span> <span>GDPR consent <strong>enabled</strong></span></div>`;
			}
			html += `</div>`;
		}

		body.innerHTML = html;
	};

	/**
	 * Apply a template: confirm, save via AJAX, and redirect to the builder.
	 */
	const useTemplate = async (index) => {
		const templates = genform.templates || [];
		const tpl = templates[index];
		if (!tpl) return;

		// Confirm before creating.
		const confirmed = await gfmConfirm(
			`Create "${tpl.name}"?`,
			`A new form with ${tpl.fields.length} fields will be created and opened in the builder.`
		);
		if (!confirmed) return;

		showSpinner();

		const res = await gfmFetch('genform_create_from_template', {
			template_name: tpl.name,
			template_fields: JSON.stringify(tpl.fields),
			template_settings: JSON.stringify(tpl.settings),
		});

		hideSpinner();

		if (res.success && res.data && res.data.redirect) {
			showNotice('Template applied! Redirecting to the builder...');
			setTimeout(() => { window.location.href = res.data.redirect; }, 600);
		} else {
			showNotice(res.data?.message || 'Failed to create form. Please try again.', 'error');
		}
	};

	/**
	 * Filter template cards by search text and active category.
	 */
	const filterTemplates = () => {
		const search = (document.getElementById('gfm-template-search')?.value || '').toLowerCase();
		const activeFilter = document.querySelector('.gfm-filter-btn.active')?.dataset.category || 'all';
		const cards = document.querySelectorAll('.gfm-template-card');
		let visible = 0;

		cards.forEach((card) => {
			const cat = card.dataset.category;
			const title = card.querySelector('.gfm-template-card-title')?.textContent?.toLowerCase() || '';
			const desc = card.querySelector('.gfm-template-card-desc')?.textContent?.toLowerCase() || '';
			const matchesCat = activeFilter === 'all' || cat === activeFilter;
			const matchesSearch = !search || title.includes(search) || desc.includes(search) || cat.includes(search);

			if (matchesCat && matchesSearch) {
				card.classList.remove('gfm-hidden');
				visible++;
			} else {
				card.classList.add('gfm-hidden');
			}
		});

		const empty = document.getElementById('gfm-templates-empty');
		if (empty) {
			if (visible === 0) {
				empty.classList.remove('gfm-hidden');
			} else {
				empty.classList.add('gfm-hidden');
			}
		}
	};

	/**
	 * Reset the template library modal to its default state.
	 */
	const resetTemplateLibrary = () => {
		const searchField = document.getElementById('gfm-template-search');
		if (searchField) searchField.value = '';
		const allFilterBtn = document.querySelector('.gfm-filter-btn[data-category="all"]');
		if (allFilterBtn) {
			document.querySelectorAll('.gfm-filter-btn').forEach(b => b.classList.remove('active'));
			allFilterBtn.classList.add('active');
		}
		document.querySelectorAll('.gfm-template-card').forEach(c => c.classList.remove('gfm-hidden'));
		const emptyEl = document.getElementById('gfm-templates-empty');
		if (emptyEl) emptyEl.classList.add('gfm-hidden');
	};

	/**
	 * Main Administration Event Controller
	 */
	const init = () => {

		// ── ESC Key to Close Any Open Modal ───────────────────
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				const openModals = document.querySelectorAll('.gfm-modal.show');
				openModals.forEach((modal) => closeModal('#' + modal.id));
			}
		});

		// ── Template Library: Search Input ────────────────────
		const searchInput = document.getElementById('gfm-template-search');
		if (searchInput) {
			searchInput.addEventListener('input', filterTemplates);
		}

		// ── Template Library: Category Filters ────────────────
		const filterBtns = document.querySelectorAll('.gfm-filter-btn');
		filterBtns.forEach((btn) => {
			btn.addEventListener('click', () => {
				filterBtns.forEach((b) => b.classList.remove('active'));
				btn.classList.add('active');
				filterTemplates();
			});
		});

		document.addEventListener('click', async function (e) {
			const target = e.target;

			// ── Add New Form: Open Create Form Chooser ───────
			if (target.closest('#gfm-add-new-form')) {
				e.preventDefault();
				openModal('#gfm-create-form-modal');
				return;
			}

			// ── Create Form Chooser: Choose a Template ───────
			if (target.closest('#gfm-create-from-template')) {
				e.preventDefault();
				closeModal('#gfm-create-form-modal');
				setTimeout(() => {
					resetTemplateLibrary();
					openModal('#gfm-templates-modal');
				}, 350);
				return;
			}

			// ── Template Library: Open Modal (from empty state / direct) ──
			if (target.closest('#gfm-open-templates')) {
				e.preventDefault();
				resetTemplateLibrary();
				openModal('#gfm-templates-modal');
				return;
			}

			// ── Template Library: Preview Button ──────────────
			const previewBtn = target.closest('.gfm-template-preview-btn');
			if (previewBtn) {
				e.preventDefault();
				const index = parseInt(previewBtn.dataset.index, 10);
				const templates = genform.templates || [];
				if (templates[index]) {
					activeTemplateIndex = index;
					renderPreview(templates[index]);
					closeModal('#gfm-templates-modal');
					setTimeout(() => openModal('#gfm-template-preview-modal'), 350);
				}
				return;
			}

			// ── Template Library: Use Template Button ─────────
			const useBtn = target.closest('.gfm-template-use-btn');
			if (useBtn && !useBtn.disabled) {
				e.preventDefault();
				const index = parseInt(useBtn.dataset.index, 10);
				closeModal('#gfm-templates-modal');
				useBtn.disabled = true;
				await useTemplate(index);
				useBtn.disabled = false;
				return;
			}

			// ── Template Preview: Back to Library ─────────────
			if (target.closest('#gfm-preview-back')) {
				e.preventDefault();
				closeModal('#gfm-template-preview-modal');
				setTimeout(() => openModal('#gfm-templates-modal'), 350);
				return;
			}

			// ── Template Preview: Cancel ──────────────────────
			if (target.closest('#gfm-preview-cancel')) {
				e.preventDefault();
				closeModal('#gfm-template-preview-modal');
				return;
			}

			// ── Template Preview: Use This Template ───────────
			const previewUseBtn = target.closest('#gfm-preview-use');
			if (previewUseBtn && !previewUseBtn.disabled) {
				e.preventDefault();
				closeModal('#gfm-template-preview-modal');
				if (activeTemplateIndex !== null) {
					previewUseBtn.disabled = true;
					await useTemplate(activeTemplateIndex);
					previewUseBtn.disabled = false;
				}
				return;
			}

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

