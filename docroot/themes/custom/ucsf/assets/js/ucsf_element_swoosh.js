'use strict';

/**
* Animation of elements based on location in the viewport.
*/

(function () {
  Drupal.behaviors.elementSwoosh = {
    attach: function attach(context, settings) {
      if ('IntersectionObserver' in window) {
        // element to be animated
        var elementSwoosh = document.querySelector('.element-swoosh');

        var observer = new IntersectionObserver(function (entry, observer) {
          var targetElement = entry[0].target;
          if (entry[0].intersectionRatio > 0) {
            // adds the class responsible for animation on the first viewport appearance
            targetElement.classList.add('element-swoosh--in-viewport');
            observer.unobserve(targetElement);
          }
        });

        observer.observe(elementSwoosh);
      }
    }

  };
})();
//# sourceMappingURL=ucsf_element_swoosh.js.map
