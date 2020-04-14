(($ => {

  // Wait for the document to be ready.
  $(() => {
    // wrapper for all header elements
    const $header = $('.combined-header-region');
    // toggle element for expanding the subnav
    const $subnavToggle = $('.header-subnav__toggle');
    // add class for css styling
    $header.has('.header-subnav-wrapper').addClass('combined-header-region--has-subnav');

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

    const toggleSubnav = (event) => {
      console.log("made it");
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        console.log("mobile");

      } else {
        console.log("desktop")
        $subnavToggle.removeAttr('aria-expanded');
      }
    }

    // Use MatchMedia to ensure that collision events are only happening in Desktop
    const mql = matchMedia('(max-width: 849px)');
    toggleSubnav(mql);
    mql.addListener(toggleSubnav);

  });
}))(jQuery);
console.log('test7');
