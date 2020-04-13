'use strict';

(function ($) {

  // Wait for the document to be ready.
  $(function () {
    var header = document.querySelector('.combined-header-region');
    var headerSubnav = document.querySelector('.header-subnav-wrapper');
    // add class for css styling
    $(header).has(headerSubnav).addClass("combined-header-region--has-subnav");
  });
})(jQuery);
//# sourceMappingURL=ucsf_header_region_subnav.js.map
