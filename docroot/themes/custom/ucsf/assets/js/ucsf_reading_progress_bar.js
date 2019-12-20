'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {
    // The progress bar which will be updated on scroll.
    var progressBar = document.querySelector('.progress-bar');

    // The article used to detect scroll position.
    var article = document.querySelector('.main-content');

    // Get the Y position for where the article sits on the page.
    var rect = article.getBoundingClientRect();
    var articleTop = rect.top + document.documentElement.scrollTop;

    // Get half the screen height.
    var screenHalfHeight = window.innerHeight / 2;

    var scrollCallback = function scrollCallback() {
      // Get the current scroll position within the article.
      var scrollY = window.scrollY - articleTop;

      // Get the length of the article minus half of the screen height so that
      // an article will be completed reading once the bottom edge of it is half
      // way up the screen.
      var articleHeight = article.clientHeight - screenHalfHeight;

      // Get the average progress ratio of article scrolled through.
      var progress = scrollY / articleHeight;

      // Ensure that the progress bar never goes lower than zero or higher than 1
      if (progress > 1) {
        progress = 1;
      } else if (progress < 0) {
        progress = 0;
      }

      // Set the progress bar percent.
      progressBar.setAttribute('value', progress);
    };

    // throttle the scroll event to reduce, performance issues
    window.addEventListener("scroll", $.throttle(50, scrollCallback));
  });
})(jQuery);
//# sourceMappingURL=ucsf_reading_progress_bar.js.map
