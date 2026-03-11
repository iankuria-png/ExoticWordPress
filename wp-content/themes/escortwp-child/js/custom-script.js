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



		var $body = jQuery("body");
		var $mobileMenu = jQuery(".mobile-menu-div-content");
		var $mobileMenuToggle = jQuery(".mobile-menu-icon");
		var $accountOverlay = jQuery(".mobile-login-div-content");
		var $accountPanel = $accountOverlay.find(".mobile-account-panel");
			var $accountToggle = jQuery(".mobile-login-icon");
			var $locationsPanel = jQuery(".slidercountries");
			var $mobileLocationSearch = $locationsPanel.find("#location-search-mobile");
			var geoConfig = window.escortwpGeoLocation || {};
			var geoCopy = geoConfig.copy || {};
			var geoSessionKey = geoConfig.sessionKey || "escortwp_geo_location_term";
			var $geoButtons = jQuery("[data-use-current-location]");
			var $geoStatus = jQuery("[data-geo-status]");
			var focusableSelector = "a[href], area[href], input:not([disabled]):not([type=\"hidden\"]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex=\"-1\"])";
			var lastAccountTrigger = null;
		var isSmallViewport = function () {
			if (window.matchMedia) {
				return window.matchMedia("(max-width: 640px)").matches;
			}
			return jQuery(window).width() <= 640;
		};

		var enhanceMobileLocationList = function () {
			if (!isSmallViewport() || !$locationsPanel.length) {
				return;
			}

			var $mobileList = $locationsPanel.find("#mobile-location-list");
			if (!$mobileList.length) {
				return;
			}

			$mobileList.children("li").each(function () {
				var $item = jQuery(this);
				var $itemLink = $item.children("a").first();
				var $childrenList = $item.children("ul.children").first();
				var childCount = $childrenList.length ? $childrenList.children("li").length : 0;
				var $icon = $item.children(".iconlocation").first();
				var $countBadge = $item.children(".location-item__count");
				var itemLabel = jQuery.trim($itemLink.text());

				$item.addClass("location-item");
				$itemLink.addClass("location-item__link");
				$item.toggleClass("location-item--has-children", childCount > 0);

				if (childCount > 0) {
					if (!$countBadge.length) {
						$countBadge = jQuery("<span/>", {
							"class": "location-item__count",
							"aria-hidden": "true"
						});
						if ($icon.length) {
							$countBadge.insertBefore($icon);
						} else {
							$item.append($countBadge);
						}
					}
					$countBadge.text(childCount);

					if ($icon.length) {
						$icon.attr({
							role: "button",
							tabindex: "0",
							"aria-label": "Toggle " + itemLabel + " cities",
							"aria-expanded": $childrenList.is(":visible") ? "true" : "false"
						});
						if ($childrenList.length) {
							$icon.insertBefore($childrenList);
						} else {
							$item.append($icon);
						}
					}
				} else {
					$countBadge.remove();
					if ($icon.length) {
						$icon.removeAttr("role tabindex aria-label aria-expanded");
					}
				}
			});
		};

		var getFocusable = function ($container) {
			return $container.find(focusableSelector).filter(":visible");
		};

		var focusAccountPanel = function () {
			var $focusables = getFocusable($accountOverlay);
			if ($focusables.length) {
				$focusables.first().trigger("focus");
				return;
			}
			$accountPanel.trigger("focus");
		};

		var trapFocus = function ($container, event) {
			var $focusables = getFocusable($container);
			if (!$focusables.length) {
				return;
			}

			var firstEl = $focusables.get(0);
			var lastEl = $focusables.get($focusables.length - 1);

			if (event.shiftKey && document.activeElement === firstEl) {
				event.preventDefault();
				jQuery(lastEl).trigger("focus");
			} else if (!event.shiftKey && document.activeElement === lastEl) {
				event.preventDefault();
				jQuery(firstEl).trigger("focus");
			}
		};

			var syncOverlayState = function () {
				var hasOverlay = $body.hasClass("mobile-nav-open") || $body.hasClass("mobile-account-open") || $body.hasClass("locations-open");
				$body.toggleClass("ui-overlay-open", hasOverlay);
			};

			var setGeoStatus = function (message, state) {
				if (!$geoStatus.length) {
					return;
				}
				$geoStatus.text(message || "").attr("data-state", state || "");
				$geoStatus.prop("hidden", !message);
			};

			var setGeoButtonsLoading = function (isLoading) {
				$geoButtons.each(function () {
					var $btn = jQuery(this);
					var $label = $btn.find("span").last();
					var idleLabel = $btn.attr("data-idle-label") || geoCopy.idle || $label.text();
					if (!$btn.attr("data-idle-label")) {
						$btn.attr("data-idle-label", idleLabel);
					}
					$btn.toggleClass("is-loading", !!isLoading).prop("disabled", !!isLoading);
					$label.text(isLoading ? (geoCopy.loading || idleLabel) : idleLabel);
				});
			};

			var isGeoSecureContext = function () {
				var hostname = window.location.hostname || "";
				var isLocalhost = hostname === "localhost" ||
					hostname === "127.0.0.1" ||
					hostname === "::1" ||
					/\.localhost$/i.test(hostname);
				return !!window.isSecureContext || window.location.protocol === "https:" || isLocalhost;
			};

			var storeGeoLocation = function (payload) {
				if (!window.sessionStorage || !payload || !payload.term_id) {
					return;
				}
				try {
					window.sessionStorage.setItem(geoSessionKey, JSON.stringify({
						term_id: payload.term_id,
						term_name: payload.term_name || "",
						taxonomy: payload.taxonomy || geoConfig.locationTaxonomy || "escorts-from",
						archive_url: payload.archive_url || ""
					}));
				} catch (err) { }
			};

			var applyGeoLocation = function (payload) {
				if (!payload || !payload.term_id) {
					return;
				}

				storeGeoLocation(payload);

				if (geoConfig.isFrontPage && typeof window.escortwpApplyLocationContext === "function") {
					window.escortwpApplyLocationContext(payload);
					if (payload.term_name) {
						setGeoStatus((geoCopy.applied || "Showing escorts near %s").replace("%s", payload.term_name), "success");
					}
					closeLocationsPanel();
					return;
				}

				if (payload.archive_url) {
					window.location.assign(payload.archive_url);
				}
			};

		var closeMobileMenu = function () {
			$body.removeClass("mobile-nav-open");
			$mobileMenuToggle.attr("aria-expanded", "false");
			$mobileMenu.attr("aria-hidden", "true");
			syncOverlayState();
		};

		var closeAccountPanel = function (restoreFocus) {
			if (typeof restoreFocus === "undefined") {
				restoreFocus = true;
			}

			$accountOverlay.hide().attr("aria-hidden", "true").removeAttr("aria-modal");
			$accountToggle.attr("aria-expanded", "false");
			$body.removeClass("mobile-account-open");
			syncOverlayState();

			if (restoreFocus && lastAccountTrigger) {
				jQuery(lastAccountTrigger).trigger("focus");
			}
			lastAccountTrigger = null;
		};


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



		var closeLocationsPanel = function () {
			$locationsPanel.hide().attr("aria-hidden", "true");
			$body.removeClass("locations-open");
			syncOverlayState();
		};

		var openMobileMenu = function () {
			closeAccountPanel(false);
			closeLocationsPanel();
			$body.addClass("mobile-nav-open");
			$mobileMenuToggle.attr("aria-expanded", "true");
			$mobileMenu.attr("aria-hidden", "false");
			syncOverlayState();
		};

		var openAccountPanel = function () {
			lastAccountTrigger = document.activeElement;
			closeMobileMenu();
			closeLocationsPanel();
			$accountOverlay.show().attr("aria-hidden", "false").attr("aria-modal", "true");
			$accountToggle.attr("aria-expanded", "true");
			$body.addClass("mobile-account-open");
			syncOverlayState();
			window.setTimeout(focusAccountPanel, 0);
		};

		var openLocationsPanel = function () {
			closeMobileMenu();
			closeAccountPanel(false);
			$locationsPanel.css("display", "flex").attr("aria-hidden", "false");
			$body.addClass("locations-open");
			syncOverlayState();
			enhanceMobileLocationList();
			window.setTimeout(function () {
				if ($mobileLocationSearch.length) {
					$mobileLocationSearch.trigger("focus");
				}
			}, 40);
		};

		jQuery(".mobile-menu-icon").click(function (e) {
				e.preventDefault();
				if ($body.hasClass("mobile-nav-open")) {
					closeMobileMenu();
				} else {
					openMobileMenu();
				}
			});

		jQuery(".close-menu").click(function (e) {
				e.preventDefault();
				closeMobileMenu();
			});

		jQuery(".mobile-login-icon").click(function (e) {
				e.preventDefault();
				if ($body.hasClass("mobile-account-open")) {
					closeAccountPanel();
				} else {
					openAccountPanel();
				}
			});

		jQuery(".mobile-account-panel__close").click(function (e) {
			e.preventDefault();
			closeAccountPanel();
		});

		jQuery(".open-country").click(function (e) {
			e.preventDefault();
			openLocationsPanel();
		});

		jQuery(".open-search").click(function (e) {
			e.preventDefault();
			closeMobileMenu();
			closeAccountPanel(false);
			closeLocationsPanel();
			jQuery(".quicksearch").show();
		});



		jQuery(".close-country").click(function (e) {
			e.preventDefault();
			closeLocationsPanel();
		});

		jQuery(document).on("keydown", ".slidercountries #mobile-location-list > li > .iconlocation", function (e) {
			if (e.key === "Enter" || e.key === " ") {
				e.preventDefault();
				jQuery(this).trigger("click");
			}
		});

		jQuery(document).on("click", ".slidercountries #mobile-location-list > li > .iconlocation", function () {
			var $toggle = jQuery(this);
			window.setTimeout(function () {
				var expanded = $toggle.closest("li").children("ul.children").first().is(":visible");
				$toggle.attr("aria-expanded", expanded ? "true" : "false");
			}, 0);
		});



		jQuery(".close-search").click(function (e) {
			e.preventDefault();
			jQuery(".quicksearch").hide();
		});

		jQuery(document).on("click", ".mobile-menu-div-content a:not(.open-country)", function () {
			closeMobileMenu();
		});

		jQuery(document).on("click", function (e) {
			var $target = jQuery(e.target);

			if ($body.hasClass("mobile-nav-open") &&
				!$target.closest(".mobile-menu-div-content, .mobile-menu-icon").length) {
				closeMobileMenu();
			}

			if ($body.hasClass("mobile-account-open") &&
				!$target.closest(".mobile-login-div-content, .mobile-login-icon").length) {
				closeAccountPanel(false);
			}

			if ($body.hasClass("locations-open") &&
				!$target.closest(".slidercountries, .open-country").length) {
				closeLocationsPanel();
			}
		});

		jQuery(document).on("keydown", function (e) {
			if (e.key === "Tab" && $body.hasClass("mobile-account-open")) {
				trapFocus($accountOverlay, e);
			}

			if (e.key === "Escape") {
				closeMobileMenu();
				closeAccountPanel();
				closeLocationsPanel();
				jQuery(".quicksearch").hide();
			}
		});

		jQuery(window).on("resize", function () {
			if (jQuery(window).width() > 640) {
				closeMobileMenu();
				closeAccountPanel(false);
				closeLocationsPanel();
				jQuery(".quicksearch").hide();
			} else {
				enhanceMobileLocationList();
			}
		});

		enhanceMobileLocationList();
		syncOverlayState();

	// Ensure phone/tel links are clickable without triggering card overlay
	jQuery(document).on("click touchstart", ".phone-number-box, .call-now-box, .contact-btn", function (e) {
		e.stopPropagation();
	});

	// Contact button now always shows number; no reveal behavior needed

			jQuery(document).on("input", ".js-location-filter", function () {
			var query = jQuery.trim(jQuery(this).val()).toLowerCase();
			var targetSelector = jQuery(this).data("target");
			var $list = targetSelector ? jQuery(targetSelector) : jQuery();

			if (!$list.length) {
				return;
			}

			var $parentItem = jQuery(this).closest(".countries");
			var $emptyState = $parentItem.find("[data-location-empty]");
			var visibleCount = 0;

			$list.children("li").each(function () {
				var $item = jQuery(this);
				var itemText = jQuery.trim($item.text()).toLowerCase();
				var isMatch = !query || itemText.indexOf(query) !== -1;

				$item.toggle(isMatch);
				if (isMatch) {
					visibleCount++;
				}
			});

				if ($emptyState.length) {
					$emptyState.prop("hidden", visibleCount > 0);
				}
			});

				jQuery(document).on("click", "[data-use-current-location]", function (e) {
					e.preventDefault();
					if (!geoConfig.ajaxUrl || !geoConfig.nonce) {
						setGeoStatus(geoCopy.networkError || "We could not resolve your location right now. Try again in a moment.", "error");
						return;
					}

					if (!isGeoSecureContext()) {
						setGeoStatus(geoCopy.insecure || "Location needs HTTPS or localhost. This local HTTP URL cannot request it.", "error");
						return;
					}

					if (!navigator.geolocation) {
						setGeoStatus(geoCopy.unsupported || "Location is not supported on this browser.", "error");
						return;
				}

				setGeoStatus("", "");
				setGeoButtonsLoading(true);

				navigator.geolocation.getCurrentPosition(function (position) {
					jQuery.ajax({
						type: "POST",
						url: geoConfig.ajaxUrl,
						dataType: "json",
						data: {
							action: "escortwp_resolve_location_term",
							lat: position.coords.latitude,
							lng: position.coords.longitude,
							nonce: geoConfig.nonce
						}
					}).done(function (response) {
						var data = response && response.data ? response.data : {};
						if (!response || !response.success || !data.term_id) {
							setGeoStatus(geoCopy.noMatch || "We could not match your location yet. Choose a county or city manually.", "error");
							return;
						}
						applyGeoLocation(data);
					}).fail(function () {
						setGeoStatus(geoCopy.networkError || "We could not resolve your location right now. Try again in a moment.", "error");
					}).always(function () {
						setGeoButtonsLoading(false);
					});
					}, function (error) {
						var denied = error && error.code === 1;
						setGeoButtonsLoading(false);
						setGeoStatus(
							!isGeoSecureContext()
								? (geoCopy.insecure || "Location needs HTTPS or localhost. This local HTTP URL cannot request it.")
								: denied
								? (geoCopy.denied || "Location access was denied. Choose a county or city manually.")
								: (geoCopy.networkError || "We could not resolve your location right now. Try again in a moment."),
							"error"
						);
				}, {
					enableHighAccuracy: false,
					timeout: 10000,
						maximumAge: 300000
					});
				});

				jQuery(document).on("click", "[data-location-link]", function (e) {
					var isFrontPage = parseInt(geoConfig.isFrontPage, 10) === 1;
					var $link = jQuery(this);
					var archiveUrl = ($link.attr("data-location-archive-url") || $link.attr("href") || "").toString();
					if (!isFrontPage) {
						if (archiveUrl) {
							e.preventDefault();
							if ($body.hasClass("locations-open")) {
								closeLocationsPanel();
							}
							window.setTimeout(function () {
								window.location.assign(archiveUrl);
							}, 40);
						}
						return;
					}

					var termId = parseInt($link.attr("data-location-term-id"), 10);
					var taxonomy = ($link.attr("data-location-taxonomy") || "").toString();
					if (!termId || !taxonomy || typeof window.escortwpApplyLocationContext !== "function") {
						return;
					}

					e.preventDefault();
					setGeoStatus("", "");

					window.escortwpApplyLocationContext({
						term_id: termId,
						term_name: ($link.attr("data-location-name") || jQuery.trim($link.text()) || "").toString(),
						taxonomy: taxonomy,
						archive_url: archiveUrl
					});

					if ($body.hasClass("locations-open")) {
						closeLocationsPanel();
						if ($mobileLocationSearch.length) {
							$mobileLocationSearch.val("");
						}
					}
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



	// Footer: mobile accordions for widget columns (scoped to .site-footer)
	jQuery(function () {
		var $footer = jQuery('.site-footer');
		if (!$footer.length) return;

		var mq = window.matchMedia ? window.matchMedia('(max-width: 768px)') : null;
		var isMobile = function () {
			if (mq) return mq.matches;
			return jQuery(window).width() <= 768;
		};

			var $widgets = $footer.find('.site-footer__widgets > .widgetbox');
			$widgets.each(function (index) {
				var $widget = jQuery(this);
				var $title = $widget.children('.widgettitle').first();
				var titleText = $title.length ? jQuery.trim($title.text()) : '';
				var widgetText = titleText || jQuery.trim($widget.text());
				var normalizedTitle = widgetText.toLowerCase();
				if (normalizedTitle.indexOf('follow us on social') !== -1 || (normalizedTitle.indexOf('follow') !== -1 && normalizedTitle.indexOf('social') !== -1)) {
					$widget.addClass('site-footer__widget--social');
				}
				if (!$title.length) return;

				// Create/move panel wrapper once
				var $panel = $widget.children('.footer-accordion__panel').first();
				if (!$panel.length) {
					$panel = jQuery('<div class="footer-accordion__panel"></div>');
					var $content = $title.nextAll();
				$panel.append($content);
				$title.after($panel);
			}

			// Ensure panel has an ID for aria-controls
			if (!$panel.attr('id')) {
				var panelIdBase = ($widget.attr('id') ? $widget.attr('id') : 'footer-widget-' + index) + '-panel';
				var panelId = panelIdBase;
				if (document.getElementById(panelId)) {
					panelId = panelIdBase + '-' + (index + 1);
				}
				$panel.attr('id', panelId);
			}

				// Convert title into a button trigger once
				var $btn = $title.find('button.footer-accordion__trigger').first();
				if (!$btn.length) {
					$title.empty();

					$btn = jQuery('<button type="button" class="footer-accordion__trigger" aria-expanded="false"></button>');
					$btn.append(jQuery('<span class="footer-accordion__label"></span>').text(titleText));
					$btn.append('<span class="footer-accordion__icon" aria-hidden="true"></span>');
					$btn.attr('aria-controls', $panel.attr('id'));

					$title.append($btn);
				} else if (!$btn.attr('aria-controls')) {
				$btn.attr('aria-controls', $panel.attr('id'));
			}

			// Bind click once
			if (!$btn.data('footer-accordion-bound')) {
				$btn.data('footer-accordion-bound', true);
				$btn.on('click', function () {
					if (!isMobile()) return;
					var expanded = $btn.attr('aria-expanded') === 'true';
					$btn.attr('aria-expanded', expanded ? 'false' : 'true');
					$panel.prop('hidden', expanded);
				});
			}
		});

		// Apply default state (collapsed on mobile, expanded on desktop)
		var applyState = function () {
			var mobile = isMobile();
			$widgets.each(function () {
				var $widget = jQuery(this);
				var $btn = $widget.find('button.footer-accordion__trigger').first();
				var $panel = $widget.children('.footer-accordion__panel').first();
				if (!$btn.length || !$panel.length) return;

				if (mobile) {
					$btn.attr('aria-expanded', 'false');
					$panel.prop('hidden', true);
				} else {
					$btn.attr('aria-expanded', 'true');
					$panel.prop('hidden', false);
				}
			});
		};

		applyState();

		if (mq) {
			if (mq.addEventListener) {
				mq.addEventListener('change', applyState);
			} else if (mq.addListener) {
				mq.addListener(applyState);
			}
		}
	});

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

(function (window) {
	window.escortwpPushEvent = window.escortwpPushEvent || function (eventName, payload) {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(Object.assign({ event: eventName }, payload || {}));
	};
}(window));

// Graceful image fallback for card/story media (initial + AJAX-injected content)
jQuery(function ($) {
	function applyFallbackImage($img) {
		if (!$img || !$img.length) return;
		var fallbackSrc = ($img.attr('data-fallback-src') || '').toString();
		if (!fallbackSrc) return;
		if ($img.attr('src') === fallbackSrc) return;

		$img.attr('src', fallbackSrc);
		$img.attr('srcset', '');
		$img.removeAttr('sizes');
		$img.addClass('is-fallback');
		$img.closest('.escort-card__media, .online-story__avatar').addClass('has-fallback-image');
	}

	function bindFallbacks(scope) {
		var $scope = scope ? $(scope) : $(document);
		$scope.find('img[data-fallback-src]').each(function () {
			var $img = $(this);
			var currentSrc = ($img.attr('src') || '').trim();

			$img.off('error.escortwpFallback').on('error.escortwpFallback', function () {
				applyFallbackImage($(this));
			});

			if (!currentSrc || currentSrc === window.location.href || /\/wp-content-\d*x?\d*$/i.test(currentSrc)) {
				applyFallbackImage($img);
			}
		});
	}

	window.escortwpApplyImageFallbacks = bindFallbacks;
	bindFallbacks(document);
});

// Homepage filter chips (VIP + Premium + Newly Added)
jQuery(function ($) {
	var $chips = $('[data-filter-controls]');
	if (!$chips.length) {
		return;
	}

	var config = window.escortwpFilterChips || {};
	var ajaxUrl = config.ajaxUrl || '';
	var nonce = config.nonce || '';
	var filterCopy = config.copy || {};
	var emptyText = config.emptyText || 'No matches for this filter.';
	var pageTemplate = config.pageTemplate || 'front-page';
	var activeRequest = null;
	var $summary = $chips.closest('.filter-chips-section').find('[data-filter-summary]').first();
	var $filterEmptyState = $chips.closest('.filter-chips-section').find('[data-filter-empty-state]').first();
	var $filterSections = $('.escort-grid__container[data-grid]').closest('section.bodybox-homepage');
	var $onlineStoriesSection = $('[data-online-stories-section]').first();
	var $onlineViewAll = $onlineStoriesSection.find('[data-online-view-all]').first();
	var geoSessionKey = (window.escortwpGeoLocation && window.escortwpGeoLocation.sessionKey) || 'escortwp_geo_location_term';

	if (!ajaxUrl || !nonce) {
		return;
	}

	function pushEvent(eventName, payload) {
		window.escortwpPushEvent(eventName, payload || {});
	}

	function isSmallFilterViewport() {
		if (window.matchMedia) {
			return window.matchMedia('(max-width: 640px)').matches;
		}
		return $(window).width() <= 640;
	}

	function setActiveChip($btn) {
		$chips.find('.filter-chip').removeClass('is-active').attr('aria-pressed', 'false');
		$btn.addClass('is-active').attr('aria-pressed', 'true');
		syncOnlineStoriesAction();
	}

	function getChipLabel($btn) {
		if (!$btn || !$btn.length) {
			return 'All';
		}
		return ($btn.data('filter-label') || $.trim($btn.text()) || 'All').toString();
	}

	function getLocationContext() {
		var termId = parseInt($chips.attr('data-term-id'), 10);
		return {
			termId: isNaN(termId) ? 0 : termId,
			taxonomy: ($chips.attr('data-taxonomy') || '').toString(),
			termName: ($chips.attr('data-term-name') || '').toString(),
			archiveUrl: ($chips.attr('data-archive-url') || '').toString()
		};
	}

	function setLocationContext(contextOrTermId, taxonomy, termName, archiveUrl) {
		var context = contextOrTermId;
		if (typeof contextOrTermId !== 'object' || contextOrTermId === null) {
			context = {
				term_id: contextOrTermId,
				taxonomy: taxonomy,
				term_name: termName,
				archive_url: archiveUrl
			};
		}

		var termId = parseInt(context.term_id || context.termId, 10);
		var resolvedTaxonomy = (context.taxonomy || '').toString();
		var resolvedTermName = (context.term_name || context.termName || '').toString();
		var resolvedArchiveUrl = (context.archive_url || context.archiveUrl || '').toString();

		if (!termId || !resolvedTaxonomy) {
			$chips.removeAttr('data-term-id data-taxonomy data-term-name data-archive-url');
			return;
		}

		$chips.attr('data-term-id', termId);
		$chips.attr('data-taxonomy', resolvedTaxonomy);
		$chips.attr('data-term-name', resolvedTermName);
		$chips.attr('data-archive-url', resolvedArchiveUrl);
	}

	function persistLocationContext(context) {
		var termId = parseInt(context && (context.term_id || context.termId), 10);
		var taxonomy = context && context.taxonomy ? context.taxonomy.toString() : '';
		if (!termId || !taxonomy || !window.sessionStorage) {
			return;
		}

		try {
			window.sessionStorage.setItem(geoSessionKey, JSON.stringify({
				term_id: termId,
				term_name: (context.term_name || context.termName || '').toString(),
				taxonomy: taxonomy,
				archive_url: (context.archive_url || context.archiveUrl || '').toString()
			}));
		} catch (err) { }
	}

	function clearPersistedLocationContext() {
		setLocationContext(null);
		try {
			if (window.sessionStorage) {
				window.sessionStorage.removeItem(geoSessionKey);
			}
		} catch (err) { }
	}

	function updateSectionTitles($btn, options) {
		var label = getChipLabel($btn);
		var filterType = ($btn && $btn.length) ? ($btn.data('filter-type') || 'all') : 'all';
		var onlineFallback = options && options.onlineFallback;
		var locationContext = getLocationContext();
		var locationName = options && typeof options.locationName !== 'undefined'
			? $.trim((options.locationName || '').toString())
			: $.trim(locationContext.termName);

		var withLocationPrefix = function (title) {
			return locationName ? $.trim(locationName + ' ' + title) : title;
		};

		var getHeadingTitle = function ($heading, attrName, fallback) {
			var attrValue = ($heading.attr(attrName) || '').toString();
			return attrValue ? attrValue : fallback;
		};

		$('.section-heading[data-default-title]').each(function () {
			var $heading = $(this);
			var baseTitle = ($heading.attr('data-default-title') || $heading.text() || '').toString();
			var locationTitle = ($heading.attr('data-location-title') || baseTitle).toString();
			var resolvedTitle = baseTitle;
			if (filterType === 'all' || !label) {
				resolvedTitle = locationName ? withLocationPrefix(locationTitle) : baseTitle;
			} else if (filterType === 'online') {
				var onlineTitle = onlineFallback
					? getHeadingTitle($heading, 'data-online-fallback-title', getHeadingTitle($heading, 'data-online-title', 'Recently Active'))
					: getHeadingTitle($heading, 'data-online-title', 'Online Now');
				resolvedTitle = withLocationPrefix(onlineTitle);
			} else if (filterType === 'recent_24h') {
				resolvedTitle = withLocationPrefix(getHeadingTitle($heading, 'data-recent-title', 'Past 24 Hours'));
			} else if (filterType === 'new') {
				resolvedTitle = withLocationPrefix('Newly Added');
			} else {
				resolvedTitle = withLocationPrefix(label + ' Escorts');
			}

			resolvedTitle = $.trim(resolvedTitle);
			if ($.trim($heading.text()) !== resolvedTitle) {
				$heading.text(resolvedTitle);
				$heading.removeClass('is-refreshing');
				if ($heading.length && $heading.get(0)) {
					void $heading.get(0).offsetWidth;
				}
				$heading.addClass('is-refreshing');
				window.setTimeout(function () {
					$heading.removeClass('is-refreshing');
				}, 260);
				return;
			}
			$heading.text(resolvedTitle);
		});
	}

	function syncOnlineStoriesAction() {
		if (!$onlineViewAll.length) {
			return;
		}
		var $activeChip = $chips.find('.filter-chip.is-active').first();
		var isOnlineActive = (($activeChip.data('filter-type') || 'all') === 'online');
		var idleLabel = ($onlineViewAll.attr('data-idle-label') || 'View all').toString();
		var activeLabel = ($onlineViewAll.attr('data-active-label') || 'Viewing all').toString();
		$onlineViewAll.toggleClass('is-active', isOnlineActive);
		$onlineStoriesSection.toggleClass('is-online-filtered', isOnlineActive);
		$onlineViewAll.find('[data-online-view-all-label]').text(isOnlineActive ? activeLabel : idleLabel);
	}

	function spotlightResultsSummary() {
		if (!$summary.length) {
			return;
		}
		$summary.removeClass('is-spotlighted');
		if ($summary.get(0)) {
			void $summary.get(0).offsetWidth;
		}
		$summary.addClass('is-spotlighted');
		window.setTimeout(function () {
			$summary.removeClass('is-spotlighted');
		}, 520);
	}

	function scrollToFilterResults() {
		var target = $summary.length ? $summary.get(0) : $('.filter-chips-section').get(0);
		if (!target || typeof target.scrollIntoView !== 'function') {
			return;
		}
		target.scrollIntoView({ behavior: 'smooth', block: 'start' });
		window.setTimeout(spotlightResultsSummary, 180);
	}

	function refreshLastActive(options) {
		var $active = $chips.find('.filter-chip.is-active');
		var filterType = $active.data('filter-type') || 'all';
		var show = filterType === 'online' || filterType === 'recent_24h';
		var now = Math.floor(Date.now() / 1000);
		var maxMinutes = (options && options.maxMinutes) ? options.maxMinutes : 1440;

		$('.escort-card__last-active').each(function () {
			var $el = $(this);
			if (!show) {
				$el.text('');
				return;
			}
			var ts = parseInt($el.data('last-online'), 10);
			if (!ts) {
				$el.text('');
				return;
			}
			var diffMin = Math.floor((now - ts) / 60);
			if (diffMin < 1) diffMin = 1;
			if (diffMin > maxMinutes) {
				$el.text('');
				return;
			}
			if (diffMin < 60) {
				$el.text('Active ' + diffMin + ' min ago');
			} else {
				var hrs = Math.floor(diffMin / 60);
				$el.text('Active ' + hrs + ' hr ago');
			}
		});
	}

		function updateSummary(summaryData, $btn) {
			if (!$summary.length) {
				return;
			}

		var totalResults = summaryData && typeof summaryData.total_results !== 'undefined'
			? parseInt(summaryData.total_results, 10)
			: 0;
		if (isNaN(totalResults) || totalResults < 0) {
			totalResults = 0;
		}

			var label = summaryData && summaryData.active_filter_label
				? summaryData.active_filter_label
				: getChipLabel($btn);
			var contextLabel = summaryData && summaryData.active_context_label
				? summaryData.active_context_label
				: label;
			var context = summaryData && summaryData.active_filter_type
				? summaryData.active_filter_type
				: (($btn && $btn.length) ? ($btn.data('filter-type') || 'all') : 'all');

			$summary.attr('data-total-results', totalResults);
			$summary.attr('data-filter-context', context);
			$summary.attr('data-filter-label', label);
			$summary.attr('data-context-label', contextLabel);
			$summary.find('[data-summary-count]').text(totalResults);
			$summary.find('[data-summary-label]').text(contextLabel);
		}

	function computeInitialSummary() {
		var count = 0;
		$('.escort-grid__container[data-grid]').each(function () {
			count += $(this).find('.escort-card').not('.escort-card--skeleton').length;
		});
		return count;
	}

	function emptyMarkup() {
		return '<div class="escort-grid__empty">' + emptyText + '</div>';
	}

	function shouldShowFilterEmptyState(filterType, totalResults) {
		return totalResults === 0 && (filterType === 'online' || filterType === 'recent_24h');
	}

	function hideFilterEmptyState() {
		if ($filterEmptyState.length) {
			$filterEmptyState.prop('hidden', true).empty();
		}
		$filterSections.prop('hidden', false).removeClass('is-filter-empty-hidden');
	}

	function renderFilterEmptyState(filterType) {
		var isPastDay = filterType === 'recent_24h';
		var title = isPastDay
			? (filterCopy.recentEmptyTitle || 'Still a little quiet')
			: (filterCopy.onlineEmptyTitle || 'Quiet right now');
		var body = isPastDay
			? (filterCopy.recentEmptyBody || 'No escorts have checked in over the past 24 hours. Try a nearby match instead.')
			: (filterCopy.onlineEmptyBody || 'No escorts are live at the moment. Want a wider pulse check or someone nearby?');
		var primaryLabel = isPastDay
			? (filterCopy.liveNowCta || 'Live now only')
			: (filterCopy.recent24Cta || 'Past 24 Hours');

		return '' +
			'<div class="filter-empty-state__panel">' +
				'<div class="filter-empty-state__icon" aria-hidden="true">' +
					'<span class="filter-empty-state__icon-ring"></span>' +
					'<span class="filter-empty-state__icon-dot"></span>' +
				'</div>' +
				'<div class="filter-empty-state__copy">' +
					'<p class="filter-empty-state__eyebrow">Online update</p>' +
					'<h3 class="filter-empty-state__title">' + title + '</h3>' +
					'<p class="filter-empty-state__text">' + body + '</p>' +
				'</div>' +
				'<div class="filter-empty-state__actions">' +
					'<button type="button" class="filter-empty-state__action filter-empty-state__action--primary" data-empty-filter="' + (isPastDay ? 'online' : 'recent_24h') + '">' + primaryLabel + '</button>' +
					'<button type="button" class="filter-empty-state__action filter-empty-state__action--secondary" data-empty-nearby="1">' + (filterCopy.nearbyCta || 'Escorts Nearby') + '</button>' +
				'</div>' +
				'<p class="filter-empty-state__hint">' + (filterCopy.nearbyHint || 'Use your location or pick a city to keep browsing.') + '</p>' +
			'</div>';
	}

	function showFilterEmptyState(filterType) {
		if (!$filterEmptyState.length) {
			return;
		}
		$filterSections.prop('hidden', true).addClass('is-filter-empty-hidden');
		$filterEmptyState.html(renderFilterEmptyState(filterType)).prop('hidden', false);
	}

	function skeletonMarkup(count) {
		var total = parseInt(count, 10);
		if (!total || total < 1) total = 6;
		var html = '';
		for (var i = 0; i < total; i++) {
			html += '<div class="escort-card escort-card--skeleton">' +
				'<div class="escort-card__skeleton-media"></div>' +
				'<div class="escort-card__skeleton-body">' +
				'<div class="escort-card__skeleton-line line-1"></div>' +
				'<div class="escort-card__skeleton-line line-2"></div>' +
				'<div class="escort-card__skeleton-pill"></div>' +
				'</div>' +
				'</div>';
		}
		return html;
	}

		function updateGrid(selector, html) {
			var $grid = $(selector);
			if (!$grid.length) return;
			$grid.html(html && html.trim() ? html : emptyMarkup());
		}

		function applyLocationContext(context) {
			if (!context) {
				return;
			}
			var taxonomy = context.taxonomy || '';
			var termId = parseInt(context.term_id || context.termId, 10);
			if (!taxonomy || !termId) {
				return;
			}

			setLocationContext(context);
			persistLocationContext(context);

			var $activeChip = $chips.find('.filter-chip.is-active').first();
			if (!$activeChip.length) {
				$activeChip = $chips.find('.filter-chip[data-filter-type="all"]').first();
			}
			if ($activeChip.length) {
				$activeChip.removeClass('is-active').attr('aria-pressed', 'false');
				$activeChip.trigger('click');
			}
		}

		window.escortwpApplyLocationContext = applyLocationContext;

	$chips.on('click', '.filter-chip', function (e) {
		var $btn = $(this);
		if ($btn.is('[data-filter-nav]')) {
			pushEvent('ek_filter_nav', {
				page_template: pageTemplate,
				label: getChipLabel($btn),
				destination: $btn.attr('href') || ''
			});
			return;
		}
		e.preventDefault();
		if ($btn.hasClass('is-active')) return;

		setActiveChip($btn);
		updateSectionTitles($btn);
		refreshLastActive();
		hideFilterEmptyState();
		$onlineStoriesSection.removeClass('is-filtering');

			var filterType = $btn.data('filter-type') || 'all';
			var filterValue = $btn.data('filter-value') || '';
			var locationContext = getLocationContext();
			var taxonomy = locationContext.taxonomy || '';
			var termId = locationContext.termId || '';
		var $grids = $('.escort-grid__container[data-grid]');

		$grids.addClass('is-loading');
		$grids.each(function () {
			var $grid = $(this);
			var count = $grid.data('skeleton-count');
			$grid.html(skeletonMarkup(count));
		});

		if (activeRequest && activeRequest.readyState !== 4) {
			activeRequest.abort();
		}

		activeRequest = $.ajax({
			type: 'POST',
			url: ajaxUrl,
			dataType: 'json',
			data: {
				action: 'escortwp_filter_home_sections',
				filter_type: filterType,
				filter_value: filterValue,
				taxonomy: taxonomy,
				term_id: termId,
				nonce: nonce
			}
		}).done(function (response) {
			if (!response || !response.success) {
				pushEvent('ek_filter_apply', {
					page_template: pageTemplate,
					filter_type: filterType,
					filter_value: filterValue,
					results_count: 0,
					status: 'error'
				});
				return;
			}
			var data = response.data || {};
			if (data.vip_html !== undefined) updateGrid('[data-grid="vip"]', data.vip_html);
			if (data.premium_html !== undefined) updateGrid('[data-grid="premium"]', data.premium_html);
			if (data.new_html !== undefined) updateGrid('[data-grid="new"]', data.new_html);

				updateSectionTitles($btn, {
					onlineFallback: data.online_fallback,
					locationName: data.summary && data.summary.location_name ? data.summary.location_name : ''
				});
				refreshLastActive({ maxMinutes: (filterType === 'recent_24h' || data.online_fallback) ? 1440 : 180 });
				updateSummary(data.summary || {}, $btn);
			var totalResults = data.summary && typeof data.summary.total_results !== 'undefined'
				? parseInt(data.summary.total_results, 10)
				: 0;
			if (shouldShowFilterEmptyState(filterType, isNaN(totalResults) ? 0 : totalResults)) {
				showFilterEmptyState(filterType);
			} else {
				hideFilterEmptyState();
			}
			if (typeof window.escortwpApplyImageFallbacks === 'function') {
				window.escortwpApplyImageFallbacks($('.escort-grid__container[data-grid], .online-stories-carousel'));
			}

			pushEvent('ek_filter_apply', {
				page_template: pageTemplate,
				filter_type: filterType,
				filter_value: filterValue,
				results_count: data.summary && typeof data.summary.total_results !== 'undefined' ? data.summary.total_results : 0,
				status: 'success'
			});
		}).fail(function () {
			pushEvent('ek_filter_apply', {
				page_template: pageTemplate,
				filter_type: filterType,
				filter_value: filterValue,
				results_count: 0,
				status: 'error'
			});
		}).always(function () {
			$grids.removeClass('is-loading');
			$onlineStoriesSection.removeClass('is-filtering');
			syncOnlineStoriesAction();
		});
	});

	$(document).on('click', '[data-online-view-all]', function (e) {
		var $trigger = $(this);
		var $onlineChip = $chips.find('.filter-chip[data-filter-type="online"]').first();
		var $allChip = $chips.find('.filter-chip[data-filter-type="all"]').first();
		if (!isSmallFilterViewport() || !$onlineChip.length || !$allChip.length) {
			return;
		}

		e.preventDefault();
		$onlineStoriesSection.addClass('is-filtering');
		$trigger.addClass('is-arming');
		window.setTimeout(function () {
			$trigger.removeClass('is-arming');
		}, 320);

		scrollToFilterResults();

		if ($onlineChip.hasClass('is-active')) {
			$allChip.trigger('click');
			return;
		}

		$onlineChip.trigger('click');
	});

	$(document).on('click', '[data-empty-filter]', function (e) {
		var targetFilter = ($(this).attr('data-empty-filter') || '').toString();
		var $targetChip = $chips.find('.filter-chip[data-filter-type="' + targetFilter + '"]').first();
		if (!$targetChip.length) {
			return;
		}
		e.preventDefault();
		$targetChip.trigger('click');
	});

	$(document).on('click', '[data-empty-nearby]', function (e) {
		var hostname = window.location.hostname || '';
		var isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1' || /\.localhost$/i.test(hostname);
		var canUseGeo = !!window.isSecureContext || window.location.protocol === 'https:' || isLocalhost;
		e.preventDefault();
		if (canUseGeo && navigator.geolocation && $('[data-use-current-location]').first().length) {
			$('[data-use-current-location]').first().trigger('click');
			return;
		}
		$('.open-country').first().trigger('click');
	});

		$chips.closest('.filter-chips-section').on('click', '[data-filter-clear]', function (e) {
			e.preventDefault();
			var $allChip = $chips.find('.filter-chip[data-filter-type=\"all\"]').first();
			if (!$allChip.length) {
				return;
			}
			clearPersistedLocationContext();
			pushEvent('ek_filter_clear', {
				page_template: pageTemplate,
				filter_type: 'all',
				filter_value: '',
				results_count: 0,
				status: 'initiated'
			});
			$allChip.removeClass('is-active').attr('aria-pressed', 'false');
			$allChip.trigger('click');
		});

	var $activeChip = $chips.find('.filter-chip.is-active').first();
	var initialLocationContext = getLocationContext();
	updateSectionTitles($activeChip);
	updateSummary({
		total_results: computeInitialSummary(),
		active_filter_label: getChipLabel($activeChip),
		active_filter_type: ($activeChip.data('filter-type') || 'all'),
		active_context_label: initialLocationContext.termName
			? ('All escorts in ' + initialLocationContext.termName)
			: getChipLabel($activeChip)
	}, $activeChip);
	refreshLastActive();
	hideFilterEmptyState();
	syncOnlineStoriesAction();
		if (typeof window.escortwpApplyImageFallbacks === 'function') {
			window.escortwpApplyImageFallbacks($('.escort-grid__container[data-grid], .online-stories-carousel'));
		}

			try {
				if (!getLocationContext().termId && geoSessionKey) {
					var storedLocation = window.sessionStorage.getItem(geoSessionKey);
					if (storedLocation) {
						applyLocationContext(JSON.parse(storedLocation));
					}
				}
			} catch (err) { }
		});

// Contact submit states + analytics
jQuery(function ($) {
	var $form = $('[data-contact-form]');
	if (!$form.length) {
		return;
	}

	var pageTemplate = (window.escortwpFilterChips && window.escortwpFilterChips.pageTemplate) || 'nav-contact';
	var $submit = $form.find('[data-contact-submit]').first();
	var $status = $('[data-contact-status]').first();

	$form.on('submit', function () {
		if (!$submit.length) return;
		$submit.prop('disabled', true).addClass('is-loading');
		window.escortwpPushEvent('ek_contact_submit', {
			page_template: pageTemplate,
			filter_type: '',
			filter_value: '',
			results_count: 0,
			status: 'submitted'
		});
	});

	if ($status.length) {
		var statusText = $.trim($status.text());
		if (statusText.length) {
			var statusType = $status.hasClass('is-success') ? 'success' : 'error';
			window.escortwpPushEvent('ek_contact_result', {
				page_template: pageTemplate,
				filter_type: '',
				filter_value: '',
				results_count: 0,
				status: statusType
			});
		}
	}
});

// Blog card click analytics
jQuery(function ($) {
	$(document).on('click', '[data-blog-card-click]', function () {
		window.escortwpPushEvent('ek_blog_card_click', {
			page_template: 'nav-blog',
			filter_type: '',
			filter_value: '',
			results_count: 0,
			status: 'click'
		});
	});
});

// Deferred video activation + analytics
jQuery(function ($) {
	$(document).on('click', '[data-video-play]', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var src = $btn.data('video-src') || '';
		var poster = $btn.data('video-poster') || '';
		if (!src) return;

		var $card = $btn.closest('.video-card');
		var $player = $card.find('[data-video-player]').first();
		if (!$player.length) return;

		if (!$player.data('mounted')) {
			var video = document.createElement('video');
			video.setAttribute('controls', 'controls');
			video.setAttribute('preload', 'metadata');
			video.setAttribute('playsinline', 'playsinline');
			if (poster) {
				video.setAttribute('poster', poster);
			}
			video.src = src;
			video.className = 'video-card__video-el';

			$player.empty().append(video).prop('hidden', false).data('mounted', true);
		} else {
			$player.prop('hidden', false);
		}

		$btn.attr('disabled', 'disabled').text('Playing');
		var playerEl = $player.find('video').get(0);
		if (playerEl && typeof playerEl.play === 'function') {
			playerEl.play();
		}

		window.escortwpPushEvent('ek_video_play', {
			page_template: 'template-videos',
			filter_type: '',
			filter_value: '',
			results_count: 1,
			status: 'play'
		});
	});
});

// Editorial clamp toggle
jQuery(function ($) {
	$(document).on('click', '[data-editorial-toggle]', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var expanded = $btn.attr('aria-expanded') === 'true';
		$btn.attr('aria-expanded', expanded ? 'false' : 'true');
		$btn.closest('[data-editorial-intro]').toggleClass('is-expanded', !expanded);
		$btn.text(expanded ? 'Read more' : 'Read less');
	});
});

// Sidebar-right mobile de-emphasis via collapsible non-critical widgets
jQuery(function ($) {
	var $sidebar = $('.sidebar-right');
	if (!$sidebar.length) return;

	var mq = window.matchMedia ? window.matchMedia('(max-width: 960px)') : null;
	var isMobile = function () {
		if (!mq) return $(window).width() <= 960;
		return mq.matches;
	};

	$sidebar.find('.widgetbox-wrapper .widgetbox').each(function (index) {
		var $widget = $(this);
		var $title = $widget.children('.widgettitle').first();
		if (!$title.length) return;

		var panelId = 'sidebar-widget-panel-' + index;
		var $panel = $widget.children('.sidebar-widget__panel').first();
		if (!$panel.length) {
			$panel = $('<div class=\"sidebar-widget__panel\"></div>');
			$panel.attr('id', panelId).append($title.nextAll());
			$title.after($panel);
		}

		if (!$title.find('button.sidebar-widget__toggle').length) {
			var label = $.trim($title.text()) || 'Section';
			$title.empty().append(
				$('<button type=\"button\" class=\"sidebar-widget__toggle\" aria-expanded=\"false\"></button>')
					.attr('aria-controls', panelId)
					.text(label)
			);
		}
	});

	$sidebar.on('click', '.sidebar-widget__toggle', function () {
		if (!isMobile()) return;
		var $btn = $(this);
		var expanded = $btn.attr('aria-expanded') === 'true';
		$btn.attr('aria-expanded', expanded ? 'false' : 'true');
		$('#' + $btn.attr('aria-controls')).prop('hidden', expanded);
	});

	function applySidebarState() {
		$sidebar.find('.sidebar-widget__toggle').each(function () {
			var $btn = $(this);
			var targetId = $btn.attr('aria-controls');
			if (!targetId) return;
			var $panel = $('#' + targetId);
			if (!$panel.length) return;

			if (isMobile()) {
				$btn.attr('aria-expanded', 'false');
				$panel.prop('hidden', true);
			} else {
				$btn.attr('aria-expanded', 'true');
				$panel.prop('hidden', false);
			}
		});
	}

	applySidebarState();
	if (mq) {
		if (mq.addEventListener) {
			mq.addEventListener('change', applySidebarState);
		} else if (mq.addListener) {
			mq.addListener(applySidebarState);
		}
	}
});


// Admin quick actions on single escort profiles (admin only)
jQuery(function ($) {
	if (!$('body.single-escort').length) {
		return;
	}

	var knownActions = ['editprofile', 'addtours', 'verified_status', 'addanote', 'delete'];

	function getActionFromElement($el) {
		for (var i = 0; i < knownActions.length; i++) {
			if ($el.hasClass(knownActions[i])) {
				return knownActions[i];
			}
		}
		return '';
	}

	function getAdminScrollOffset() {
		var offset = 16;
		var $adminBar = $('#wpadminbar');
		if ($adminBar.length && $adminBar.css('position') === 'fixed') {
			offset += $adminBar.outerHeight();
		}

		var $quickActions = $('.profile-admin-quick-actions--sticky:visible').first();
		if ($quickActions.length) {
			offset += $quickActions.outerHeight() + 12;
		}

		return offset;
	}

	function scrollToSection($section) {
		if (!$section || !$section.length) {
			return;
		}

		var targetTop = Math.max($section.offset().top - getAdminScrollOffset(), 0);
		$('html, body').stop(true).animate({
			scrollTop: targetTop
		}, 280);
	}

	function focusFirstField($section) {
		if (!$section || !$section.length) {
			return;
		}

		var $firstField = $section
			.find('input:not([type="hidden"]):enabled:visible, select:enabled:visible, textarea:enabled:visible, button:enabled:visible, a[href]:visible')
			.first();

		if ($firstField.length) {
			$firstField.trigger('focus');
		}
	}

	function openAdminSection(action) {
		if (!action) {
			return false;
		}

		var $target = $('.agency_options_' + action).first();
		if (!$target.length) {
			return false;
		}

		$('.agency_options_dropdowns').stop(true, true).slideUp('fast');
		$('.girlsingle').stop(true, true).slideUp('fast');
		$target.stop(true, true).slideDown('fast', function () {
			scrollToSection($target);
			focusFirstField($target);
		});

		if ($.fn.select2) {
			$target.find('.select2').select2();
		}

		return true;
	}

	function triggerSidebarFallback(action) {
		var fallbackMap = {
			editprofile: '.agencyeditbuttons a.editprofile',
			addtours: '.agencyeditbuttons a.addtours',
			verified_status: '.agencyeditbuttons a.verified_status',
			addanote: '.agencyeditbuttons a.addanote',
			delete: '.agencyeditbuttons a.delete, .agencyeditbuttons a.admin-delete-profile'
		};
		var selector = fallbackMap[action] || ('.agencyeditbuttons a.' + action);
		var $fallback = $(selector).first();

		if ($fallback.length) {
			$fallback.trigger('click');
			return true;
		}

		return false;
	}

	function openAndAssist(action) {
		if (!openAdminSection(action)) {
			triggerSidebarFallback(action);
		}

		setTimeout(function () {
			var $opened = $('.agency_options_' + action + ':visible').first();
			if ($opened.length) {
				scrollToSection($opened);
				focusFirstField($opened);
			}
		}, 220);
	}

	$(document).on('click', '[data-admin-profile-action]', function (event) {
		event.preventDefault();
		var action = String($(this).data('admin-profile-action') || '');
		if (!action) {
			return;
		}
		openAndAssist(action);
	});

	$(document).on('click', '.agencyeditbuttons a', function () {
		var action = getActionFromElement($(this));
		if (!action) {
			return;
		}

		setTimeout(function () {
			var $opened = $('.agency_options_' + action + ':visible').first();
			if ($opened.length) {
				scrollToSection($opened);
				focusFirstField($opened);
			}
		}, 220);
	});
});
