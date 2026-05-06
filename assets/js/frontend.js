/**
 * GenForm Frontend JavaScript (Vanilla JS)
 *
 * Inline field validation on blur, URL parameter prefill,
 * and AJAX form submission handling.
 */

( function () {
	'use strict';

	// ─── Regex patterns ───────────────────────────────────────────────────────

	const PATTERNS = {
		email: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
		url:   /^https?:\/\/.{2,}/,
		tel:   /^[+\d\s\-().]{7,20}$/,
	};

	// ─── i18n helper ─────────────────────────────────────────────────────────

	function t( key, replacements ) {
		const defaults = {
			required:  'This field is required.',
			email:     'Please enter a valid email address.',
			url:       'Please enter a valid URL (e.g. https://example.com).',
			tel:       'Please enter a valid phone number.',
			number:    'Please enter a valid number.',
			minlength: 'Please enter at least {min} characters.',
			maxlength: 'Please enter no more than {max} characters.',
			min:       'Value must be at least {min}.',
			max:       'Value must be no more than {max}.',
		};
		const i18n = ( typeof genform !== 'undefined' && genform.i18n ) ? genform.i18n : {};
		let msg = i18n[ key + '_error' ] || defaults[ key ] || '';
		if ( replacements ) {
			Object.keys( replacements ).forEach( function ( k ) {
				msg = msg.replace( '{' + k + '}', replacements[ k ] );
			} );
		}
		return msg;
	}

	// ─── DOM helpers ──────────────────────────────────────────────────────────

	function setError( wrapper, message ) {
		wrapper.classList.add( 'error' );
		wrapper.classList.remove( 'success' );

		let errEl = wrapper.querySelector( '.gfm-error-message' );
		if ( ! errEl ) {
			errEl = document.createElement( 'span' );
			errEl.className = 'gfm-error-message';
			errEl.setAttribute( 'role', 'alert' );
			errEl.setAttribute( 'aria-live', 'polite' );
			wrapper.appendChild( errEl );
		}
		errEl.textContent = message;
	}

	function clearError( wrapper, markSuccess ) {
		wrapper.classList.remove( 'error', 'success' );
		const errEl = wrapper.querySelector( '.gfm-error-message' );
		if ( errEl ) {
			errEl.remove();
		}
		if ( markSuccess ) {
			wrapper.classList.add( 'success' );
		}
	}

	// ─── Field validation ─────────────────────────────────────────────────────

	function validateField( field ) {
		const wrapper  = field.closest( '.gfm-form-field' );
		if ( ! wrapper ) {
			return true;
		}

		const value    = field.value.trim();
		const required = field.hasAttribute( 'required' ) || field.required;
		const type     = field.type || 'text';

		// Required check.
		if ( required && ! value ) {
			setError( wrapper, t( 'required' ) );
			return false;
		}

		if ( value ) {
			// Email format.
			if ( 'email' === type && ! PATTERNS.email.test( value ) ) {
				setError( wrapper, t( 'email' ) );
				return false;
			}

			// URL format.
			if ( 'url' === type && ! PATTERNS.url.test( value ) ) {
				setError( wrapper, t( 'url' ) );
				return false;
			}

			// Phone/tel pattern.
			if ( 'tel' === type && ! PATTERNS.tel.test( value ) ) {
				setError( wrapper, t( 'tel' ) );
				return false;
			}

			// Number range.
			if ( 'number' === type ) {
				const num = parseFloat( value );
				if ( isNaN( num ) ) {
					setError( wrapper, t( 'number' ) );
					return false;
				}
				if ( '' !== field.min && num < parseFloat( field.min ) ) {
					setError( wrapper, t( 'min', { min: field.min } ) );
					return false;
				}
				if ( '' !== field.max && num > parseFloat( field.max ) ) {
					setError( wrapper, t( 'max', { max: field.max } ) );
					return false;
				}
			}

			// Min / max character length.
			if ( field.minLength > 0 && value.length < field.minLength ) {
				setError( wrapper, t( 'minlength', { min: field.minLength } ) );
				return false;
			}
			// maxLength default is 524288 (browser default when unset); skip unless explicitly set.
			if ( field.maxLength > 0 && field.maxLength < 524288 && value.length > field.maxLength ) {
				setError( wrapper, t( 'maxlength', { max: field.maxLength } ) );
				return false;
			}
		}

		// Valid — show success ring only when there is a value (avoid green-marking empty optional fields).
		clearError( wrapper, !! value );
		return true;
	}

	function validateCheckboxGroup( group ) {
		const anyChecked = group.querySelectorAll( 'input[type="checkbox"]:checked' ).length > 0;
		if ( ! anyChecked ) {
			group.style.outline      = '2px solid #ef4444';
			group.style.borderRadius = '4px';
			return false;
		}
		group.style.outline      = '';
		group.style.borderRadius = '';
		return true;
	}

	// ─── URL parameter prefill ────────────────────────────────────────────────

	function prefillFromUrl() {
		if ( ! window.URLSearchParams ) {
			return;
		}
		const params = new URLSearchParams( window.location.search );
		if ( ! params.size ) {
			return;
		}
		document.querySelectorAll( '.gfm-form-js' ).forEach( function ( form ) {
			form.querySelectorAll( '.gfm-input, .gfm-textarea, .gfm-select' ).forEach( function ( field ) {
				if ( ! field.name ) {
					return;
				}
				// Field names are prefixed "gfm_" — strip to get the parameter name.
				const key = field.name.replace( /^gfm_/, '' );
				if ( params.has( key ) ) {
					// Cap at 1000 chars to prevent oversized injections.
					field.value = params.get( key ).substring( 0, 1000 );
				}
			} );
		} );
	}

	// ─── Event wiring ─────────────────────────────────────────────────────────

	document.addEventListener( 'DOMContentLoaded', function () {

		// Populate fields from URL query string (e.g. ?email=user@example.com).
		prefillFromUrl();

		// Blur: validate individual field when user leaves it.
		document.addEventListener( 'blur', function ( e ) {
			const field = e.target;
			if (
				field.closest( '.gfm-form-js' ) &&
				field.matches( '.gfm-input, .gfm-textarea, .gfm-select' )
			) {
				validateField( field );
			}
		}, true ); // use capture so blur fires on non-focusable ancestors too

		// Submit: full validation before AJAX.
		document.addEventListener( 'submit', async function ( e ) {
			const form = e.target.closest( '.gfm-form-js' );
			if ( ! form ) {
				return;
			}
			e.preventDefault();

			const msg     = form.querySelector( '.gfm-message' );
			let   isValid = true;

			// 1. Validate text / textarea / select fields.
			form.querySelectorAll( '.gfm-input, .gfm-textarea, .gfm-select' ).forEach( function ( field ) {
				if ( ! validateField( field ) ) {
					isValid = false;
				}
			} );

			// 2. Validate required checkbox groups (at least one option selected).
			form.querySelectorAll( '.gfm-options-list[data-required]' ).forEach( function ( group ) {
				if ( ! validateCheckboxGroup( group ) ) {
					isValid = false;
				}
			} );

			// 3. Validate GDPR consent checkbox.
			const gdprCheckbox = form.querySelector( '.gfm-gdpr-checkbox' );
			if ( gdprCheckbox && ! gdprCheckbox.checked ) {
				msg.textContent = t( 'gdpr' );
				msg.className   = 'gfm-message error';
				const gdprField = gdprCheckbox.closest( '.gfm-gdpr-field' );
				if ( gdprField ) {
					gdprField.style.outline      = '2px solid #ef4444';
					gdprField.style.borderRadius = '4px';
				}
				isValid = false;
			} else if ( gdprCheckbox ) {
				const gdprField = gdprCheckbox.closest( '.gfm-gdpr-field' );
				if ( gdprField ) {
					gdprField.style.outline      = '';
					gdprField.style.borderRadius = '';
				}
			}

			if ( ! isValid ) {
				// Scroll to the first invalid field and focus it.
				const firstError = form.querySelector( '.gfm-form-field.error, .gfm-options-list[style*="outline"]' );
				if ( firstError ) {
					firstError.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					const focusable = firstError.querySelector( '.gfm-input, .gfm-textarea, .gfm-select' );
					if ( focusable ) {
						focusable.focus( { preventScroll: true } );
					}
				}
				return;
			}

			// ─── AJAX submission ──────────────────────────────────────────────

			const btn            = form.querySelector( '.gfm-submit' );
			const originalHTML   = btn.innerHTML;
			const submittingText = ( genform.i18n && genform.i18n.submitting ) ? genform.i18n.submitting : 'Submitting...';

			btn.disabled  = true;
			btn.innerHTML = '<span class="gfm-spinner"></span> ' + submittingText;
			msg.textContent = '';
			msg.className   = 'gfm-message gfm-hidden';

			try {
				const response = await fetch( genform.ajax_url, {
					method: 'POST',
					body:   new FormData( form ),
				} );
				const result = await response.json();

				if ( result.success ) {
					msg.textContent = result.data.message;
					msg.className   = 'gfm-message success';

					// Clear all field validation states after a successful submission.
					form.querySelectorAll( '.gfm-form-field' ).forEach( function ( f ) {
						f.classList.remove( 'error', 'success' );
						const errEl = f.querySelector( '.gfm-error-message' );
						if ( errEl ) {
							errEl.remove();
						}
					} );

					if ( result.data.redirect ) {
						window.location.href = result.data.redirect;
					}
					form.reset();
				} else {
					msg.textContent = ( result.data && result.data.message )
						? result.data.message
						: ( ( genform.i18n && genform.i18n.generic_error ) ? genform.i18n.generic_error : 'An error occurred.' );
					msg.className = 'gfm-message error';
				}
			} catch ( error ) {
				console.error( 'GenForm Submission Error:', error );
				msg.textContent = ( genform.i18n && genform.i18n.unknown_error )
					? genform.i18n.unknown_error
					: 'An unknown error occurred.';
				msg.className = 'gfm-message error';
			} finally {
				btn.disabled  = false;
				btn.innerHTML = originalHTML;
			}
		} );
	} );

} )();
