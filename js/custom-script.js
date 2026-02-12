jQuery(document).ready(function () {
	jQuery(".r-more").click(function () {
		jQuery(this).hide();
		jQuery(".top-mobile-expand p").show();
		jQuery(".top-mobile-expand .r-less").show();
		return false;
	})

	jQuery(".r-less").click(function () {
		jQuery(this).hide();
		jQuery(".top-mobile-expand p").hide();
		jQuery(".top-mobile-expand .r-more").show();
		return false;
	})



	jQuery(".mobile-menu-icon").click(function () {
		jQuery(".mobile-menu-div-content").show();
	})

	jQuery(".close-menu").click(function () {
		jQuery(".mobile-menu-div-content").hide();
	})

	jQuery(".mobile-login-icon").click(function () {
		jQuery(".mobile-login-div-content").toggle()
	})


	var w = jQuery(document).width();
	var showOverlay = function ($card) {
		$card.find(".vip-div").hide();
		$card.find(".premiumlabel").hide();
		$card.find(".model-info").hide();
		$card.find(".video-set").hide();
		$card.find(".girl-overlay").show();
	};
	var hideOverlay = function ($card) {
		$card.find(".vip-div").show();
		$card.find(".premiumlabel").show();
		$card.find(".model-info").show();
		$card.find(".video-set").show();
		$card.find(".girl-overlay").hide();
	};

	if (w > 640) {
		jQuery(".bodybox .girl .thumbwrapper").on("mouseenter", function () {
			showOverlay(jQuery(this).closest(".girl"));
		});
		jQuery(".bodybox .girl .thumbwrapper").on("mouseleave", function () {
			hideOverlay(jQuery(this).closest(".girl"));
		});
	}



	jQuery(".open-country").click(function () {
		jQuery(".mobile-menu-div-content").hide();
		jQuery(".mobile-login-div-content").hide();
		jQuery(".slidercountries").show();
		return false;
	})



	jQuery(".open-search").click(function () {
		jQuery(".mobile-menu-div-content").hide();
		jQuery(".quicksearch").show();
		return false;
	})



	jQuery(".close-country").click(function () {
		jQuery(".slidercountries").hide();
		return false;
	})



	jQuery(".close-search").click(function () {
		jQuery(".quicksearch").hide();
		return false;
	})

	// Ensure phone/tel links are clickable without triggering card overlay
	jQuery(document).on("click touchstart", ".phone-number-box, .call-now-box, .contact-btn", function (e) {
		e.stopPropagation();
	});

	// Contact button now always shows number; no reveal behavior needed

	// Location sidebar controls (desktop + overlay)
	jQuery(".location-expand").on("click", function (e) {
		e.preventDefault();
		jQuery(".sidebar-left .country-list li ul").show();
		jQuery(".sidebar-left .country-list .iconlocation.icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
	});

	jQuery(".location-collapse").on("click", function (e) {
		e.preventDefault();
		jQuery(".sidebar-left .country-list li ul").hide();
		jQuery(".sidebar-left .country-list .iconlocation.icon-angle-up")
			.removeClass("icon-angle-up")
			.addClass("icon-angle-down");

		// Keep current category path visible
		jQuery(".sidebar-left .country-list .current-cat").parentsUntil(".country-list").show();
		jQuery(".sidebar-left .country-list .current-cat > ul").show();
		jQuery(".sidebar-left .country-list .current-cat-parent > .icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
		jQuery(".sidebar-left .country-list .current-cat > .icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
	});




	jQuery(".close-online-escort").click(function () {

		jQuery(this).parent().hide();

		var dataString = 'action=set-session';



		var str_this = jQuery(this);



		jQuery.ajax({

			type: "POST",

			url: 'https://www.exoticethiopia.com/wp-content/themes/escortwp2022-child/get-online-escort.php',

			data: dataString,

			success: function (data) {

			}

		})





		return false;

	})





	jQuery(".hide-all").click(function () {

		jQuery(".fullPopup").css("display", "none");

		return false;

	})



	jQuery(".show-popup").click(function () {

		jQuery(".fullPopup").css("display", "block");

		return false;

	})



})



jQuery(window).scroll(function () {

	if (jQuery(this).scrollTop() > 150) { // this refers to window

		jQuery(".online-escort-counter-div").addClass("fixed-position");



	}



	if (jQuery(this).scrollTop() < 150) {

		jQuery(".online-escort-counter-div").removeClass("fixed-position");

	}

});









var count_escort_call = function () {



	var dataString = 'action=get-online-escrot-count';
	var url = "https://www.exoticethiopia.com?count_online_escort=yes";


	var str_this = jQuery(this);



	jQuery.ajax({

		type: "GET",

		url: url,

		success: function (data) {

			if (data != 0) {

				jQuery('.online-escort-counter-div .count').html("Chat " + data + "escort now!");

			} else {

				jQuery('.online-escort-counter-div .count').html("");

			}

		}

	})



	return false;





};



var interval = 1000 * 1 * 30;



//setInterval(count_escort_call, interval);

;

// Sidebar ads carousel (auto-advance, pause on hover/focus)
jQuery(function () {
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	jQuery('.sidebar-ad-carousel').each(function () {
		var $carousel = jQuery(this);
		var $slides = $carousel.find('.widgetadbox');
		if ($slides.length <= 1) {
			return;
		}

		var index = 0;
		var timer = null;
		var resumeTimer = null;

		var goTo = function (i) {
			var el = $carousel.get(0);
			if (!el || !el.scrollTo) {
				return;
			}
			index = (i + $slides.length) % $slides.length;
			var target = index * el.clientWidth;
			el.scrollTo({ left: target, behavior: 'smooth' });
		};

		var start = function () {
			if (timer) return;
			timer = setInterval(function () {
				goTo(index + 1);
			}, 4500);
		};

		var stop = function () {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		};

		$carousel.on('mouseenter focusin', stop);
		$carousel.on('mouseleave focusout', start);
		$carousel.on('scroll', function () {
			stop();
			if (resumeTimer) {
				clearTimeout(resumeTimer);
			}
			resumeTimer = setTimeout(start, 6000);
		});

		start();
	});
});

/* ==============================================
   MULTI-STEP REGISTRATION WIZARD
   ============================================== */
jQuery(document).ready(function ($) {
	var $form = $('#register_form');
	if (!$form.length) return;

	var $steps = $form.find('.wizard-step');
	if ($steps.length < 2) return;

	// --- State ---
	var currentStep = 0;
	var totalSteps = $steps.length;
	var $progress = $form.find('.wizard-progress');
	var $progressSteps = $progress.find('.wizard-progress__step');
	var $progressFill = $progress.find('.wizard-progress__fill');
	var $prevBtn = $form.find('.wizard-nav__btn--prev');
	var $nextBtn = $form.find('.wizard-nav__btn--next');
	var $currentLabel = $form.find('.wizard-nav__current');
	var $totalLabel = $form.find('.wizard-nav__total');
	var $announce = $('#wizard-announce');

	// --- Find first step with actual content ---
	function findFirstVisibleStep() {
		for (var i = 0; i < totalSteps; i++) {
			var $step = $steps.eq(i);
			var $fields = $step.find('input, select, textarea').not('[type="hidden"]');
			if ($fields.length > 0) return i;
		}
		return 0;
	}

	// --- Show Step ---
	function showStep(index) {
		// Hide all
		$steps.each(function () {
			$(this).attr('hidden', true).removeClass('wizard-step--active');
		});

		// Show target
		var $target = $steps.eq(index);
		$target.removeAttr('hidden').addClass('wizard-step--active');

		// Update progress dots
		$progressSteps.removeClass('wizard-progress__step--active wizard-progress__step--error');
		$progressSteps.each(function (i) {
			if (i < index) {
				$(this).addClass('wizard-progress__step--completed');
			} else if (i === index) {
				$(this).addClass('wizard-progress__step--active')
				       .removeClass('wizard-progress__step--completed');
			} else {
				$(this).removeClass('wizard-progress__step--completed');
			}
		});

		// Update fill bar
		var percent = ((index + 1) / totalSteps) * 100;
		$progressFill.css('width', percent + '%');

		// Update counter
		$currentLabel.text(index + 1);

		// Button states
		var firstStep = findFirstVisibleStep();
		$prevBtn.prop('disabled', index === firstStep);

		// On last step: hide Next (submit button is there)
		if (index === totalSteps - 1) {
			$nextBtn.hide();
		} else {
			$nextBtn.show();
		}

		// Scroll to form top
		var formTop = $form.offset().top - 80;
		if ($(window).scrollTop() > formTop) {
			$('html, body').animate({ scrollTop: formTop }, 300);
		}

		// Focus step title
		var $title = $target.find('.wizard-step__title').first();
		if ($title.length) {
			$title.attr('tabindex', '-1').focus();
		}

		// Re-init Select2 for newly visible dropdowns (only on <select>, not container spans)
		if ($(window).width() > 960) {
			$target.find('select.select2').each(function () {
				if (!$(this).hasClass('select2-hidden-accessible')) {
					try {
						$(this).select2({ minimumResultsForSearch: 20, width: 'auto', dropdownAutoWidth: true });
					} catch (e) {}
				}
			});
		}

		// Render reCAPTCHA if in this step
		if (typeof grecaptcha !== 'undefined') {
			$target.find('.g-recaptcha').each(function () {
				if (!$(this).find('iframe').length && $(this).html().trim() === '') {
					try {
						grecaptcha.render(this, { sitekey: $(this).data('sitekey') });
					} catch (e) {}
				}
			});
		}

		// Announce to screen readers
		var stepLabel = $target.attr('aria-label') || ('Step ' + (index + 1));
		$announce.text(stepLabel);

		currentStep = index;
	}

	// --- Per-Step Validation ---
	function validateStep(index) {
		var $step = $steps.eq(index);
		var isValid = true;
		var firstError = null;

		// Clear previous errors
		$step.find('.wizard-field-error').removeClass('wizard-field-error');
		$step.find('.wizard-error-msg').remove();

		// Check required fields (text, email, tel, password, etc.)
		$step.find('input[required], select[required], textarea[required]').each(function () {
			var $field = $(this);
			if (!$field.is(':visible') && $field.closest('.wizard-step').attr('hidden') !== undefined) return;
			if ($field.closest('[style*="display: none"]').length) return;

			var val = $.trim($field.val());
			if (!val || val === '-1') {
				isValid = false;
				$field.addClass('wizard-field-error');
				if (!firstError) firstError = $field;
			}
		});

		// Step-specific checks
		var stepKey = $step.data('step-key');

		if (stepKey === 'account') {
			// Username length
			var $user = $step.find('#user');
			if ($user.length && $user.is(':visible')) {
				var ulen = ($user.val() || '').length;
				if ($user.val() && (ulen < 4 || ulen > 30)) {
					isValid = false;
					$user.addClass('wizard-field-error');
					addFieldError($user, 'Username must be 4-30 characters');
					if (!firstError) firstError = $user;
				}
			}
			// Password length
			var $pass = $step.find('#pass');
			if ($pass.length && $pass.is(':visible')) {
				var plen = ($pass.val() || '').length;
				if ($pass.val() && (plen < 6 || plen > 30)) {
					isValid = false;
					$pass.addClass('wizard-field-error');
					addFieldError($pass, 'Password must be 6-30 characters');
					if (!firstError) firstError = $pass;
				}
			}
		}

		if (stepKey === 'about') {
			// Gender must be selected
			var $genderRadios = $step.find('input[name="gender"]');
			if ($genderRadios.length && !$genderRadios.is(':checked')) {
				isValid = false;
				$step.find('#gender').addClass('wizard-field-error');
				if (!firstError) firstError = $genderRadios.first();
			}
			// DOB all three selects
			var day = $step.find('#dateday').val();
			var month = $step.find('#datemonth').val();
			var year = $step.find('#dateyear').val();
			if (!day || !month || !year) {
				isValid = false;
				$step.find('.birthday').addClass('wizard-field-error');
				if (!firstError) firstError = $step.find('#dateday');
			}
		}

		if (stepKey === 'location') {
			// Country
			var $country = $step.find('[name="country"]');
			if ($country.length) {
				var cVal = $country.val();
				if (!cVal || cVal === '-1' || cVal === '0') {
					isValid = false;
					$country.addClass('wizard-field-error');
					if (!firstError) firstError = $country;
				}
			}
		}

		if (stepKey === 'services') {
			// TOS check
			var $tos = $step.find('input[name="tos_accept"]');
			if ($tos.length && !$tos.is(':checked')) {
				isValid = false;
				$step.find('.form-input-accept-tos').addClass('wizard-field-error');
				if (!firstError) firstError = $tos;
			}
		}

		if (firstError) {
			firstError.focus();
		}

		return isValid;
	}

	function addFieldError($field, message) {
		if (message && !$field.next('.wizard-error-msg').length) {
			$field.after('<span class="wizard-error-msg">' + message + '</span>');
		}
	}

	// --- Navigation ---
	$nextBtn.on('click', function () {
		if (validateStep(currentStep)) {
			if (currentStep < totalSteps - 1) {
				showStep(currentStep + 1);
			}
		}
	});

	$prevBtn.on('click', function () {
		var firstStep = findFirstVisibleStep();
		if (currentStep > firstStep) {
			showStep(currentStep - 1);
		}
	});

	// Progress dot click
	$progressSteps.on('click', function () {
		var targetIndex = $(this).index();
		// Allow clicking back to completed steps
		if (targetIndex < currentStep) {
			showStep(targetIndex);
		}
		// Allow forward one step if current validates
		else if (targetIndex === currentStep + 1) {
			if (validateStep(currentStep)) {
				showStep(targetIndex);
			}
		}
	});

	// Enter key: advance step, not submit (unless last step)
	$form.on('keydown', function (e) {
		if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit') {
			if (currentStep < totalSteps - 1) {
				e.preventDefault();
				$nextBtn.trigger('click');
			}
		}
	});

	// Intercept form submit: validate ALL steps
	$form.on('submit.wizard', function (e) {
		var allValid = true;
		var firstFailingStep = -1;

		for (var i = 0; i < totalSteps; i++) {
			if (!validateStep(i)) {
				allValid = false;
				if (firstFailingStep === -1) firstFailingStep = i;
			}
		}

		if (!allValid) {
			e.preventDefault();
			e.stopImmediatePropagation();
			showStep(firstFailingStep);
			// Mark failing steps on progress bar
			for (var j = 0; j < totalSteps; j++) {
				// Re-validate silently to mark errors
				$steps.eq(j).find('.wizard-field-error').removeClass('wizard-field-error');
				$steps.eq(j).find('.wizard-error-msg').remove();
				if (!validateStep(j)) {
					$progressSteps.eq(j).addClass('wizard-progress__step--error');
				}
			}
			return false;
		}
		// If all valid, existing submit handler (TOS, double-submit) runs
	});

	// --- Draft Persistence (sessionStorage) ---
	var STORAGE_KEY = 'escortwp_reg_draft';

	function saveDraft() {
		try {
			var data = {};
			$form.find('input, select, textarea').each(function () {
				var $el = $(this);
				var name = $el.attr('name');
				if (!name || name === 'action' || name === 'pass' || name === 'escort_post_id' || name === 'agencyid') return;
				if ($el.attr('type') === 'hidden') return;

				if ($el.is(':checkbox') || $el.is(':radio')) {
					if ($el.is(':checked')) {
						if (!data[name]) data[name] = [];
						data[name].push($el.val());
					}
				} else {
					data[name] = $el.val();
				}
			});
			data['__step'] = currentStep;
			sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
		} catch (ex) { /* quota or disabled */ }
	}

	function restoreDraft() {
		try {
			var raw = sessionStorage.getItem(STORAGE_KEY);
			if (!raw) return;
			var data = JSON.parse(raw);

			// Only restore for new registrations (no edit mode)
			if ($form.find('[name="escort_post_id"]').length) return;
			if ($form.find('[name="agencyid"]').length) return;

			Object.keys(data).forEach(function (name) {
				if (name === '__step' || name === 'action') return;
				var $fields = $form.find('[name="' + name + '"]');
				if (!$fields.length) return;

				var val = data[name];
				if ($fields.first().is(':checkbox') || $fields.first().is(':radio')) {
					if (!Array.isArray(val)) val = [val];
					$fields.each(function () {
						$(this).prop('checked', val.indexOf($(this).val()) !== -1);
					});
				} else {
					$fields.val(val);
				}
			});

			// Restore step
			if (data['__step'] !== undefined) {
				var savedStep = parseInt(data['__step'], 10);
				if (savedStep >= 0 && savedStep < totalSteps) {
					currentStep = savedStep;
				}
			}
		} catch (ex) { /* corrupt data */ }
	}

	// Save on field changes
	$form.on('change input', 'input, select, textarea', function () {
		saveDraft();
	});

	// Clear draft on submit
	$form.on('submit', function () {
		try { sessionStorage.removeItem(STORAGE_KEY); } catch (ex) {}
	});

	// --- Server Error Handling ---
	// If form returned with errors, detect and show first step
	if ($form.data('has-errors') === 1 || $form.data('has-errors') === '1') {
		currentStep = findFirstVisibleStep();
	}

	// --- Init ---
	restoreDraft();
	$totalLabel.text(totalSteps);
	showStep(currentStep);

	// Trigger availability check since the incall/outcall may be in a hidden step
	if (typeof check_availability === 'function') {
		check_availability();
	}
});

