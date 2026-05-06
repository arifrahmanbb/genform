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

	const NOTICE_ICONS = {
		success: 'yes-alt',
		error:   'warning',
		info:    'info-outline',
		warning: 'flag',
		loading: 'update',
	};

	/**
	 * Floating notice HUD.
	 *
	 * @param {string}   msg
	 * @param {string}   type    success|error|info|warning|loading
	 * @param {object}   opts    { duration: ms (default 3500), action: { label, onClick } }
	 * @returns {{ dismiss: () => void, update: (msg, type) => void }}
	 */
	const showNotice = (msg, type = 'success', opts = {}) => {
		const notice = document.createElement('div');
		notice.className = `gfm-builder-notice gfm-notice-${type}`;
		const buildBody = (m, t) => {
			const icon = NOTICE_ICONS[t] || 'yes-alt';
			let html = `<span class="dashicons dashicons-${icon} ${t === 'loading' ? 'is-spinning' : ''}"></span><span class="gfm-notice-text">${escapeHtml(m)}</span>`;
			if (opts.action && opts.action.label) {
				html += `<button type="button" class="gfm-notice-action">${escapeHtml(opts.action.label)}</button>`;
			}
			return html;
		};
		notice.innerHTML = buildBody(msg, type);

		if (opts.action && typeof opts.action.onClick === 'function') {
			notice.addEventListener('click', (e) => {
				if (e.target.classList.contains('gfm-notice-action')) {
					opts.action.onClick();
					dismiss();
				}
			});
		}

		document.body.appendChild(notice);
		setTimeout(() => notice.classList.add('show'), 10);

		const duration = opts.duration ?? (type === 'loading' ? 0 : 3500);
		let timer = null;
		const dismiss = () => {
			if (timer) { clearTimeout(timer); timer = null; }
			notice.classList.remove('show');
			setTimeout(() => notice.remove(), 400);
		};
		if (duration > 0) timer = setTimeout(dismiss, duration);

		return {
			dismiss,
			update: (newMsg, newType) => {
				notice.className = `gfm-builder-notice gfm-notice-${newType} show`;
				notice.innerHTML = buildBody(newMsg, newType);
				if (timer) clearTimeout(timer);
				if (newType !== 'loading') timer = setTimeout(dismiss, 3500);
			},
		};
	};

	/**
	 * AJAX Fetch Wrapper.
	 * Pass `{ loadingMessage: 'Saving…' }` to auto-show a loading toast that
	 * resolves to success/error on completion.
	 */
	const gfmFetch = async (action, data = {}, opts = {}) => {
		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', genform.nonce);
		for (const key in data) formData.append(key, data[key]);

		const toast = opts.loadingMessage
			? showNotice(opts.loadingMessage, 'loading')
			: null;

		try {
			const response = await fetch(genform.ajax_url, { method: 'POST', body: formData });
			const json = await response.json();
			if (toast && opts.successMessage && json.success) {
				toast.update(opts.successMessage, 'success');
			} else if (toast && !json.success) {
				toast.update(json.data?.message || 'Action failed.', 'error');
			} else if (toast) {
				toast.dismiss();
			}
			return json;
		} catch (error) {
			console.error('GenForm AJAX Error:', error);
			if (toast) toast.update('Network error — please try again.', 'error');
			return { success: false, data: { message: 'Network error occurred.' } };
		}
	};

	// Export helpers to window for other scripts (like form-builder.js)
	window.gfmAdmin = { openModal, closeModal, showSpinner, hideSpinner, showNotice, gfmConfirm };

	/**
	 * Pro Upgrade Modal handler.
	 * Opens the pricing modal when locked items are clicked.
	 * Sets the context banner with the feature name that triggered it.
	 */
	const openProModal = (featureSlug) => {
		const modal = document.getElementById('gfm-pro-upgrade-modal');
		if (!modal) return;

		// Update the context banner with the feature name.
		const contextText = document.getElementById('gfm-pro-context-text');
		if (contextText && featureSlug) {
			// Look for the feature title in the card or tab that was clicked.
			const card = document.querySelector(`[data-feature="${featureSlug}"] .gfm-pro-preview-header h4`);
			const featureTitle = card ? card.textContent.trim() : featureSlug.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
			contextText.textContent = `"${featureTitle}" requires GenForm Pro to unlock.`;
		}

		openModal('#gfm-pro-upgrade-modal');
	};



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

		// Pro locked fields & tabs — open upgrade pricing modal.
		document.querySelectorAll('.gfm-pro-field-locked, .gfm-pro-tab-locked').forEach((el) => {
			el.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const slug = el.getAttribute('data-pro') || el.getAttribute('data-feature') || '';
				openProModal(slug);
			});
		});


		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				const openModals = document.querySelectorAll('.gfm-modal.show');
				openModals.forEach((modal) => closeModal('#' + modal.id));
			}
		});


		const searchInput = document.getElementById('gfm-template-search');
		if (searchInput) {
			searchInput.addEventListener('input', filterTemplates);
		}


		const filterBtns = document.querySelectorAll('.gfm-filter-btn');
		filterBtns.forEach((btn) => {
			btn.addEventListener('click', () => {
				filterBtns.forEach((b) => b.classList.remove('active'));
				btn.classList.add('active');
				filterTemplates();
			});
		});

		// JSON Import handler
		const importFile = document.getElementById('gfm-import-file');
		if (importFile) {
			importFile.addEventListener('change', async (e) => {
				const file = e.target.files[0];
				if (!file) return;
				const reader = new FileReader();
				reader.onload = async (evt) => {
					showSpinner();
					const res = await gfmFetch('genform_import_form_json', { json_data: evt.target.result });
					hideSpinner();
					if (res.success) {
						showNotice('Form imported successfully. Redirecting...');
						setTimeout(() => { window.location.href = res.data.redirect; }, 800);
					} else {
						showNotice(res.data?.message || 'Import failed. Please check the file.', 'error');
					}
				};
				reader.readAsText(file);
				// Reset so the same file can be re-selected if needed
				importFile.value = '';
			});
		}

		document.addEventListener('click', async function (e) {
			const target = e.target;


			if (target.closest('#gfm-add-new-form')) {
				e.preventDefault();
				openModal('#gfm-create-form-modal');
				return;
			}


			if (target.closest('#gfm-create-from-template')) {
				e.preventDefault();
				closeModal('#gfm-create-form-modal');
				setTimeout(() => {
					resetTemplateLibrary();
					openModal('#gfm-templates-modal');
				}, 350);
				return;
			}


			if (target.closest('#gfm-open-templates')) {
				e.preventDefault();
				resetTemplateLibrary();
				openModal('#gfm-templates-modal');
				return;
			}


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


			if (target.closest('#gfm-preview-back')) {
				e.preventDefault();
				closeModal('#gfm-template-preview-modal');
				setTimeout(() => openModal('#gfm-templates-modal'), 350);
				return;
			}


			if (target.closest('#gfm-preview-cancel')) {
				e.preventDefault();
				closeModal('#gfm-template-preview-modal');
				return;
			}


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

			// 5a. Star Entry toggle
			const starBtn = target.closest('.gfm-star-btn');
			if (starBtn) {
				e.preventDefault();
				const entryId = starBtn.dataset.entryId;
				const res = await gfmFetch('genform_star_entry', { entry_id: entryId });
				if (res.success) {
					const isStarred = res.data.starred;
					const icon = starBtn.querySelector('.dashicons');
					if (icon) {
						icon.classList.toggle('dashicons-star-filled', isStarred);
						icon.classList.toggle('dashicons-star-empty', !isStarred);
					}
					starBtn.classList.toggle('gfm-starred', isStarred);
					starBtn.title = isStarred ? 'Unstar' : 'Star';
				}
				return;
			}

			// 5b. Form status toggle
			const statusToggle = target.closest('.gfm-toggle-status');
			if (statusToggle) {
				const formId = statusToggle.dataset.formId;
				const res = await gfmFetch('genform_toggle_form_status', { form_id: formId });
				if (res.success) {
					const isActive = res.data.status === 'active';
					statusToggle.checked = isActive;
					const label = statusToggle.closest('.gfm-status-toggle');
					if (label) label.title = isActive ? 'Click to deactivate' : 'Click to activate';
					showNotice(isActive ? 'Form activated.' : 'Form deactivated.');
				} else {
					// Revert the checkbox if request failed
					statusToggle.checked = !statusToggle.checked;
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

/* ─── Onboarding Wizard ─────────────────────────────────────────────────────── */
(function () {
	'use strict';

	if (typeof genform === 'undefined') return;

	const wizard = document.getElementById('gfm-onboarding-wizard');
	if (!wizard) return;

	let selectedCategory = 'blank';

	const panels     = wizard.querySelectorAll('.gfm-wiz-panel');
	const steps      = wizard.querySelectorAll('.gfm-wiz-step');
	const emailInput = wizard.querySelector('.gfm-wiz-email-input');

	const showPanel = (n) => {
		panels.forEach((p) => {
			p.classList.toggle('gfm-hidden', +p.dataset.panel !== n);
		});
		steps.forEach((s) => {
			const sn = +s.dataset.step;
			s.classList.toggle('active', sn === n);
			s.classList.toggle('done',   sn < n);
		});
	};

	const openWizard = () => {
		showPanel(1);
		selectedCategory = 'blank';
		wizard.classList.remove('gfm-hidden');
		wizard.offsetHeight; // force reflow for CSS transition
		wizard.classList.add('show');
	};

	const closeWizard = () => {
		wizard.classList.remove('show');
		setTimeout(() => wizard.classList.add('gfm-hidden'), 300);
	};

	const dismiss = async () => {
		closeWizard();
		await fetch(genform.ajax_url, {
			method: 'POST',
			body:   new URLSearchParams({ action: 'genform_dismiss_onboarding', nonce: genform.nonce }),
		});
	};

	const createForm = async () => {
		const btn       = wizard.querySelector('.gfm-wiz-create-btn');
		const labelEl   = wizard.querySelector('.gfm-wiz-create-label');
		const loadingEl = wizard.querySelector('.gfm-wiz-create-loading');
		const email     = (emailInput && emailInput.value.trim()) || genform.adminEmail;

		btn.disabled = true;
		labelEl.classList.add('gfm-hidden');
		loadingEl.classList.remove('gfm-hidden');

		const body = new URLSearchParams({
			action:             'genform_wizard_create_form',
			nonce:              genform.nonce,
			category:           selectedCategory,
			notification_email: email,
		});

		try {
			const res  = await fetch(genform.ajax_url, { method: 'POST', body });
			const json = await res.json();
			if (json.success && json.data.redirect) {
				window.location.href = json.data.redirect;
			} else {
				btn.disabled = false;
				labelEl.classList.remove('gfm-hidden');
				loadingEl.classList.add('gfm-hidden');
			}
		} catch {
			btn.disabled = false;
			labelEl.classList.remove('gfm-hidden');
			loadingEl.classList.add('gfm-hidden');
		}
	};

	// Wire events.
	wizard.querySelector('.gfm-wiz-next-btn')?.addEventListener('click', () => showPanel(2));

	wizard.querySelectorAll('.gfm-wiz-skip').forEach((el) =>
		el.addEventListener('click', dismiss)
	);

	wizard.querySelectorAll('.gfm-wiz-cat-card').forEach((card) => {
		card.addEventListener('click', () => {
			selectedCategory = card.dataset.category;
			showPanel(3);
		});
	});

	wizard.querySelector('.gfm-wiz-back')?.addEventListener('click', () => showPanel(2));
	wizard.querySelector('.gfm-wiz-create-btn')?.addEventListener('click', createForm);

	emailInput?.addEventListener('keydown', (e) => {
		if (e.key === 'Enter') createForm();
	});

	// Relaunch button on Settings page — works regardless of showWizard flag.
	document.getElementById('gfm-relaunch-wizard-btn')?.addEventListener('click', async () => {
		await fetch(genform.ajax_url, {
			method: 'POST',
			body: new URLSearchParams({ action: 'genform_relaunch_wizard', nonce: genform.nonce }),
		});
		openWizard();
	});

	// Auto-open on first visit.
	if (genform.showWizard) {
		openWizard();
	}
})();

