'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {
    // wrapper for all header elements
    var $header = $('.combined-header-region');
    // toggle element for expanding the subnav
    var $subnavToggle = $('.header-subnav__title');
    var $subnavMenu = $('.header-subnav');
    // get id from collapsable subnav menu for accessibility controls
    var menuID = $('.header-subnav__menu').attr('id');

    // add class for css styling
    $header.has('.header-subnav-wrapper').addClass('combined-header-region--has-subnav');

    var toggleMenu = function toggleMenu(event) {
      var $this = $(this);
      $subnavMenu.toggleClass('header-subnav--expanded');

      if ($this.attr('aria-expanded') === 'true') {
        $this.attr('aria-expanded', 'false');
      } else {
        $this.attr('aria-expanded', 'true');
      }
      event.preventDefault();
    };

    // Only execute subnav extend / collapse code in mobile
    var mobileDetect = function mobileDetect(event) {
      // Mobile
      if (event.matches) {
        $subnavToggle.attr('aria-expanded', 'false');
        $subnavToggle.addClass('header-subnav__toggle');
        // set area controls and role on the title
        $subnavToggle.attr({ "aria-controls": menuID, "role": "button", 'tabindex': '0' });

        // toggle mobile subnav menu open and closed
        $subnavToggle.on('click', toggleMenu);
        $subnavToggle.on('keypress', function (event) {
          // If Enter key was pressed
          if (event.which === 13) {
            toggleMenu(event);
          }
        });

        // Desktop
      } else {
        $subnavToggle.off('click');
        $subnavToggle.off('keypress');
        $subnavMenu.removeClass('header-subnav--expanded');
        $subnavToggle.removeClass('header-subnav__toggle');
        // Remove unnecessary Aria controls from desktop subnav
        $subnavToggle.removeAttr('aria-expanded aria-controls role tabindex');
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
