"use strict";

/* eslint-disable */
(function ($) {

  Drupal.behaviors.verticalTabs = {
    attach: function attach(context, settings) {

      $(".js-tabs").tabs().addClass("ui-helper-clearfix");
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_vertical_tabs.js.map
