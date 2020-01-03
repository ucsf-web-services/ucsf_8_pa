'use strict';

// TODO: Trigger hiding on page load to start the initial visibility
// TODO: Use MatchMedia to ensure that collision events are only happening in Desktop
// TODO: Throttle the scrolling to increase performance

(function ($) {

  /**
   * Detect if two elements are overlapping.
   *
   * @param {HTMLElement} element1
   * @param {HTMLElement} element2
   */
  function overlaps(element1, element2) {
    var a = {
      x1: element1.getBoundingClientRect().top,
      y1: element1.getBoundingClientRect().left,
      x2: element1.getBoundingClientRect().bottom,
      y2: element1.getBoundingClientRect().right
    };
    var b = {
      x1: element2.getBoundingClientRect().top,
      y1: element2.getBoundingClientRect().left,
      x2: element2.getBoundingClientRect().bottom,
      y2: element2.getBoundingClientRect().right

      // no horizontal overlap
    };if (a.x1 >= b.x2 || b.x1 >= a.x2) return false;

    // no vertical overlap
    if (a.y1 >= b.y2 || b.y1 >= a.y2) return false;

    return true;
  }

  /**
   * Check if a target element is overlapping with any elements in a given array.
   *
   * @param {HTMLElement} target
   * @param {NodeList} elements
   */
  function overlapTarget(target, elements) {
    var overlap = false;

    var _iteratorNormalCompletion = true;
    var _didIteratorError = false;
    var _iteratorError = undefined;

    try {
      for (var _iterator = elements[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
        var element = _step.value;

        overlap = overlaps(target, element);
        // Exit the loop if an overlap is found.
        if (overlap === true) break;
      }
    } catch (err) {
      _didIteratorError = true;
      _iteratorError = err;
    } finally {
      try {
        if (!_iteratorNormalCompletion && _iterator.return) {
          _iterator.return();
        }
      } finally {
        if (_didIteratorError) {
          throw _iteratorError;
        }
      }
    }

    return overlap;
  }

  // Wait for the document to be ready.
  $(function () {
    // Get the fixed share icons to detect collision against
    var fixed = document.querySelector('.article-meta-share');
    // The CSS selectors for anything that the share icons can collide with.
    var selectors = ['.article-feature-overlay-image', '.half-image-left-full', '.half-image-left', '.full-bleed-image', '.callout-left', '.layout-columns__1', '.layout-columns__2', '.layout-columns__3', '.layout-columns__4', '.blockquote-full-width', '.blockquote--half-left', '.paragraph--type--gallery', '.recommended-articles', '.footer-region'];

    // Get the NodeList of all the selectors matching elements on the page.
    var elements = document.querySelectorAll(selectors.toString());

    // Set the starting state for if there is anything colliding
    var overlapState = null;

    window.addEventListener("scroll", function () {
      console.log('test');
      // Check if the fixed box collides with any other boxes.
      var overlap = overlapTarget(fixed, elements);

      // Check if the state of the overlap has changed.
      if (overlapState !== overlap) {
        overlapState = overlap;

        // Add the hidden class to the fixed box if overlapping.
        if (overlapState == true) {
          fixed.classList.add('article-meta-share--hidden');
        } else {
          fixed.classList.remove('article-meta-share--hidden');
        }
      }
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_element_overlap_detection.js.map
