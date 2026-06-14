(function () {
  'use strict';

  function init() {
    var root = document.querySelector('.productlist-card-config');

    if (!root) {
      return;
    }

    var tabs = root.querySelectorAll('[data-productlist-admin-tab]');
    var panels = root.querySelectorAll('[data-productlist-admin-panel]');
    var toggles = root.querySelectorAll('.productlist-admin-toggle input[type="checkbox"]');

    Array.prototype.forEach.call(tabs, function (tab) {
      tab.addEventListener('click', function () {
        var view = tab.getAttribute('data-productlist-admin-tab');

        Array.prototype.forEach.call(tabs, function (item) {
          var active = item === tab;
          item.classList.toggle('is-active', active);
          item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        Array.prototype.forEach.call(panels, function (panel) {
          panel.classList.toggle('is-active', panel.getAttribute('data-productlist-admin-panel') === view);
        });
      });
    });

    Array.prototype.forEach.call(toggles, function (toggle) {
      toggle.addEventListener('change', function () {
        var label = toggle.closest('.productlist-admin-toggle');

        if (label) {
          label.classList.toggle('is-enabled', toggle.checked);
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
