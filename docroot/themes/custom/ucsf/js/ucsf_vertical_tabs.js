/* eslint-disable */
(function ($) {
  
  Drupal.behaviors.verticalTabs = {
    attach: function (context, settings) {
      
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
            accordionElement.accordion('destroy')
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
            tabsElement.tabs('destroy')
            createAccordion()
          } else {
            createAccordion()
          }

          // Remove style elements for
          tabsElement.removeClass('layout-left-30-70');
        }
      }
      
      function createAccordion() {
        accordionElement.accordion({
          header: "h3"
        })
      }
  
      function createTabs() {
        tabsElement.tabs().addClass(
          'ui-helper-clearfix layout-left-30-70'
        );
      }

      tabAccordionSwitch(mediaQuery);
      
    }
  };
  
})(jQuery);
