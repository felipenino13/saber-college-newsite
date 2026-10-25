<?php
/**
 * Plugin Name: SABER Accessibility Enhancements
 * Description: Sitewide focus, reduced-motion, and third-party widget accessibility safeguards.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		?>
		<style id="saber-accessibility-css">
			/* Keep footer controls easy to tap without changing the Elementor layout. */
			footer a.elementor-icon,
			[data-elementor-type="footer"] a.elementor-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 40px;
				min-height: 40px;
			}

			footer a[href]:not(.elementor-button),
			[data-elementor-type="footer"] a[href]:not(.elementor-button) {
				display: inline-flex;
				align-items: center;
				min-height: 24px;
			}

			.text-canvas a[href] {
				display: inline-flex;
				align-items: center;
				min-height: 24px;
			}

			.elementor a.elementor-button-link,
			.elementor button.elementor-button {
				transition: filter 160ms ease, transform 160ms ease;
			}

			.elementor a.elementor-button-link:hover,
			.elementor button.elementor-button:hover {
				filter: brightness(0.92);
				transform: translateY(-1px);
			}

			.elementor a.elementor-button-link:active,
			.elementor button.elementor-button:active {
				filter: brightness(0.84);
				transform: translateY(0);
			}

			:where(a, button, input, select, textarea, summary, iframe, [tabindex]:not([tabindex="-1"])):focus-visible {
				outline: 3px solid #003f7d !important;
				outline-offset: 3px !important;
				box-shadow: 0 0 0 2px #fff !important;
			}

			iframe:focus {
				outline: 3px solid #003f7d !important;
				outline-offset: 3px !important;
				box-shadow: 0 0 0 2px #fff !important;
			}

			@media (prefers-reduced-motion: reduce) {
				html:focus-within {
					scroll-behavior: auto !important;
				}

				*, *::before, *::after {
					animation-duration: 0.01ms !important;
					animation-iteration-count: 1 !important;
					transition-duration: 0.01ms !important;
					scroll-behavior: auto !important;
				}
			}
		</style>
		<?php
	},
	99
);

add_action(
	'wp_footer',
	function () {
		?>
		<script id="saber-accessibility-js">
			(() => {
				const enhance = () => {
					if (!document.querySelector('main, [role="main"]')) {
						const pageContent = document.querySelector('.elementor[data-elementor-type="wp-page"]');
						if (pageContent) pageContent.setAttribute('role', 'main');
					}

					document.querySelectorAll('.iti__selected-country[aria-label]').forEach((selector) => {
						const dropdown = selector.closest('.iti')?.querySelector('.iti__dropdown-content, .iti__country-list');
						if (!dropdown) {
							selector.setAttribute('role', 'img');
							selector.setAttribute('tabindex', '-1');
							const country = selector.getAttribute('title');
							if (country) selector.setAttribute('aria-label', `Phone country code: ${country}`);
							return;
						}

						selector.setAttribute('role', 'button');
						selector.setAttribute('tabindex', '0');
						if (selector.dataset.saberKeyboardReady) return;
						selector.dataset.saberKeyboardReady = 'true';
						selector.addEventListener('keydown', (event) => {
							if (event.key === 'Enter' || event.key === ' ') {
								event.preventDefault();
								selector.click();
							}
						});
					});

					document.querySelectorAll('.e-n-accordion[aria-label]:not([role])').forEach((accordion) => {
						accordion.setAttribute('role', 'region');
					});

					document.querySelectorAll('[aria-controls]').forEach((trigger) => {
						const controlledId = trigger.getAttribute('aria-controls');
						if (!controlledId || document.getElementById(controlledId)) return;
						const candidate = trigger.nextElementSibling;
						if (candidate && candidate.matches('ul, [role="menu"], .sub-menu')) {
							candidate.id = controlledId;
						}
					});

					document.querySelectorAll('textarea[placeholder="Type your message..."]').forEach((textarea) => {
						if (!textarea.getAttribute('aria-label') && !textarea.getAttribute('aria-labelledby') && !textarea.labels?.length) {
							textarea.setAttribute('aria-label', 'Type your message to SABER College');
						}
					});

					document.querySelectorAll('form input:not([type="hidden"]), form textarea').forEach((field) => {
						const label = [
							field.name,
							field.id,
							field.placeholder,
							field.getAttribute('aria-label'),
							field.labels ? [...field.labels].map((item) => item.textContent).join(' ') : '',
						].filter(Boolean).join(' ').toLowerCase();

						if (!field.getAttribute('autocomplete')) {
							if (/first.?name|given.?name/.test(label)) field.setAttribute('autocomplete', 'given-name');
							else if (/last.?name|family.?name|surname/.test(label)) field.setAttribute('autocomplete', 'family-name');
							else if (/full.?name|your.?name/.test(label)) field.setAttribute('autocomplete', 'name');
							else if (/e-?mail/.test(label)) field.setAttribute('autocomplete', 'email');
							else if (/phone|telephone|mobile/.test(label)) field.setAttribute('autocomplete', 'tel');
						}

						if (/phone|telephone|mobile/.test(label)) field.setAttribute('inputmode', 'tel');
						if (/expense|amount|monthly/.test(label) && field.tagName === 'INPUT') field.setAttribute('inputmode', 'decimal');
					});

					document.querySelectorAll('video[muted][loop]').forEach((video) => {
						video.setAttribute('aria-hidden', 'true');
						video.setAttribute('tabindex', '-1');
					});

					document.querySelectorAll('style[id="loop-dynamic-312"]').forEach((style, index) => {
						style.id = `loop-dynamic-312-${index + 1}`;
					});

					document.querySelectorAll('.footable-page-link[aria-label]').forEach((link) => {
						const visibleText = link.textContent.trim();
						const label = link.getAttribute('aria-label').trim();
						if (visibleText && !label.includes(visibleText)) {
							link.setAttribute('aria-label', `${label} (${visibleText})`);
						}
					});
				};

				let scheduled = false;
				const scheduleEnhancement = () => {
					if (scheduled) return;
					scheduled = true;
					requestAnimationFrame(() => {
						scheduled = false;
						enhance();
					});
				};

				enhance();
				new MutationObserver(scheduleEnhancement).observe(document.documentElement, {
					childList: true,
					subtree: true,
					attributes: true,
					attributeFilter: ['aria-label'],
				});
			})();
		</script>
		<?php
	},
	99
);

