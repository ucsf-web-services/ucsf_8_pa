(($ => {

  // Wait for the document to be ready.
  $(() => {

    // Exit if the admin toolbar is present.
    const toolbar = document.querySelector('#toolbar-administration');
    if (toolbar) {
      return;
    }

    const header = document.querySelector('.combined-header-region');
    const headerNav = document.querySelector('.header-region .header');
    const headerTop = document.querySelector('.universal-header-region');
    const headerNavY = headerTop.offsetHeight;
    const root = document.documentElement;

    // Calculate the Nav Height and set a CSS variable.
    const setNavHeight = () => {
      // Get element height
      const headerNavHeight = headerNav.offsetHeight;
      // Set root variable of nav-height
      root.style.setProperty('--nav-height', headerNavHeight + "px");
    }

    // Set the initial nav height.
    setNavHeight()

    // Get the bottom Y coordinate of the Header.
    const headerBottomY = headerNav.offsetHeight + headerNavY;
    const headerFixedBottomY = 60 + headerNavY; // 60 is the height of the fixed-nav

    // Recalculate css variable on screen resize for the Nav height
    const mql = matchMedia('(min-width: 850px)');
    mql.addListener(setNavHeight);

    // In Mobile, remove all the fixed classes when toggling the menu.
    const mobileToggle = document.querySelector('.slicknav_btn');
    mobileToggle.addEventListener('click', () => {
      header.classList.remove('fixed-nav', 'fixed-nav--visible', 'fixed-nav--hidden', 'fixed-nav--pre-hidden');
    });

    // Toggle the .is-fixed class on scroll.
    let lastScroll = 0;

    // calculate weather user is scrolling up or down
    // to reveal full navbar on up scroll
    window.addEventListener("scroll", () => {
      const currentScroll = window.pageYOffset;

      // make nav fixed only when user scrolled past navigation.
      // In other words, put the navigation back to its normal spot.
      if (currentScroll < headerNavY) {
        header.classList.remove('fixed-nav', 'fixed-nav--visible', 'fixed-nav--hidden', 'fixed-nav--pre-hidden');
        return;
      }

      // Ignore scrolling until 400px below so that it doesn't
      // mess with the navigation in its normal position.
      if (currentScroll > 400) {
        // Ignore if the scroll position is the same.
        // If scrolling down
        if (currentScroll > lastScroll) {
          if (!header.classList.contains('fixed-nav--pre-hidden')) {
            header.classList.remove('fixed-nav--visible');
              if (!header.classList.contains('fixed-nav')) {
                header.classList.add('fixed-nav', 'fixed-nav--pre-hidden');
              } else {
                header.classList.add('fixed-nav--hidden');
              }

              header.classList.remove('fixed-nav--visible');
          }
        // If scrolling up
        } else if (currentScroll < lastScroll) {
          header.classList.remove('fixed-nav--hidden', 'fixed-nav--pre-hidden');
          header.classList.add('fixed-nav', 'fixed-nav--visible');
        }

        // In between the normal position and the height where the sticky nav
      // should start displaying.
      } else if (currentScroll > headerBottomY) {
        if (!header.classList.contains('fixed-nav')) {
          header.classList.add('fixed-nav', 'fixed-nav--pre-hidden');
        }

        // Return to normal position when having never shown a sticky nav.
        // If a pre-hidden class is applied (and assumed fixed-nav) then the
        // navigation is vertically smaller than the normal navigation. So we need
        // to wait until we have travelled to the height of the sticky nav so that
        // we can then remove the fixed-nav and allow it to animate back to its
        // normal height.
      } else if (currentScroll < headerFixedBottomY) {
        if (header.classList.contains('fixed-nav--pre-hidden')) {
          header.classList.remove('fixed-nav', 'fixed-nav--pre-hidden');
        }
      }

      lastScroll = currentScroll;
    });

  });
}))(jQuery);
