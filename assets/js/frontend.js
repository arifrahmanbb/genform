/**
 * GenForm Frontend JavaScript (Vanilla JS)
 *
 * Manages dynamic typography and handling of form submissions via Fetch API.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/**
		 * Handles form submission via Fetch API.
		 */
		document.addEventListener('submit', async function (e) {
			const form = e.target.closest('.gfm-form-js');
			if (!form) return;

			e.preventDefault();

			// Validate required checkbox groups (at least one must be checked).
			const msg = form.querySelector('.gfm-message');
			const requiredCheckboxGroups = form.querySelectorAll('.gfm-options-list[data-required]');
			let checkboxValid = true;
			requiredCheckboxGroups.forEach(function (group) {
				const checked = group.querySelectorAll('input[type="checkbox"]:checked');
				if (checked.length === 0) {
					checkboxValid = false;
					group.style.outline = '2px solid #e74c3c';
					group.style.borderRadius = '4px';
				} else {
					group.style.outline = '';
					group.style.borderRadius = '';
				}
			});
			if (!checkboxValid) {
				msg.textContent = 'Please select at least one option for required checkbox fields.';
				msg.className = 'gfm-message error';
				return;
			}

			// Validate GDPR consent checkbox if present.
			const gdprCheckbox = form.querySelector('.gfm-gdpr-checkbox');
			if (gdprCheckbox && !gdprCheckbox.checked) {
				msg.textContent = 'Please accept the consent checkbox to proceed.';
				msg.className = 'gfm-message error';
				gdprCheckbox.closest('.gfm-gdpr-field').style.outline = '2px solid #e74c3c';
				gdprCheckbox.closest('.gfm-gdpr-field').style.borderRadius = '4px';
				return;
			}
			if (gdprCheckbox) {
				const gdprField = gdprCheckbox.closest('.gfm-gdpr-field');
				if (gdprField) {
					gdprField.style.outline = '';
					gdprField.style.borderRadius = '';
				}
			}

			const btn = form.querySelector('.gfm-submit');
			const originalText = btn.textContent;
			const formData = new FormData(form);

			// Prepare UI for submission state
			btn.disabled = true;
			btn.textContent = '...';
			msg.textContent = '';
			msg.className = 'gfm-message gfm-hidden';

			try {
				const response = await fetch(genform.ajax_url, {
					method: 'POST',
					body: formData
				});

				const result = await response.json();

				if (result.success) {
					msg.textContent = result.data.message;
					msg.className = 'gfm-message success';

					if (result.data.redirect) {
						window.location.href = result.data.redirect;
					}
					form.reset();
				} else {
					msg.textContent = result.data.message || 'An error occurred.';
					msg.className = 'gfm-message error';
				}
			} catch (error) {
				console.error('GenForm Submission Error:', error);
				msg.textContent = 'An unknown error occurred.';
				msg.className = 'gfm-message error';
			} finally {
				btn.disabled = false;
				btn.textContent = originalText;
			}
		});
	});

})();
