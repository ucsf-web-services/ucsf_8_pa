(($ => {

  // Wait for the document to be ready.
  $(() => {

    // Exit if the admin toolbar is present.
    const toolbar = document.querySelector('#toolbar-administration');
    if (toolbar) {
      return;
    }

    // Toggle the .is-fixed class on scroll.
    const header = document.querySelector('.combined-header-region')
    const headerNav = document.querySelector('.header-region .header')
    let lastScroll = 0;

    // Get element height
    const headerNavHeight = headerNav.offsetHeight;

    // Set root variable of nav-height
    const root = document.documentElement;
    root.style.setProperty('--nav-height', headerNavHeight + "px")

    const headerTop = document.querySelector('.universal-header-region');
    const headerNavY = headerTop.offsetHeight;

    // Get the bottom Y coordinate of the Header.
    const headerBottomY = headerNavHeight + headerNavY;
    const headerFixedBottomY = 60 + headerNavY; // 60 is the height of the fixed-nav

    // calculate weather user is scrolling up or down
    // to reveal full navbar on up scroll
    window.addEventListener("scroll", () => {
      const currentScroll = window.pageYOffset;

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
}))(jQuery);
