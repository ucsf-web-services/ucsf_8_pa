/**
 * Animation of elements based on location in the viewport.
 */
(function () {

  // Elements that should not be animated with a fade
  const excludeElements = [
    '.explorer .element-fade',
    'dom-twocolumn-layout .element-fade',
    'dom-threecolumn-layout .element-fade',
    '.column1 .element-fade',
    '.column2 .element-fade',
    '.column3 .element-fade',
    '.field-taxonomy-banner-image .element-fade',
    '.paragraph--type--gallery  :not(.slick-active) .element-fade',
    '.article-twocol-banner .element-fade',
    '.article-medium-banner .element-fade'
  ];

  // element to be animated
  Drupal.behaviors.fadeInImages = {
    attach: function (context, settings) {
      // Get the NodeList of all the selectors matching elements on the page.
      const excludeSelectors = document.querySelectorAll(excludeElements.toString());
      excludeSelectors.forEach(element => element.classList.remove('element-fade'));

      const elementFade = document.querySelectorAll('.element-fade');
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
          entries.forEach(entry => {
            if (entry.intersectionRatio > 0) {
              // adds the class responsible for animation on the first viewport appearance
              entry.target.classList.add('element-fade--in-viewport');
              observer.unobserve(entry.target);
            }
          })
        })

        elementFade.forEach(element => {
          observer.observe(element);
        });
      }
    }
  }
})();
