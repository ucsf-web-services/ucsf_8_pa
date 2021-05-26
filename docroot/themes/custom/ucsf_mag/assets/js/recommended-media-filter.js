'use strict';

(function ($) {
  // Wait for the document to be ready.
  $(function () {
    // Apply a class to filter option based on the current query value.
    var theHref = window.location.href;
    var $optionAll = $('[href="?"]');
    var options = ['414451', '414601', '414606'];
    if (theHref.indexOf('category') < 0) {
      $optionAll.addClass('active');
    }

    options.forEach(function (option) {
      if (theHref.indexOf(option) > 0) {
        $('.' + option).addClass('active');
      }
    });
  });
})(jQuery);
//# sourceMappingURL=recommended-media-filter.js.map
