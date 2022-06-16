'use strict';

// scrollama documentation and tutoreal
// https://www.npmjs.com/package/scrollama
// https://www.youtube.com/watch?v=d7wTA9F-l8c

// Scrollama event triggers
// https://www.youtube.com/watch?v=rXHtHLiBWhY

var galleries = document.querySelectorAll('.scrolly-gallery');

galleries.forEach(function (gallery) {
  // instantiate the scrollama library.
  var scroller = scrollama();

  var items = gallery.querySelectorAll('.scrolly-gallery__item');
  var textItems = gallery.querySelectorAll('.scrolly-gallery__text');
  var images = gallery.querySelectorAll('.scrolly-gallery__bg img');

  function removeLazyLoading() {
    setTimeout(function () {
      images.forEach(function (image) {
        image.removeAttribute('loading');
        image.style.animation = 'none';
      });
    }, 600);
  }
  removeLazyLoading();

  function setGalleryWidth() {
    gallery.style.setProperty('--scrolly-gallery-width', gallery.offsetWidth + 'px');
  }

  function removeCurrent() {
    items.forEach(function (item) {
      return item.classList.remove('scrolly-gallery__item--current');
    });
  }

  function removeAnimateOut() {
    textItems.forEach(function (item) {
      return item.classList.remove('scrolly-gallery__text--out');
    });
  }

  function stepEnter(response) {
    gallery.dataset.scrollyGalleryDirection = response.direction;
    // 0 is the first stem in an item and 2 is the last
    var position = response.direction === 'down' ? 0 : 2;
    if (response.index % 3 === position) {
      removeCurrent();
      response.element.parentElement.classList.add('scrolly-gallery__item--current');
    }

    // Ensure the first item gets the current class.
    if (response.direction === 'down' && response.index === 1) {
      response.element.parentElement.classList.add('scrolly-gallery__item--current');
    }

    if (response.element.classList.contains('scrolly-gallery__text')) {
      removeAnimateOut();
      response.element.classList.add('scrolly-gallery__text--active');
    }
  }

  function stepExit(response) {
    if (response.element.classList.contains('scrolly-gallery__text')) {
      response.element.classList.remove('scrolly-gallery__text--active');
      response.element.classList.add('scrolly-gallery__text--out');
    }

    // Remove classes when exiting the gallery.
    var exitFirst = response.direction === 'up' && response.index <= 1;
    var exitLast = response.direction === 'down' && response.index >= items.length * 3 - 1;
    if (exitFirst || exitLast) {
      setTimeout(function () {
        removeCurrent();
        removeAnimateOut();
      }, 500);
    }
  }

  function init() {
    // Set the initial gallery width:
    setGalleryWidth();
    window.addEventListener('resize', setGalleryWidth);

    // setup the instance, pass callback functions
    scroller.setup({
      step: gallery.querySelectorAll('.scrolly-gallery__step')
      // debug: true
    }).onStepEnter(stepEnter).onStepExit(stepExit);
  }

  // Use MatchMedia to ensure that collision events are only happening in Desktop
  var mql = matchMedia('(min-width: 1050px)');
  // On page load, check if desktop and listen to scroll event.
  if (mql.matches) {
    removeLazyLoading();
    init();
  }

  // When screen size changes, add or remove the scroll listener.
  mql.addListener(function (event) {
    if (event.matches) {
      init();
    } else {
      window.removeEventListener('resize', setGalleryWidth);
      scroller.destroy();
    }
  });
});
//# sourceMappingURL=ucsf_parallax_image_with_text_overlay.js.map
