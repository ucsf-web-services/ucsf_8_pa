'use strict';

/**
* Animation of elements based on location in the viewport.
*/
(function () {
  // element to be animated
  var elementFade = document.querySelectorAll('.element-fade');

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
})();
//# sourceMappingURL=ucsf_element_fade.js.map
