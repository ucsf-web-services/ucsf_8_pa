'use strict';
// TODO: Throttle the scrolling to increase performance

(($ => {

  /**
   * Detect if two elements are overlapping.
   *
   * @param {HTMLElement} element1
   * @param {HTMLElement} element2
   */
  function overlaps(element1, element2) {
    const a = {
      x1: element1.getBoundingClientRect().top,
      y1: element1.getBoundingClientRect().left,
      x2: element1.getBoundingClientRect().bottom,
      y2: element1.getBoundingClientRect().right,
    };
    const b = {
      x1: element2.getBoundingClientRect().top,
      y1: element2.getBoundingClientRect().left,
      x2: element2.getBoundingClientRect().bottom,
      y2: element2.getBoundingClientRect().right,
    };

    // no horizontal overlap
    if (a.x1 >= b.x2 || b.x1 >= a.x2) return false;

    // no vertical overlap
    if (a.y1 >= b.y2 || b.y1 >= a.y2) return false;

    return true;
  };

  /**
   * Check if a target element is overlapping with any elements in a given array.
   *
   * @param {HTMLElement} target
   * @param {NodeList} elements
   */
  function overlapTarget(target, elements) {
    let overlap = false;

    for (const element of elements) {
      overlap = overlaps(target, element);
      // Exit the loop if an overlap is found.
      if (overlap === true) break;
    };

    return overlap;
  };

  // Wait for the document to be ready.
  $(() => {
    // Get the fixed share icons to detect collision against
    const fixed = document.querySelector('.article-meta-share');
    // The CSS selectors for anything that the share icons can collide with.
    const selectors = [
      '.half-image-left-full',
      '.half-image-left',
      '.full-bleed-image',
      '.callout-left',
      '.layout-columns__1',
      '.layout-columns__2',
      '.layout-columns__3',
      '.layout-columns__4',
      '.blockquote-full-width',
      '.blockquote--half-left',
      '.paragraph--type--gallery',
    ];

    // Get the NodeList of all the selectors matching elements on the page.
    const elements = document.querySelectorAll(selectors.toString());

    // Set the starting state for if there is anything colliding
    let overlapState = null;

    /**
     * Hide element if overlap is detected.
     */
    const hideOnOverlap = () => {
      // Check if the fixed box collides with any other boxes.
      const overlap = overlapTarget(fixed, elements);

      // Check if the state of the overlap has changed.
      if (overlapState !== overlap) {
        overlapState = overlap;

        // Add the hidden class to the fixed box if overlapping.
        if (overlapState == true) {
          fixed.classList.add('article-meta-share--hidden');
        } else {
          fixed.classList.remove('article-meta-share--hidden');
        }
      };
    }

    // Use MatchMedia to ensure that collision events are only happening in Desktop
    const mql = matchMedia('(min-width: 1050px)');
    // On page load, check if desktop and listen to scroll event.
    if (mql.matches) {
      window.addEventListener("scroll", hideOnOverlap);
    }

    // When screen size changes, add or remove the scroll listener.
    mql.addListener(event => {
      if (event.matches) {
        window.addEventListener("scroll", hideOnOverlap);
      } else {
        window.removeEventListener("scroll", hideOnOverlap);
      }
    })

  });

}))(jQuery);
