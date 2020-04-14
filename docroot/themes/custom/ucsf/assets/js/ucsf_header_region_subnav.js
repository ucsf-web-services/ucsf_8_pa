'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {
    // wrapper for all header elements
    var $header = $('.combined-header-region');
    // toggle element for expanding the subnav
    var $subnavToggle = $('.header-subnav__toggle');
    // add class for css styling
    $header.has('.header-subnav-wrapper').addClass('combined-header-region--has-subnav');

    // toggle mobile subnav menu open and closed
    $subnavToggle.on('click', function (e) {
      var $this = $(undefined);
      $('.header-subnav__menu').toggleClass('header-subnav__menu--expanded');
      console.log($this.text());

      if ($this.attr('aria-expanded') === 'true') {
        $this.attr('aria-expanded', 'false');
      } else {
        $this.attr('aria-expanded', 'true');
      }
      e.preventDefault();
    });

    var toggleSubnav = function toggleSubnav(event) {
      console.log("made it");
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        console.log("mobile");
      } else {
        console.log("desktop");
        $subnavToggle.removeAttr('aria-expanded');
      }
    };

    // Use MatchMedia to ensure that collision events are only happening in Desktop
    var mql = matchMedia('(max-width: 849px)');
    toggleSubnav(mql);
    mql.addListener(toggleSubnav);
  });
})(jQuery);
console.log('test4');
//# sourceMappingURL=ucsf_header_region_subnav.js.map
