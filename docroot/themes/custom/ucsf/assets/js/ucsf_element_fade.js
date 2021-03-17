'use strict';

/**
 * Animation of elements based on location in the viewport.
 */
(function () {

  // Elements that should not be animated with a fade
  var excludeElements = ['.explorer .element-fade', 'dom-twocolumn-layout .element-fade', 'dom-threecolumn-layout .element-fade', '.column1 .element-fade', '.column2 .element-fade', '.column3 .element-fade', '.field-taxonomy-banner-image .element-fade', '.paragraph--type--gallery  :not(.slick-active) .element-fade'];

  // element to be animated
  Drupal.behaviors.fadeInImages = {
    attach: function attach(context, settings) {
      // Get the NodeList of all the selectors matching elements on the page.
      var excludeSelectors = document.querySelectorAll(excludeElements.toString());
      excludeSelectors.forEach(function (element) {
        return element.classList.remove('element-fade');
      });

      var elementFade = document.querySelectorAll('.element-fade');
      if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.intersectionRatio > 0) {
              // adds the class responsible for animation on the first viewport appearance
              entry.target.classList.add('element-fade--in-viewport');
              observer.unobserve(entry.target);
            }
          });
        });

        elementFade.forEach(function (element) {
          observer.observe(element);
        });
      }
    }
  };
})();
//# sourceMappingURL=ucsf_element_fade.js.map
