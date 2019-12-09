'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {

    // Exit if the admin toolbar is present.
    var toolbar = document.querySelector('#toolbar-administration');
    if (toolbar) {
      return;
    }

    // Toggle the .is-fixed class on scroll.
    var header = document.querySelector('.combined-header-region');
    var headerNav = document.querySelector('.header-region .header');
    var lastScroll = 0;

    // Get element height
    var headerNavHeight = headerNav.offsetHeight;

    // Set root variable of nav-height
    var root = document.documentElement;
    root.style.setProperty('--nav-height', headerNavHeight + "px");

    var headerTop = document.querySelector('.universal-header-region');
    var headerNavY = headerTop.offsetHeight;

    // Get the bottom Y coordinate of the Header.
    var headerBottomY = headerNavHeight + headerNavY;
    var headerFixedBottomY = 60 + headerNavY; // 60 is the height of the fixed-nav

    // calculate weather user is scrolling up or down
    // to reveal full navbar on up scroll
    window.addEventListener("scroll", function () {
      var currentScroll = window.pageYOffset;

      // make nav fixed only when user scrolled past navigation.
      // In other words, put the navigation back to its noraml
      // spot.
      if (currentScroll < headerNavY) {
        header.classList.remove('fixed-nav', 'fixed-nav--visible', 'fixed-nav--hidden');
        return;
      }

      // Ignore scrolling until 400px below so that it doesn't
      // mess with the navigation in its normal position.
      if (currentScroll > 400) {
        // If scrolling down
        if (currentScroll > lastScroll) {
          if (!header.classList.contains('fixed-nav--pre-hidden')) {
            header.classList.add('fixed-nav--hidden');
            header.classList.remove('fixed-nav--visible');
          }
          // If scrolling up
        } else {
          header.classList.remove('fixed-nav--hidden', 'fixed-nav--pre-hidden');
          header.classList.add('fixed-nav', 'fixed-nav--visible');
        }
      } else if (currentScroll > headerBottomY) {
        if (!header.classList.contains('fixed-nav')) {
          header.classList.add('fixed-nav', 'fixed-nav--pre-hidden');
        }
      } else if (currentScroll < headerFixedBottomY) {
        if (header.classList.contains('fixed-nav--pre-hidden')) {
          header.classList.remove('fixed-nav', 'fixed-nav--pre-hidden');
        }
      }

      lastScroll = currentScroll;
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_sticky_nav.js.map
