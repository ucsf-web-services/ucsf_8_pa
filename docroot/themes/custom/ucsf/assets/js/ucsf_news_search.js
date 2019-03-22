"use strict";

/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function attach(context) {

      $(".search-filter__advanced", context).click(function () {
        $(".search-filter__dropdown").toggleClass('js-search_filter__dropdown-open');
      });

      $(".search-filter__dropdown legend", context).click(function () {
        $(this).siblings(".fieldset-wrapper").toggleClass('js-search_filter__category-open');
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_news_search.js.map
