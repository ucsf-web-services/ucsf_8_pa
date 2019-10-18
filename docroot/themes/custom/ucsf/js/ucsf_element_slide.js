/**
 * Slide in callout boxes and blockquotes from right or left based on location
 * in the viewport.
 */

(function () {
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
})();
