(function () {
  'use strict';

  var cfg = window.exoticCampaignDashboard || null;
  var root = document.getElementById('exotic-campaign-dashboard');

  if (!cfg || !root) {
    return;
  }

  var tableBody = root.querySelector('#campaign-dashboard-table tbody');
  var feedback = root.querySelector('#campaign-dashboard-feedback');
  var filterSelect = root.querySelector('#campaign-status-filter');
  var refreshBtn = root.querySelector('#campaign-refresh-btn');
  var createBtn = root.querySelector('#campaign-create-btn');

  var modal = root.querySelector('#campaign-form-modal');
  var form = root.querySelector('#campaign-dashboard-form');
  var formatSelect = form.querySelector('select[name="_campaign_format"]');
  var formFeedback = form.querySelector('#campaign-form-feedback');
  var saveBtn = form.querySelector('#campaign-save-btn');
  var titleInput = form.querySelector('input[name="post_title"]');
  var previousFocusedElement = null;

  var imageIdInput = form.querySelector('input[name="_campaign_image_id"]');
  var imagePreview = form.querySelector('[data-image-preview]');
  var imageSelectBtn = form.querySelector('[data-image-select]');
  var imageRemoveBtn = form.querySelector('[data-image-remove]');

  var stats = {
    active: root.querySelector('[data-stat="active"]'),
    impressions: root.querySelector('[data-stat="impressions"]'),
    clicks: root.querySelector('[data-stat="clicks"]'),
    ctr: root.querySelector('[data-stat="ctr"]')
  };

  var campaignsById = new Map();

  function showFeedback(message, isError) {
    feedback.textContent = message || '';
    feedback.style.color = isError ? '#ff9a9a' : '#cce5ff';
  }

  function showFormFeedback(message, isError) {
    if (!formFeedback) {
      return;
    }

    formFeedback.textContent = message || '';
    formFeedback.style.color = isError ? '#ffb7c6' : '#b5f4da';
  }

  function setSaveState(isSaving) {
    if (!saveBtn) {
      return;
    }

    saveBtn.disabled = !!isSaving;
    saveBtn.textContent = isSaving ? 'Saving...' : 'Save Campaign';
    form.setAttribute('aria-busy', isSaving ? 'true' : 'false');
  }

  function trapModalFocus(event) {
    if (modal.hidden || event.key !== 'Tab') {
      return;
    }

    var focusable = modal.querySelectorAll('a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])');
    if (!focusable.length) {
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

  function formatNumber(value) {
    return Number(value || 0).toLocaleString();
  }

  function api(path, options) {
    options = options || {};

    var headers = options.headers || {};
    headers['X-WP-Nonce'] = cfg.nonce;

    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }

    return fetch(cfg.apiRoot + path, {
      method: options.method || 'GET',
      credentials: 'same-origin',
      headers: headers,
      body: options.body || null
    }).then(function (response) {
      return response.json().catch(function () {
        return {};
      }).then(function (data) {
        if (!response.ok) {
          var message = (data && data.message) ? data.message : (cfg.messages && cfg.messages.errorGeneric) || 'Request failed.';
          throw new Error(message);
        }
        return data;
      });
    });
  }

  function updateStats(data) {
    var summary = data && data.summary ? data.summary : {};

    stats.active.textContent = formatNumber(summary.campaigns_active || 0);
    stats.impressions.textContent = formatNumber(summary.impressions_total || 0);
    stats.clicks.textContent = formatNumber(summary.clicks_total || 0);
    stats.ctr.textContent = Number(summary.avg_ctr || 0).toFixed(2) + '%';
  }

  function statusActionLabel(status) {
    return status === 'active' ? 'Pause' : 'Activate';
  }

  function statusActionTarget(status) {
    return status === 'active' ? 'paused' : 'active';
  }

  function formatStatusLabel(status) {
    var value = String(status || 'scheduled');
    return value.charAt(0).toUpperCase() + value.slice(1);
  }

  function renderRows(items) {
    campaignsById.clear();

    if (!Array.isArray(items) || items.length === 0) {
      tableBody.innerHTML = '<tr class="exotic-campaign-row--empty"><td colspan="8">No campaigns found for this filter.</td></tr>';
      return;
    }

    var html = items.map(function (item) {
      campaignsById.set(Number(item.id), item);

      var ctr = Number(item.ctr || 0).toFixed(2) + '%';
      var actionLabel = statusActionLabel(item.status);
      var actionTarget = statusActionTarget(item.status);
      var status = String(item.status || 'scheduled');
      var format = String(item.format || 'card').toUpperCase();
      var title = item.title || 'Untitled';

      return '<tr class="exotic-campaign-row exotic-campaign-row--' + escapeAttr(status) + '">' +
        '<td data-label="Campaign"><strong>' + escapeHtml(title) + '</strong></td>' +
        '<td data-label="Format"><span class="exotic-format-chip">' + escapeHtml(format) + '</span></td>' +
        '<td data-label="Status"><span class="exotic-status-chip exotic-status-chip--' + escapeAttr(status) + '">' + escapeHtml(formatStatusLabel(status)) + '</span></td>' +
        '<td data-label="Priority">' + escapeHtml(String(item.priority || 10)) + '</td>' +
        '<td data-label="Impressions">' + formatNumber(item.impressions || 0) + '</td>' +
        '<td data-label="Clicks">' + formatNumber(item.clicks || 0) + '</td>' +
        '<td data-label="CTR">' + ctr + '</td>' +
        '<td data-label="Actions">' +
          '<div class="exotic-campaign-row-actions">' +
            '<button type="button" class="exotic-row-btn" data-action="edit" data-id="' + Number(item.id) + '" aria-label="Edit ' + escapeAttr(title) + '">Edit</button>' +
            '<button type="button" class="exotic-row-btn" data-action="status" data-id="' + Number(item.id) + '" data-target-status="' + actionTarget + '" aria-label="' + escapeAttr(actionLabel + ' ' + title) + '">' + actionLabel + '</button>' +
            '<button type="button" class="exotic-row-btn exotic-row-btn--danger" data-action="delete" data-id="' + Number(item.id) + '" aria-label="Delete ' + escapeAttr(title) + '">Delete</button>' +
          '</div>' +
        '</td>' +
      '</tr>';
    }).join('');

    tableBody.innerHTML = html;
  }

  function loadCampaigns() {
    var status = filterSelect.value;
    var query = '?per_page=100&page=1';
    if (status) {
      query += '&status=' + encodeURIComponent(status);
    }

    return api('/campaigns' + query).then(function (data) {
      renderRows(data.items || []);
    });
  }

  function loadSummary() {
    return api('/analytics/summary').then(updateStats);
  }

  function toggleFormatBlocks() {
    var format = formatSelect.value || 'card';

    form.querySelectorAll('[data-format]').forEach(function (block) {
      block.hidden = block.getAttribute('data-format') !== format;
    });

    if (format === 'card') {
      form.querySelector('input[name="_campaign_cta_visible"]').checked = true;
    }
  }

  function clearForm() {
    form.reset();
    form.querySelector('input[name="id"]').value = '';
    form.querySelector('input[name="_campaign_priority"]').value = '10';
    imageIdInput.value = '';
    imagePreview.innerHTML = '';
    showFormFeedback('', false);
    setSaveState(false);
    toggleFormatBlocks();
  }

  function toDatetimeLocal(value) {
    if (!value) {
      return '';
    }

    var normalized = String(value).replace(' ', 'T');
    return normalized.slice(0, 16);
  }

  function fillForm(item) {
    clearForm();

    form.querySelector('input[name="id"]').value = String(item.id || '');
    form.querySelector('input[name="post_title"]').value = item.title || '';
    form.querySelector('select[name="_campaign_format"]').value = item.format || 'card';
    form.querySelector('input[name="_campaign_badge_text"]').value = item.badge_text || '';
    form.querySelector('textarea[name="_campaign_description"]').value = item.description || '';
    form.querySelector('input[name="_campaign_icon_class"]').value = item.icon_class || '';
    form.querySelector('input[name="_campaign_color_primary"]').value = item.color_primary || '';
    form.querySelector('input[name="_campaign_color_secondary"]').value = item.color_secondary || '';
    form.querySelector('input[name="_campaign_image_alt"]').value = item.image_alt || '';
    form.querySelector('input[name="_campaign_cta_text"]').value = item.cta_text || '';
    form.querySelector('input[name="_campaign_cta_url"]').value = item.cta_url || '';
    form.querySelector('input[name="_campaign_cta_visible"]').checked = !!item.cta_visible;
    form.querySelector('select[name="_campaign_status"]').value = item.status || 'scheduled';
    form.querySelector('input[name="_campaign_priority"]').value = String(item.priority || 10);
    form.querySelector('input[name="_campaign_start_date"]').value = toDatetimeLocal(item.start_date);
    form.querySelector('input[name="_campaign_end_date"]').value = toDatetimeLocal(item.end_date);

    if (item.image_id) {
      imageIdInput.value = String(item.image_id);
      if (window.wp && wp.media && wp.media.attachment) {
        wp.media.attachment(item.image_id).fetch().then(function () {
          var url = wp.media.attachment(item.image_id).get('url');
          if (url) {
            imagePreview.innerHTML = '<img src="' + escapeAttr(url) + '" alt="" />';
          }
        }).catch(function () {
          // Ignore preview fetch errors.
        });
      }
    }

    toggleFormatBlocks();
  }

  function openModal(item) {
    previousFocusedElement = document.activeElement;

    if (item) {
      fillForm(item);
    } else {
      clearForm();
    }

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.style.overflow = 'hidden';
    window.requestAnimationFrame(function () {
      if (titleInput) {
        titleInput.focus();
      }
    });
  }

  function closeModal() {
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.style.overflow = '';
    showFormFeedback('', false);
    setSaveState(false);

    if (previousFocusedElement && typeof previousFocusedElement.focus === 'function') {
      previousFocusedElement.focus();
    } else if (createBtn) {
      createBtn.focus();
    }
  }

  function collectPayload() {
    var formData = new FormData(form);
    var payload = {};

    formData.forEach(function (value, key) {
      if (key === 'id') {
        return;
      }
      payload[key] = value;
    });

    payload._campaign_cta_visible = form.querySelector('input[name="_campaign_cta_visible"]').checked ? '1' : '0';

    if (!payload._campaign_priority) {
      payload._campaign_priority = 10;
    }

    if (payload._campaign_format === 'card') {
      payload._campaign_image_id = 0;
      payload._campaign_image_alt = '';
      payload._campaign_cta_visible = '1';
    }

    if (!payload._campaign_status) {
      payload._campaign_status = 'scheduled';
    }

    return payload;
  }

  function validatePayload(payload) {
    var title = String(payload.post_title || '').trim();
    if (!title) {
      return 'Campaign name is required.';
    }

    payload.post_title = title;

    if (payload._campaign_format === 'image' && !(Number(payload._campaign_image_id) > 0)) {
      return 'Choose a campaign image for image format.';
    }

    var start = payload._campaign_start_date ? Date.parse(payload._campaign_start_date) : null;
    var end = payload._campaign_end_date ? Date.parse(payload._campaign_end_date) : null;

    if (start && end && end < start) {
      return 'End date must be later than start date.';
    }

    return '';
  }

  function saveCampaign(event) {
    event.preventDefault();

    var id = Number(form.querySelector('input[name="id"]').value || 0);
    var payload = collectPayload();
    var validationMessage = validatePayload(payload);

    if (validationMessage) {
      showFormFeedback(validationMessage, true);
      return;
    }

    var path = id > 0 ? '/campaigns/' + id : '/campaigns';
    showFormFeedback('', false);
    setSaveState(true);

    api(path, {
      method: 'POST',
      body: payload
    }).then(function () {
      showFeedback((cfg.messages && cfg.messages.saveSuccess) || 'Campaign saved.', false);
      closeModal();
      return Promise.all([loadSummary(), loadCampaigns()]);
    }).catch(function (error) {
      showFormFeedback(error.message, true);
      showFeedback(error.message, true);
    }).finally(function () {
      setSaveState(false);
    });
  }

  function deleteCampaign(id) {
    if (!confirm((cfg.messages && cfg.messages.deleteConfirm) || 'Delete this campaign?')) {
      return;
    }

    api('/campaigns/' + id, {
      method: 'DELETE'
    }).then(function () {
      showFeedback((cfg.messages && cfg.messages.deleteSuccess) || 'Campaign deleted.', false);
      return Promise.all([loadSummary(), loadCampaigns()]);
    }).catch(function (error) {
      showFeedback(error.message, true);
    });
  }

  function changeStatus(id, targetStatus) {
    api('/campaigns/' + id + '/status', {
      method: 'POST',
      body: { status: targetStatus }
    }).then(function () {
      return Promise.all([loadSummary(), loadCampaigns()]);
    }).catch(function (error) {
      showFeedback(error.message, true);
    });
  }

  function attachTableActions() {
    tableBody.addEventListener('click', function (event) {
      var button = event.target.closest('button[data-action]');
      if (!button) {
        return;
      }

      var id = Number(button.getAttribute('data-id') || 0);
      if (!(id > 0)) {
        return;
      }

      var action = button.getAttribute('data-action');
      if (action === 'edit') {
        var item = campaignsById.get(id);
        if (item) {
          openModal(item);
        }
        return;
      }

      if (action === 'delete') {
        deleteCampaign(id);
        return;
      }

      if (action === 'status') {
        var target = button.getAttribute('data-target-status') || 'active';
        changeStatus(id, target);
      }
    });
  }

  function initModal() {
    root.querySelectorAll('[data-modal-close]').forEach(function (el) {
      el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
      trapModalFocus(event);

      if (event.key === 'Escape' && !modal.hidden) {
        closeModal();
      }
    });

    formatSelect.addEventListener('change', toggleFormatBlocks);

    form.addEventListener('submit', saveCampaign);

    createBtn.addEventListener('click', function () {
      openModal(null);
    });
  }

  function initImagePicker() {
    var frame;

    imageSelectBtn.addEventListener('click', function () {
      if (!window.wp || !wp.media) {
        return;
      }

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: 'Choose Campaign Image',
        button: { text: 'Use image' },
        library: { type: 'image' },
        multiple: false
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        imageIdInput.value = attachment.id || '';

        var url = (attachment.sizes && attachment.sizes.medium_large)
          ? attachment.sizes.medium_large.url
          : attachment.url;

        if (url) {
          imagePreview.innerHTML = '<img src="' + escapeAttr(url) + '" alt="" />';
        }
      });

      frame.open();
    });

    imageRemoveBtn.addEventListener('click', function () {
      imageIdInput.value = '';
      imagePreview.innerHTML = '';
    });
  }

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function escapeAttr(value) {
    return escapeHtml(value);
  }

  refreshBtn.addEventListener('click', function () {
    Promise.all([loadSummary(), loadCampaigns()]).catch(function (error) {
      showFeedback(error.message, true);
    });
  });

  filterSelect.addEventListener('change', function () {
    loadCampaigns().catch(function (error) {
      showFeedback(error.message, true);
    });
  });

  attachTableActions();
  initModal();
  initImagePicker();

  Promise.all([loadSummary(), loadCampaigns()]).catch(function (error) {
    showFeedback(error.message, true);
  });
})();
