"use strict";

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
      trigger.attr("aria-expanded", value);
    };

    var $navbar = $(".mag-nav");
    var menuId = document.querySelector(".mag-menu").getAttribute("id");
    var $navToggle = $(".nav-toggle");
    $navToggle.attr("aria-controls", menuId);
    var $subnavToggle = $(".subnav-toggle");
    var $searchInput = $('#header-search');

    $navToggle.on("click", function (e) {
      var $this = $(this);
      $this.toggleClass("nav-toggle--active");
      $navbar.toggleClass("mag-nav--active");
      if ($this.attr("aria-expanded") === "false") {
        setAria($this, "true");
      } else {
        setAria($this, "false");
      }
    });

    var fixedBody = function fixedBody() {
      if ($navbar.hasClass("mag-nav--active")) {
        $('body').addClass('fixed');
      } else {
        $('body').removeClass('fixed');
      }

      $navToggle.on("click", function (e) {
        $('body').toggleClass('fixed');
      });
    };

    // Only execute subnav extend / collapse code in mobile
    var mobileDetect = function mobileDetect(event) {
      // Mobile
      if (event.matches) {
        fixedBody();
        $subnavToggle.on("click", function (e) {
          var $this = $(this);
          var $subnavMenuCollapsable = $this.closest(".mag-menu__item");
          $subnavMenuCollapsable.toggleClass("mag-menu__item--active");
          if ($this.attr("aria-expanded") === "false") {
            setAria($this, "true");
          } else {
            setAria($this, "false");
          }
        });
        // Desktop
      } else {
        $subnavToggle.off("click");
        // Remove unnecessary Aria controls from desktop subnav
        $subnavToggle.removeAttr("aria-expanded aria-controls");

        $('body').removeClass('fixed');

        $(".mag-mobile-search-reveal").click(function (e) {
          e.preventDefault();

          if ($(this).hasClass("active")) {
            $searchInput.focus();

            // if magazine menu is open minimize logo when search form is open
            if ($(".mag-nav--active").length > 0) {
              $(".header--logo").addClass("header--logo-minimized");
            }

            // change checked option default
            if ($("#allucsf").is(":checked")) {
              $("#allucsf").attr("checked", false);
              $("#newscenter").attr("checked", true);
            }
          } else {
            // if magazine menu is open close the form, return logo to regular size
            $(".header--logo").removeClass("header--logo-minimized");
          }
        });
      }
    };

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    var watchResize = function watchResize() {
      // Use MatchMedia to ensure that subnav expand/collapse is only happening in mobile
      var mql = matchMedia("(max-width: 850px)");
      // Detect mobile on page load.
      mobileDetect(mql);
      // Watch to see if the page size changes.
      mql.addListener(mobileDetect);
    };
    // Initialize.
    watchResize();
  });
})(jQuery);
//# sourceMappingURL=all.js.map
