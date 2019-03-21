/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function (context) {
      
      $(".advanced", context).click(function() {
        $(".search_dropdown").toggle();
      });
      
    }
  };
  
})(jQuery);
