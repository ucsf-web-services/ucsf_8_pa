"use strict";

/* eslint-disable */
(function ($) {

  Drupal.behaviors.verticalTabs = {
    attach: function attach(context, settings) {

      $(".js-tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix");
      $(".js-tabs").removeClass("ui-corner-top").addClass("ui-corner-left");
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_vertical_tabs.js.map
