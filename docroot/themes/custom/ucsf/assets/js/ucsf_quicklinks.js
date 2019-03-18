"use strict";

/* eslint-disable */
(function ($) {
  Drupal.behaviors.quicklinks = {
    attach: function attach(context) {

      $(".quicklinks-trigger, .quicklinks-close", context).click(function () {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".quicklinks").toggleClass('js-quicklinks-open');
        $(".header-region, .region-content").toggleClass('js-quicklinks-open');
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_quicklinks.js.map
