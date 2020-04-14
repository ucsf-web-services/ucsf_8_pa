(($ => {

  // Wait for the document to be ready.
  $(() => {
    // wrapper for all header elements
    const $header = $('.combined-header-region');
    // toggle element for expanding the subnav
    const $subnavToggle = $('.header-subnav__toggle');
    // add class for css styling
    $header.has('.header-subnav-wrapper').addClass('combined-header-region--has-subnav');

    // Only execute subnav extend / collapse code in mobile
    const mobileDetect = (event) => {
      console.log("made it");

      // Mobile
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        console.log("mobile");

        // toggle mobile subnav menu open and closed
        $subnavToggle.on('click', function(event) {
          const $this = $(this);
          $('.header-subnav__menu').toggleClass('header-subnav__menu--expanded');

          if ($this.attr('aria-expanded') === 'true') {
            $this.attr('aria-expanded', 'false');
          } else {
            $this.attr('aria-expanded', 'true');
          }
          event.preventDefault();
        });

      // Desktop
      } else {
        console.log("desktop")
        $subnavToggle.removeAttr('aria-expanded');
        $subnavToggle.off('click');
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
