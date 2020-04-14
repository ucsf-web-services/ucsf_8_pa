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

    // Only execute subnav extend / collapse code in mobile
    var mobileDetect = function mobileDetect(event) {
      console.log("made it");

      // Mobile
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        console.log("mobile");

        // toggle mobile subnav menu open and closed
        $subnavToggle.on('click', function (event) {
          var $this = $(this);
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
        console.log("desktop");
        $subnavToggle.removeAttr('aria-expanded');
        $subnavToggle.off('click');
      }
    };

    // Use MatchMedia to ensure that subnav expand/collapse is only happening in mobile
    var mql = matchMedia('(max-width: 849px)');
    // Detect mobile on page load.
    mobileDetect(mql);
    // Watch to see if the page size changes.
    mql.addListener(mobileDetect);
  });
})(jQuery);
//# sourceMappingURL=ucsf_header_region_subnav.js.map
