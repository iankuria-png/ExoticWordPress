(function () {
	"use strict";

	var config = window.exoticAgeGateConfig || {};
	var root = document.querySelector("[data-exotic-age-gate]");

	if (!root) {
		return;
	}

	var dialog = root.querySelector(".exotic-age-gate__dialog");
	var acceptButton = root.querySelector("[data-age-gate-accept]");
	var leaveButton = root.querySelector("[data-age-gate-leave]");
	var focusableSelector = "a[href], area[href], button:not([disabled]), input:not([disabled]):not([type='hidden']), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex='-1'])";

	function getFocusable() {
		return Array.prototype.slice.call(root.querySelectorAll(focusableSelector));
	}

	function dispatch(name) {
		document.dispatchEvent(new CustomEvent(name, {
			detail: {
				cookieName: config.cookieName || "tos18"
			}
		}));
	}

	function closeGate() {
		document.documentElement.classList.remove("exotic-age-gate-open");
		document.body.classList.remove("exotic-age-gate-open");
		root.parentNode.removeChild(root);
	}

	function setCookie(name, value, days) {
		var maxAge = Math.max(1, parseInt(days || 60, 10)) * 24 * 60 * 60;
		var cookie = name + "=" + value + "; path=/; max-age=" + maxAge + "; SameSite=Lax";
		if (window.location.protocol === "https:") {
			cookie += "; Secure";
		}
		document.cookie = cookie;
	}

	function trapFocus(event) {
		if (event.key !== "Tab") {
			return;
		}

		var focusable = getFocusable();
		if (!focusable.length) {
			event.preventDefault();
			return;
		}

		var first = focusable[0];
		var last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
			return;
		}

		if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	document.documentElement.classList.add("exotic-age-gate-open");
	document.body.classList.add("exotic-age-gate-open");

	if (acceptButton) {
		acceptButton.addEventListener("click", function () {
			setCookie(config.cookieName || "tos18", "yes", config.cookieDays || 60);
			dispatch("exoticAgeGateAccepted");
			closeGate();
		});
	}

	if (leaveButton) {
		leaveButton.addEventListener("click", function () {
			dispatch("exoticAgeGateExited");
			window.location.assign(config.exitUrl || "https://www.google.com/");
		});
	}

	root.addEventListener("keydown", trapFocus);

	window.requestAnimationFrame(function () {
		var focusable = getFocusable();
		if (focusable.length) {
			focusable[0].focus();
		} else if (dialog) {
			dialog.focus();
		}
	});
}());
