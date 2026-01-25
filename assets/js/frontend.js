/**
 * GenForm Frontend JavaScript (Vanilla JS)
 *
 * Manages dynamic typography and handling of form submissions via Fetch API.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/**
		 * Initialization: Apply dynamic styles based on form settings.
		 */
		const containers = document.querySelectorAll('.gfm-form-container');
		containers.forEach(container => {
			const size = container.dataset.size;
			const weight = container.dataset.weight;

			if (size) {
				container.style.setProperty('--gfm-base-size', size + 'px');
			}
			if (weight) {
				container.style.fontWeight = weight;
			}
		});

		/**
		 * Handles form submission via Fetch API.
		 */
		document.addEventListener('submit', async function (e) {
			const form = e.target.closest('.gfm-form-js');
			if (!form) return;

			e.preventDefault();

			const btn = form.querySelector('.gfm-submit');
			const msg = form.querySelector('.gfm-message');
			const originalText = btn.textContent;
			const formData = new FormData(form);

			// Prepare UI for submission state
			btn.disabled = true;
			btn.textContent = '...';
			msg.className = 'gfm-message gfm-hidden';
			msg.textContent = '';
			msg.style.display = 'none';

			try {
				const response = await fetch(genform.ajax_url, {
					method: 'POST',
					body: formData
				});

				const result = await response.json();

				if (result.success) {
					msg.classList.remove('gfm-hidden');
					msg.classList.add('gfm-success');
					msg.textContent = result.data.message;
					msg.style.display = 'block';

					if (result.data.redirect) {
						window.location.href = result.data.redirect;
					}
					form.reset();
				} else {
					msg.classList.remove('gfm-hidden');
					msg.classList.add('gfm-error');
					msg.textContent = result.data.message || 'An error occurred.';
					msg.style.display = 'block';
				}
			} catch (error) {
				console.error('GenForm Submission Error:', error);
				msg.classList.remove('gfm-hidden');
				msg.classList.add('gfm-error');
				msg.textContent = 'An unknown error occurred.';
				msg.style.display = 'block';
			} finally {
				btn.disabled = false;
				btn.textContent = originalText;
			}
		});
	});

})();
