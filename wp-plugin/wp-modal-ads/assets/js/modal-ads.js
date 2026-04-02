/**
 * WP Modal Ads — Frontend Script
 *
 * Reads configuration from the wpModalAdsConfig global (set via wp_localize_script)
 * and shows a modal ad based on the configured trigger, respecting the per-visitor
 * per-hour limit tracked in localStorage.
 */
(function () {
	'use strict';

	var config = window.wpModalAdsConfig || {};
	var adUrl         = config.adUrl        || '';
	var modalSize     = parseInt(config.modalSize, 10)     || 100;
	var skipDelay     = parseInt(config.skipDelay, 10)     || 5;
	var trigger       = config.trigger      || 'click';
	var scrollPercent = parseInt(config.scrollPercent, 10) || 30;
	var timeDelay     = parseInt(config.timeDelay, 10)     || 3;
	var maxPerHour    = parseInt(config.maxPerHour, 10)    || 3;

	var STORAGE_KEY = 'wpModalAds_hourlyCount';
	var triggered   = false;

	// ------------------------------------------------------------------
	// Rate limiting helpers (localStorage)
	// ------------------------------------------------------------------

	/**
	 * Returns the current hourly impression count for this visitor.
	 * Resets automatically when the recorded hour changes.
	 *
	 * @returns {number}
	 */
	function getHourlyCount() {
		try {
			var raw    = localStorage.getItem(STORAGE_KEY);
			var data   = raw ? JSON.parse(raw) : null;
			var nowHr  = Math.floor(Date.now() / 3600000);

			if (!data || data.hour !== nowHr) {
				return 0;
			}
			return data.count || 0;
		} catch (e) {
			return 0;
		}
	}

	/**
	 * Increment the hourly impression counter in localStorage.
	 */
	function incrementHourlyCount() {
		try {
			var nowHr = Math.floor(Date.now() / 3600000);
			localStorage.setItem(STORAGE_KEY, JSON.stringify({ hour: nowHr, count: getHourlyCount() + 1 }));
		} catch (e) {}
	}

	/**
	 * Returns true when the visitor has NOT yet reached the hourly limit.
	 *
	 * @returns {boolean}
	 */
	function canShowAd() {
		return getHourlyCount() < maxPerHour;
	}

	// ------------------------------------------------------------------
	// Modal logic
	// ------------------------------------------------------------------

	/**
	 * Open the modal, set up the skip-button countdown, and record the impression.
	 */
	function showModal() {
		if (!canShowAd()) return;
		if (triggered) return;
		triggered = true;

		var overlay   = document.getElementById('wp-modal-ads-overlay');
		var container = document.getElementById('wp-modal-ads-container');
		var skipBtn   = document.getElementById('wp-modal-ads-skip');
		var countdown = document.getElementById('wp-modal-ads-skip-countdown');
		var iframe    = document.getElementById('wp-modal-ads-iframe');

		if (!overlay || !container || !skipBtn || !iframe) return;

		// Apply modal size.
		var pct = (modalSize >= 10 && modalSize <= 100) ? modalSize : 100;
		container.style.width  = pct + 'vw';
		container.style.height = pct + 'vh';

		// Load the iframe src.
		iframe.src = adUrl;

		// Show overlay.
		overlay.hidden = false;
		overlay.removeAttribute('hidden');
		document.body.style.overflow = 'hidden';

		// Skip-button countdown.
		if (skipDelay <= 0) {
			skipBtn.hidden = false;
			skipBtn.removeAttribute('hidden');
			if (countdown) countdown.textContent = '';
		} else {
			var remaining = skipDelay;
			if (countdown) countdown.textContent = remaining + 's ';
			skipBtn.hidden = false;
			skipBtn.removeAttribute('hidden');
			skipBtn.disabled = true;

			var timer = setInterval(function () {
				remaining--;
				if (remaining <= 0) {
					clearInterval(timer);
					skipBtn.disabled = false;
					if (countdown) countdown.textContent = '';
				} else {
					if (countdown) countdown.textContent = remaining + 's ';
				}
			}, 1000);
		}

		// Record impression.
		incrementHourlyCount();

		// Skip button handler.
		skipBtn.addEventListener('click', closeModal, { once: true });

		// Close on overlay background click (outside container).
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) closeModal();
		}, { once: true });

		// Close on Escape key.
		document.addEventListener('keydown', handleEscKey);
	}

	/**
	 * Close the modal and restore scroll.
	 */
	function closeModal() {
		var overlay = document.getElementById('wp-modal-ads-overlay');
		var iframe  = document.getElementById('wp-modal-ads-iframe');
		var skipBtn = document.getElementById('wp-modal-ads-skip');

		if (overlay) {
			overlay.hidden = true;
		}
		if (iframe) {
			iframe.src = '';
		}
		if (skipBtn) {
			skipBtn.hidden = true;
			skipBtn.disabled = false;
		}

		document.body.style.overflow = '';
		document.removeEventListener('keydown', handleEscKey);
	}

	function handleEscKey(e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			var skipBtn = document.getElementById('wp-modal-ads-skip');
			if (skipBtn && !skipBtn.disabled) {
				closeModal();
			}
		}
	}

	// ------------------------------------------------------------------
	// Trigger setup
	// ------------------------------------------------------------------

	function setupTrigger() {
		switch (trigger) {
			case 'click':
				setupClickTrigger();
				break;
			case 'scroll':
				setupScrollTrigger();
				break;
			case 'time':
				setupTimeTrigger();
				break;
			case 'exit':
				setupExitTrigger();
				break;
			default:
				setupClickTrigger();
		}
	}

	/** Show on the first click anywhere on the page. */
	function setupClickTrigger() {
		document.addEventListener('click', function onFirstClick() {
			document.removeEventListener('click', onFirstClick);
			showModal();
		}, { once: true });
	}

	/** Show once the user scrolls past a configurable % of the page height. */
	function setupScrollTrigger() {
		function onScroll() {
			var scrolled  = window.scrollY || window.pageYOffset;
			var docHeight = Math.max(
				document.body.scrollHeight, document.documentElement.scrollHeight,
				document.body.offsetHeight, document.documentElement.offsetHeight,
				document.body.clientHeight, document.documentElement.clientHeight
			);
			var viewHeight = window.innerHeight;
			var scrollable = docHeight - viewHeight;

			if (scrollable <= 0) return;

			var pct = (scrolled / scrollable) * 100;
			if (pct >= scrollPercent) {
				window.removeEventListener('scroll', onScroll);
				showModal();
			}
		}
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/** Show after a fixed number of seconds have elapsed. */
	function setupTimeTrigger() {
		var delay = timeDelay > 0 ? timeDelay * 1000 : 3000;
		setTimeout(showModal, delay);
	}

	/**
	 * Show when the user moves the cursor toward the top of the viewport
	 * (exit-intent heuristic — desktop only).
	 */
	function setupExitTrigger() {
		document.addEventListener('mouseleave', function onMouseLeave(e) {
			if (e.clientY <= 0) {
				document.removeEventListener('mouseleave', onMouseLeave);
				showModal();
			}
		});
	}

	// ------------------------------------------------------------------
	// Boot
	// ------------------------------------------------------------------

	if (!adUrl) return;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupTrigger);
	} else {
		setupTrigger();
	}
})();
