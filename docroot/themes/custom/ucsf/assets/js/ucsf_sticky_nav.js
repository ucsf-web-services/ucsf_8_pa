'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {

    // Exit if the admin toolbar is present.
    var toolbar = document.querySelector('#toolbar-administration');
    if (toolbar) {
      return;
    }

    var header = document.querySelector('.combined-header-region');
    var headerNav = document.querySelector('.header-region');
    var headerTop = document.querySelector('.universal-header-region');
    var root = document.documentElement;

    // Calculate the Nav Height and set a CSS variable.
    var setNavHeight = function setNavHeight() {
      // Get element height
      var headerNavHeight = headerNav.offsetHeight;
      // Set root variable of nav-height
      root.style.setProperty('--nav-height', headerNavHeight + "px");
    };

    // Set the initial nav height.
    setNavHeight();

    // Recalculate css variable on screen resize for the Nav height
    var mql = matchMedia('(min-width: 850px)');
    mql.addListener(setNavHeight);

    // In Mobile, remove all the fixed classes when toggling the menu.
    var mobileToggle = document.querySelector('.slicknav_btn');
    mobileToggle.addEventListener('click', function () {
      header.classList.remove('fixed-nav', 'fixed-nav--visible', 'fixed-nav--hidden');
    });

    // Toggle the .is-fixed class on scroll.
    var lastScroll = 0;

    // calculate weather user is scrolling up or down
    // to reveal full navbar on up scroll
    window.addEventListener("scroll", function () {
      var currentScroll = window.pageYOffset;

      // make nav fixed only when user scrolled past navigation.
      // In other words, put the navigation back to its normal spot.
      if (currentScroll < headerTop.offsetHeight) {
        header.classList.remove('fixed-nav', 'fixed-nav--visible', 'fixed-nav--hidden');
        return;
      }

      // Ignore scrolling until 400px below so that it doesn't
      // mess with the navigation in its normal position.
      if (currentScroll > 400) {
        // If scrolling down
        if (currentScroll > lastScroll) {
          header.classList.add('fixed-nav--hidden');
          header.classList.remove('fixed-nav--visible');
          // If scrolling up
        } else {
          header.classList.remove('fixed-nav--hidden');
          header.classList.add('fixed-nav', 'fixed-nav--visible');
        }
      }

      lastScroll = currentScroll;
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_sticky_nav.js.map
