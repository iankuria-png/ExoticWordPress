(function () {
  'use strict';

  var form = document.getElementById('efm-rule-form');
  if (!form) {
    return;
  }

  var scopeSelect = form.querySelector('[data-efm-scope]');
  var scopeWrap = form.querySelector('[data-efm-scope-value-wrap]');
  var scopeInput = form.querySelector('[data-efm-scope-value]');

  function syncScopeField() {
    if (!scopeSelect || !scopeWrap || !scopeInput) {
      return;
    }

    var type = scopeSelect.value || 'site';
    var showValue = type !== 'site' && type !== 'front_page';

    scopeWrap.style.display = showValue ? '' : 'none';
    scopeInput.required = showValue;

    if (!showValue) {
      scopeInput.value = '';
    }
  }

  if (scopeSelect) {
    scopeSelect.addEventListener('change', syncScopeField);
    syncScopeField();
  }
})();
