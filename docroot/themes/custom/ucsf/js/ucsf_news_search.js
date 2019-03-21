/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function (context) {
      
      $(".advanced", context).click(function() {
        $(".search-filter__dropdown").toggle();
      });
      
    }
  };
  
})(jQuery);
