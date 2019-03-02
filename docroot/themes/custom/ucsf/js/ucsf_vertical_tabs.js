/* eslint-disable */
(function ($) {
  
  Drupal.behaviors.verticalTabs = {
    attach: function (context, settings) {
      
      $( ".js-tabs" ).tabs().addClass("ui-helper-clearfix");
      
    }
  };
  
})(jQuery);
