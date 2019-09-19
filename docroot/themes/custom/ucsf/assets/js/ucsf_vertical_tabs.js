"use strict";

/* eslint-disable */
(function ($) {

  Drupal.behaviors.verticalTabs = {
    attach: function attach(context, settings) {

      var mediaQuery = window.matchMedia('(min-width: 850px)');
      var accordionElement = $(".js-accordion");
      var tabsElement = $(".js-tabs");
      var accordionTitles = $(".js-accordion > h3");
      var tabsTitles = $(".vertical-tab__title");
      var accordionActive = $('.ui-accordion');
      var tabsActive = $('.ui-tabs');

      mediaQuery.addListener(tabAccordionSwitch);

      function tabAccordionSwitch(mediaQuery) {

        // If large screen.
        if (mediaQuery.matches) {

          // Show/hide headers.
          tabsTitles.show();
          accordionTitles.hide();

          // Destroy accordion if it exists.
          if (accordionActive.length > 0) {
            accordionElement.accordion('destroy');
            createTabs();
          } else {
            createTabs();
          }

          // If small screen.
        } else {

          // Show/hide headers.
          tabsTitles.hide();
          accordionTitles.show();

          // Destroy tabs if they exist.
          if (tabsActive.length > 0) {
            tabsElement.tabs('destroy');
            createAccordion();
          } else {
            createAccordion();
          }

          // Remove style elements for
          tabsElement.removeClass('layout-left-30-70');
        }
      }

      function createAccordion() {
        accordionElement.accordion({
          header: ".js-accordion-header",
          active: false,
          collapsible: true,

          beforeActivate: function beforeActivate(event, ui) {
            // The accordion believes a panel is being opened
            if (ui.newHeader[0]) {
              var currHeader = ui.newHeader;
              var currContent = currHeader.next('.ui-accordion-content');
              // The accordion believes a panel is being closed
            } else {
              var currHeader = ui.oldHeader;
              var currContent = currHeader.next('.ui-accordion-content');
            }
            // Since we've changed the default behavior, this detects the actual status
            var isPanelSelected = currHeader.attr('aria-selected') == 'true';

            // Toggle the panel's header
            currHeader.toggleClass('ui-corner-all', isPanelSelected).toggleClass('ui-accordion-header-active ui-state-active ui-corner-top', !isPanelSelected).attr('aria-selected', (!isPanelSelected).toString());

            // Toggle the panel's icon
            currHeader.children('.ui-icon').toggleClass('ui-icon-triangle-1-e', isPanelSelected).toggleClass('ui-icon-triangle-1-s', !isPanelSelected);

            // Toggle the panel's content
            currContent.toggleClass('accordion-content-active', !isPanelSelected);
            if (isPanelSelected) {
              currContent.slideUp();
            } else {
              currContent.slideDown();
            }

            return false; // Cancels the default action
          }
        });
      }

      function createTabs() {
        tabsElement.tabs().addClass('ui-helper-clearfix layout-left-30-70');
      }

      tabAccordionSwitch(mediaQuery);

      // Select a link inside tablist and update url when it is clicked.
      // Passing context to prevent multiple bindings.
      $('.js-tablist a, .js-accordion-header', context).on('click', function () {
        // Update url.
        var href = $(this).attr("href");
        history.replaceState(null, null, href);
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_vertical_tabs.js.map
