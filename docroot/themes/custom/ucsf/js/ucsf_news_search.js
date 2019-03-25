/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function (context) {
      
      $(".search-filter__advanced", context).click(function() {
        $(".search-filter__dropdown").toggleClass('js-search_filter__dropdown-open');
        $(".search-filter__top").toggleClass('js-search_filter__top-open');
        $(".fieldset-wrapper").removeClass('js-search_filter__category-open');
      });

      $(".search-filter__dropdown legend", context).click(function() {
        $(this).siblings(".fieldset-wrapper").toggleClass('js-search_filter__category-open');
      });

    }
  };
  
})(jQuery);
