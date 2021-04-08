/**
 * Slide in callout boxes and blockquotes from right or left based on location
 * in the viewport.
 */

(function () {

  // Detect if article is not in viewport.
  const  quoteDetect = () => {
    if ('IntersectionObserver' in window) {
      // element to be animated
      const quote = document.querySelectorAll('.blockquote--half-right, .blockquote--half-left, .callout-right, .callout-left');

      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {

          if (entry.intersectionRatio > 0) {
            // adds the class responsible for animation on the first viewport appearance
            entry.target.classList.add('in-viewport');
            observer.unobserve(entry.target);
          }
        })
      })

      quote.forEach(element => {
        observer.observe(element);
      });
    }
  }

  // Use MatchMedia to ensure that slide events are only happening in Tablet/Desktop
  const mql = matchMedia('(min-width: 769px)');
  // On page load, check if desktop and listen to scroll event.
  if (mql.matches) {
    quoteDetect();
  }
})();
