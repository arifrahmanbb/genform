/**
 * GenForm Frontend JavaScript (Vanilla JS)
 *
 * Manages dynamic typography and handling of form submissions via Fetch API.
 */

(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		/**
		 * Initialization: (Dynamic styles moved to PHP/CSS)
		 */


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
