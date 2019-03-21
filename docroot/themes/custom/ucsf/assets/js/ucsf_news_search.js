"use strict";

/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function attach(context) {

      $(".advanced", context).click(function () {
        $(".search-filter__dropdown").toggle();
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_news_search.js.map
