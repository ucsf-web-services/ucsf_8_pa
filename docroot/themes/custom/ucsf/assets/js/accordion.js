"use strict";

(function ($) {
  'use strict';

  Drupal.behaviors.accordionOHM = {
    attach: function attach(context, settings) {
      $(context).find(".accordion-paragraph").once("accordion-init").each(function () {
        $('.accordion-paragraph', context).accordion({
          heightStyle: 'content',
          icons: { "header": "accordion-angle-down", "activeHeader": "accordion-angle-up" }
        });
      });
    }
  };
})(jQuery);
//# sourceMappingURL=accordion.js.map
