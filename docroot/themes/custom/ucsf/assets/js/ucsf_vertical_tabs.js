"use strict";

/* eslint-disable */
(function ($) {

  Drupal.behaviors.verticalTabs = {
    attach: function attach(context, settings) {

      var mediaQuery = window.matchMedia('(min-width: 850px)');

      mediaQuery.addListener(tabAccordionSwitch);

      function tabAccordionSwitch(mediaQuery) {

        // If large screen.
        if (mediaQuery.matches) {

          // Show/hide headers.
          $(".vertical-tab__title").show();
          $(".js-accordion > h3").hide();

          // Destroy accordion if it exists.
          if ($('.ui-accordion').length > 0) {
            $(".js-accordion").accordion('destroy');

            // Create tabs.
            $(".js-tabs").tabs().addClass('ui-helper-clearfix vertical-tab-active layout-left-30-70');
          } else {

            // Create tabs.
            $(".js-tabs").tabs().addClass('ui-helper-clearfix layout-left-30-70');
          }

          // If small screen.
        } else {

          // Show/hide headers.
          $(".vertical-tab__title").hide();
          $(".js-accordion > h3").show();

          // Destroy tabs if they exist.
          if ($('.ui-tabs').length > 0) {
            $(".js-tabs").tabs('destroy');

            // Create accordion.
            $(".js-accordion").accordion({
              header: "h3"
            });
          } else {

            // Create accordion.
            $(".js-accordion").accordion({
              header: "h3"
            });
          }

          // Remove style elements for
          $(".js-tabs").removeClass('layout-left-30-70');
        }
      }

      tabAccordionSwitch(mediaQuery);
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_vertical_tabs.js.map
