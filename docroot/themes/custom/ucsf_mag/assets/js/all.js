'use strict';

/**
 * Main navigation, Desktop.
 */
(function ($) {
  // Wait for the document to be ready.
  $(function () {
    /**
    * Set aria-expanded attribute value on element that triggers the submenu visibility
    * @param {jQuery Object} trigger
    * @param {String} value
    */
    var setAria = function setAria(trigger, value) {
      trigger.attr('aria-expanded', value);
    };

    var $navbar = $('.mag-nav');
    var menuId = document.querySelector('.mag-menu').getAttribute('id');
    var $navToggle = $('.nav-toggle');
    $navToggle.attr("aria-controls", menuId);
    var $subnavToggle = $('.subnav-toggle');

    $navToggle.on('click', function (e) {
      var $this = $(this);
      $this.toggleClass('nav-toggle--active');
      $navbar.toggleClass('mag-nav--active');
      if ($this.attr("aria-expanded") === "false") {
        setAria($this, "true");
      } else {
        setAria($this, "false");
      }
    });

    // Only execute subnav extend / collapse code in mobile
    var mobileDetect = function mobileDetect(event) {
      // Mobile
      if (event.matches) {
        $subnavToggle.on('click', function (e) {
          var $this = $(this);
          var $subnavMenuCollapsable = $this.closest('.mag-menu__item');
          $subnavMenuCollapsable.toggleClass('mag-menu__item--active');
          if ($this.attr("aria-expanded") === "false") {
            setAria($this, "true");
          } else {
            setAria($this, "false");
          }
        });
        // Desktop
      } else {
        $subnavToggle.off('click');
        // Remove unnecessary Aria controls from desktop subnav
        $subnavToggle.removeAttr('aria-expanded aria-controls');
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
//# sourceMappingURL=all.js.map
