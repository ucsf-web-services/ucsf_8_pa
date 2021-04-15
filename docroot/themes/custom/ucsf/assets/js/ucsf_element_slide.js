'use strict';

/**
 * Slide in callout boxes and blockquotes from right or left based on location
 * in the viewport.
 */

(function () {

  // Detect if article is not in viewport.
  var quoteDetect = function quoteDetect() {
    if ('IntersectionObserver' in window) {
      // element to be animated
      var quote = document.querySelectorAll('.blockquote--half-right, .blockquote--half-left, .callout-right, .callout-left');

      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {

          if (entry.intersectionRatio > 0) {
            // adds the class responsible for animation on the first viewport appearance
            entry.target.classList.add('in-viewport');
            observer.unobserve(entry.target);
          }
        });
      });

      quote.forEach(function (element) {
        observer.observe(element);
      });
    }
  };

  // Use MatchMedia to ensure that slide events are only happening in Tablet/Desktop
  var mql = matchMedia('(min-width: 769px)');
  // On page load, check if desktop and listen to scroll event.
  if (mql.matches) {
    quoteDetect();
  }
})();
//# sourceMappingURL=ucsf_element_slide.js.map
