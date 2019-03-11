"use strict";

/* eslint-disable */
(function ($) {
  Drupal.behaviors.quicklinks = {
    attach: function attach(context) {

      $(".quicklinks, .quicklinks-close", context).click(function () {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".main").toggleClass('js-quicklinks-open');
        // @todo Add scroll up function on small screens because quicklinks toggle is fixed?
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_quicklinks.js.map
