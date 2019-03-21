/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function (context) {
      
      $(".search-filter__advanced", context).click(function() {
        $(".search-filter__dropdown").toggleClass('js-search_flter__dropdown-open');
      });
      
    }
  };
  
})(jQuery);
