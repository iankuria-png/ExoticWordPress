(function () {
  'use strict';

  var config = window.exoticChatLandingConfig || {};
  var runtime = window.exoticChatLandingRuntime || {};
  var delayMs = Number(config.autoOpenDelayMs || 1500);
  var metadataAttached = false;
  var actionButtonMode = 'hidden';
  var chatReady = false;
  var widgetObserverStarted = false;
  var strings = config.strings || {};

  if (!Number.isFinite(delayMs) || delayMs < 0) {
    delayMs = 1500;
  }

  function t(key, fallback) {
    var value = strings[key];
    return typeof value === 'string' && value !== '' ? value : fallback;
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true });
      return;
    }
    fn();
  }

  function statusText(message) {
    var statusEl = document.getElementById('exotic-chat-status');
    if (statusEl) {
      statusEl.textContent = message;
    }
  }

  function getFallbackButton() {
    return document.getElementById('exotic-chat-focus-fallback');
  }

  function getBadgesContainer() {
    return document.getElementById('exotic-chat-badges');
  }

  function setChatOpenState(isOpen) {
    var opened = Boolean(isOpen);
    if (document.body) {
      document.body.classList.toggle('is-chat-open', opened);
    }

    var badges = getBadgesContainer();
    if (badges) {
      badges.setAttribute('aria-hidden', opened ? 'true' : 'false');
    }
  }

  function getActionButtonMode() {
    return actionButtonMode;
  }

  function setActionButtonMode(mode) {
    var button = getFallbackButton();
    actionButtonMode = mode;

    if (!button) {
      return;
    }

    if (mode === 'reopen') {
      button.hidden = false;
      button.dataset.mode = 'reopen';
      button.textContent = t('cta_open', 'Open chat');
      button.setAttribute('aria-label', t('cta_open', 'Open chat'));
      return;
    }

    if (mode === 'focus') {
      button.hidden = false;
      button.dataset.mode = 'focus';
      button.textContent = t('cta_focus', 'Tap to start typing');
      button.setAttribute('aria-label', t('cta_focus', 'Tap to start typing'));
      return;
    }

    button.hidden = true;
    button.dataset.mode = 'hidden';
    button.textContent = t('cta_focus', 'Tap to start typing');
    button.removeAttribute('aria-label');
  }

  function isWidgetOpen() {
    return Boolean(document.body && document.body.classList.contains('sb-chat-open'));
  }

  function syncUiWithWidgetState() {
    var open = isWidgetOpen();
    setChatOpenState(open);

    if (!chatReady) {
      return;
    }

    if (open) {
      if (getActionButtonMode() === 'reopen') {
        setActionButtonMode('hidden');
      }
      return;
    }

    setActionButtonMode('reopen');
    statusText(t('status_minimized', 'Chat minimized. Tap Open chat.'));
  }

  function observeWidgetState() {
    if (widgetObserverStarted || !document.body) {
      return;
    }

    widgetObserverStarted = true;

    if (window.MutationObserver) {
      var observer = new MutationObserver(function () {
        syncUiWithWidgetState();
      });

      observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
      });
    }
  }

  function getComposer() {
    return document.querySelector('.sb-editor textarea, .sb-textarea textarea');
  }

  function getSearchParam(name) {
    try {
      var params = new URLSearchParams(window.location.search || '');
      return params.get(name) || '';
    } catch (error) {
      return '';
    }
  }

  function getDeviceType() {
    var ua = (window.navigator && window.navigator.userAgent) || '';
    if (/Android|iPhone|iPad|iPod|Mobile/i.test(ua)) {
      return 'mobile';
    }

    if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
      return 'mobile';
    }

    return 'desktop';
  }

  function getTimezone() {
    try {
      var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
      return tz || '';
    } catch (error) {
      return '';
    }
  }

  function buildMetadata() {
    return {
      country_code: runtime.countryCode || '',
      host: runtime.host || window.location.hostname || '',
      landing_url: window.location.href || '',
      landing_path: window.location.pathname || '',
      landing_language: runtime.languageCode || '',
      widget_language: runtime.widgetLanguageCode || '',
      referrer: document.referrer || '',
      utm_source: getSearchParam('utm_source'),
      utm_medium: getSearchParam('utm_medium'),
      utm_campaign: getSearchParam('utm_campaign'),
      utm_term: getSearchParam('utm_term'),
      utm_content: getSearchParam('utm_content'),
      browser_language: (window.navigator && window.navigator.language) || '',
      timezone: getTimezone(),
      device_type: getDeviceType()
    };
  }

  function buildSettingsExtraMap(metadata) {
    var result = {};
    Object.keys(metadata).forEach(function (key) {
      var value = metadata[key];
      if (value === null || value === undefined || value === '') {
        return;
      }
      result[key] = [String(value), key];
    });
    return result;
  }

  function attachMetadata() {
    if (metadataAttached) {
      return true;
    }

    var metadata = buildMetadata();

    if (window.SBF && typeof window.SBF.setConversationExtraDetails === 'function') {
      try {
        window.SBF.setConversationExtraDetails(metadata);
        metadataAttached = true;
        return true;
      } catch (error) {
        // Continue to fallback path.
      }
    }

    if (
      window.SBF &&
      typeof window.SBF.ajax === 'function' &&
      typeof window.SBF.activeUser === 'function'
    ) {
      var activeUser = window.SBF.activeUser();
      var userId = activeUser && (activeUser.id || activeUser.user_id);

      if (userId) {
        window.SBF.ajax(
          {
            function: 'update-user',
            user_id: userId,
            settings_extra: buildSettingsExtraMap(metadata)
          },
          function () {}
        );
        metadataAttached = true;
        return true;
      }
    }

    return false;
  }

  function watchMetadataAttachment() {
    var attempts = 0;
    var timer = window.setInterval(function () {
      attempts += 1;

      var hasConversation = Boolean(window.SBChat && window.SBChat.conversation);
      var hasActiveUser = Boolean(window.SBF && typeof window.SBF.activeUser === 'function' && window.SBF.activeUser());

      if ((hasConversation || hasActiveUser) && attachMetadata()) {
        window.clearInterval(timer);
        return;
      }

      if (attempts >= 120) {
        window.clearInterval(timer);
      }
    }, 500);
  }

  function openChat() {
    if (window.SBChat && typeof window.SBChat.open === 'function') {
      window.SBChat.open(true);
      setChatOpenState(true);
      setActionButtonMode('hidden');
      return true;
    }
    return false;
  }

  function focusComposerWithRetries() {
    var attempts = 0;
    var maxAttempts = 20;

    var timer = window.setInterval(function () {
      attempts += 1;
      var composer = getComposer();
      if (composer) {
        try {
          composer.focus({ preventScroll: true });
        } catch (error) {
          composer.focus();
        }

        if (document.activeElement === composer) {
          window.clearInterval(timer);
          setActionButtonMode('hidden');
          statusText(t('status_live', 'Chat is live.'));
          return;
        }
      }

      if (attempts >= maxAttempts) {
        window.clearInterval(timer);
        setActionButtonMode('focus');
        statusText(t('status_focus_prompt', 'Tap the button below to start typing.'));
      }
    }, 200);
  }

  function runAutoOpenFlow() {
    statusText(t('status_opening', 'Opening chat...'));

    if (!openChat()) {
      setChatOpenState(false);
      statusText(t('status_still_loading', 'Still loading chat...'));
      return;
    }

    statusText(t('status_connected', 'Connected. Preparing input...'));
    focusComposerWithRetries();
    watchMetadataAttachment();
  }

  function bindFallbackButton() {
    var button = getFallbackButton();
    if (!button) {
      return;
    }

    button.addEventListener('click', function () {
      var mode = button.dataset.mode || 'focus';
      statusText(mode === 'reopen' ? t('status_reopening', 'Reopening chat...') : t('status_opening', 'Opening chat...'));
      if (!openChat()) {
        statusText(t('status_still_loading', 'Still loading chat...'));
        return;
      }
      focusComposerWithRetries();
      watchMetadataAttachment();
    });
  }

  function waitForChatReady(callback) {
    var scheduled = false;

    function trigger() {
      if (scheduled) {
        return;
      }
      scheduled = true;
      chatReady = true;
      observeWidgetState();
      syncUiWithWidgetState();
      window.setTimeout(callback, delayMs);
    }

    if (window.jQuery && typeof window.jQuery === 'function') {
      window.jQuery(document).one('SBReady', trigger);
      window.jQuery(document).on('SBConversationOpen', function () {
        setChatOpenState(true);
        setActionButtonMode('hidden');
        attachMetadata();
      });
      window.jQuery(document).on('SBConversationClose SBChatClose SBClosed', function () {
        syncUiWithWidgetState();
      });
    }

    if (window.SBChat && typeof window.SBChat.open === 'function') {
      trigger();
      return;
    }

    var pollAttempts = 0;
    var poll = window.setInterval(function () {
      pollAttempts += 1;
      if (window.SBChat && typeof window.SBChat.open === 'function') {
        window.clearInterval(poll);
        trigger();
        return;
      }

      if (pollAttempts >= 60) {
        window.clearInterval(poll);
        setActionButtonMode('focus');
        statusText(t('status_delayed', 'Chat is taking longer than expected. Tap the button below.'));
      }
    }, 250);
  }

  onReady(function () {
    setActionButtonMode('hidden');
    observeWidgetState();
    syncUiWithWidgetState();
    statusText(t('status_loading', 'Chat is loading...'));
    bindFallbackButton();
    waitForChatReady(runAutoOpenFlow);
  });
})();
