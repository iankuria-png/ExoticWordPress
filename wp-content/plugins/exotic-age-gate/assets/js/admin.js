(function () {
	"use strict";

	var config = window.exoticAgeGateAdmin || {};
	var fields = document.querySelectorAll("[data-age-gate-logo-field]");

	if (!fields.length || typeof wp === "undefined" || !wp.media) {
		return;
	}

	fields.forEach(function (field) {
		var input = field.querySelector("[data-age-gate-logo-input]");
		var preview = field.querySelector("[data-age-gate-logo-preview]");
		var selectButton = field.querySelector("[data-age-gate-media-select]");
		var removeButton = field.querySelector("[data-age-gate-media-remove]");
		var frame;

		function renderPreview(url) {
			if (!preview || !input) {
				return;
			}

			preview.classList.toggle("is-empty", !url);
			preview.innerHTML = "";

			if (!url) {
				var emptyState = document.createElement("span");
				emptyState.setAttribute("data-age-gate-logo-empty", "");
				emptyState.textContent = config.emptyLabel || "No logo selected.";
				preview.appendChild(emptyState);
				if (removeButton) {
					removeButton.hidden = true;
				}
				return;
			}

			var image = document.createElement("img");
			image.src = url;
			image.alt = "Selected logo preview";
			image.setAttribute("data-age-gate-logo-image", "");
			preview.appendChild(image);

			if (removeButton) {
				removeButton.hidden = false;
			}
		}

		if (selectButton) {
			selectButton.addEventListener("click", function (event) {
				event.preventDefault();

				if (!frame) {
					frame = wp.media({
						title: config.frameTitle || "Choose logo",
						button: {
							text: config.frameButton || "Use this logo"
						},
						multiple: false,
						library: {
							type: "image"
						}
					});

					frame.on("select", function () {
						var attachment = frame.state().get("selection").first();
						if (!attachment) {
							return;
						}

						var data = attachment.toJSON();
						input.value = data.url || "";
						renderPreview(input.value);
					});
				}

				frame.open();
			});
		}

		if (removeButton) {
			removeButton.addEventListener("click", function (event) {
				event.preventDefault();
				if (!input) {
					return;
				}

				input.value = "";
				renderPreview("");
			});
		}
	});
}());
