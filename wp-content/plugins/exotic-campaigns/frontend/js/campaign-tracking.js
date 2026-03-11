(function () {
  'use strict';

  var cfg = window.exoticCampaignTracking || null;
  if (!cfg || !cfg.impressionUrl || !cfg.clickUrl) {
    return;
  }

  var carousel = document.querySelector('.homepage-ad-carousel .static-ad-carousel');
  if (!carousel) {
    return;
  }

  var pendingImpressions = new Set();
  var seenInPageSession = new Set();
  var flushTimer = null;

  function encodePayload(params) {
    return params.toString();
  }

  function sendFormEncoded(url, params) {
    var payload = encodePayload(params);

    if (navigator.sendBeacon) {
      var blob = new Blob([payload], {
        type: 'application/x-www-form-urlencoded; charset=UTF-8'
      });
      navigator.sendBeacon(url, blob);
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      credentials: 'same-origin',
      keepalive: true,
      body: payload
    }).catch(function () {
      // Ignore tracking transport errors.
    });
  }

  function scheduleImpressionFlush() {
    if (flushTimer) {
      clearTimeout(flushTimer);
    }

    flushTimer = setTimeout(flushImpressions, 3000);
  }

  function queueImpression(campaignId) {
    if (!campaignId || seenInPageSession.has(campaignId)) {
      return;
    }

    seenInPageSession.add(campaignId);
    pendingImpressions.add(campaignId);
    scheduleImpressionFlush();
  }

  function flushImpressions() {
    if (pendingImpressions.size === 0) {
      return;
    }

    var params = new URLSearchParams();
    pendingImpressions.forEach(function (campaignId) {
      params.append('campaign_ids[]', String(campaignId));
    });

    pendingImpressions.clear();
    params.append('_wpnonce', cfg.nonce || '');
    sendFormEncoded(cfg.impressionUrl, params);
  }

  function installImpressionObserver() {
    var cards = carousel.querySelectorAll('[data-campaign-id]');
    if (!cards.length || typeof IntersectionObserver !== 'function') {
      cards.forEach(function (card) {
        var id = parseInt(card.getAttribute('data-campaign-id'), 10);
        if (id > 0) {
          queueImpression(id);
        }
      });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.45) {
          return;
        }

        var id = parseInt(entry.target.getAttribute('data-campaign-id'), 10);
        if (id > 0) {
          queueImpression(id);
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: [0.45, 0.75],
      rootMargin: '0px'
    });

    cards.forEach(function (card) {
      observer.observe(card);
    });
  }

  function installClickTracking() {
    document.addEventListener('click', function (event) {
      var target = event.target;
      if (!target) {
        return;
      }

      var clickable = target.closest('.js-campaign-click');
      if (!clickable) {
        return;
      }

      var idAttr = clickable.getAttribute('data-campaign-id');
      if (!idAttr) {
        var parentCard = clickable.closest('[data-campaign-id]');
        idAttr = parentCard ? parentCard.getAttribute('data-campaign-id') : '';
      }

      var campaignId = parseInt(idAttr || '', 10);
      if (!(campaignId > 0)) {
        return;
      }

      var params = new URLSearchParams();
      params.append('campaign_id', String(campaignId));
      params.append('_wpnonce', cfg.nonce || '');
      sendFormEncoded(cfg.clickUrl, params);
    }, { capture: true, passive: true });
  }

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') {
      flushImpressions();
    }
  });

  window.addEventListener('pagehide', flushImpressions, { passive: true });
  window.addEventListener('beforeunload', flushImpressions, { passive: true });

  installImpressionObserver();
  installClickTracking();
})();
