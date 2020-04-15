(($ => {

  // Wait for the document to be ready.
  $(() => {
    // wrapper for all header elements
    const $header = $('.combined-header-region');
    // toggle element for expanding the subnav
    const $subnavToggle = $('.header-subnav__title');
    const $subnavMenu = $('.header-subnav');
    // get id from collapsable subnav menu for accessibility controls
    const menuID = $('.header-subnav__menu').attr('id');

    // add class for css styling
    $header.has('.header-subnav-wrapper').addClass('combined-header-region--has-subnav');

    // Accessibility additions for mobile collapse subnav
    const addAriaControlls = () => {
      // set area controls and role on the title
      $subnavToggle.attr({"aria-controls":menuID, "role":"button", 'tabindex':'0'});
    }

    // Remove unnecessary Aria controls from desktop subnav
    const removeAriaControlls = () => {
      // set area controls and role on the title
      $subnavToggle.removeAttr('aria-expanded aria-controls role tabindex');
    }



    // Only execute subnav extend / collapse code in mobile
    const mobileDetect = (event) => {
      // Mobile
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        $subnavToggle.addClass('header-subnav__toggle')
        addAriaControlls();

        // toggle mobile subnav menu open and closed
        $subnavToggle.on('click', function(event) {
          const $this = $(this);
          $subnavMenu.toggleClass('header-subnav--expanded');

          if ($this.attr('aria-expanded') === 'true') {
            $this.attr('aria-expanded', 'false');
          } else {
            $this.attr('aria-expanded', 'true');
          }
          event.preventDefault();
        });

      // Desktop
      } else {
        removeAriaControlls();
        $subnavToggle.off('click');
        $subnavMenu.removeClass('header-subnav__menu--expanded');
        $subnavToggle.removeClass('header-subnav__toggle');
      }
    }

    // Use MatchMedia to ensure that subnav expand/collapse is only happening in mobile
    const mql = matchMedia('(max-width: 849px)');
    // Detect mobile on page load.
    mobileDetect(mql);
    // Watch to see if the page size changes.
    mql.addListener(mobileDetect);
  });

}))(jQuery);
