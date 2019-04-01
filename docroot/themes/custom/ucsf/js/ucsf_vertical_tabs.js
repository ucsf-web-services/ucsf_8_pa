/* eslint-disable */
(function ($) {
  
  Drupal.behaviors.verticalTabs = {
    attach: function (context, settings) {
      
      
      var mediaQuery = window.matchMedia('(min-width: 850px)');
      
      mediaQuery.addListener(tabAccordionSwitch);
      
      function tabAccordionSwitch(mediaQuery) {
        
        // If large screen.
        if (mediaQuery.matches) {
          
          // Create tabs.
          $(".js-tabs").tabs().addClass("ui-helper-clearfix");
  
        // If small screen.
        } else {
  
          // Destroy tabs if they exist.
          if ($('.ui-tabs').length > 0) {
            $(".js-tabs").tabs('destroy')
          }
        }
      }
      
      tabAccordionSwitch(mediaQuery);
      
    }
  };
  
})(jQuery);
