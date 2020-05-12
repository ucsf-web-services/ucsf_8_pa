'use strict';

(function ($) {
  // Wait for the document to be ready.
  $(function () {
    var $findMorePr = $('.redirect-to-advanced');
    // Create a query pass it along to /news/filter and conduct search by term
    $findMorePr.one('click', function (e) {
      e.preventDefault();
      var $searchTerm = $('.search-form__input').val();
      var theHref = $findMorePr.attr("href");
      // Pass search term into url in order to populate search input and conduct a search
      var newHref = theHref.replace('\/news\/filter?', '/news/filter?combine=' + $searchTerm + '&');
      // Manually redirect to the news filter page
      window.location.href = newHref;
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_press_releases_tab.js.map
