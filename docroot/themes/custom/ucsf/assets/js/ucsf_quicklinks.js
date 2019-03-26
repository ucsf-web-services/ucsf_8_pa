'use strict';

/* eslint-disable */
(function ($) {

  $(document).ready(function () {
    setTimeout(function () {
      $('body').removeClass('loading');
    }, 2500);
  });

  Drupal.behaviors.quicklinks = {
    attach: function attach(context) {

      $(".quicklinks-trigger, .quicklinks-close", context).click(function () {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".quicklinks").toggleClass('js-quicklinks-open');
        $(".header-region").toggleClass('js-quicklinks-open');
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_quicklinks.js.map
