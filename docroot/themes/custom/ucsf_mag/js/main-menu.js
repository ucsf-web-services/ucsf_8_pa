/**
 * Main navigation.
 */
(($) => {
  // Wait for the document to be ready.
  $(() => {
    /**
     * Set aria-expanded attribute value on element that triggers the submenu visibility
     * @param {jQuery Object} trigger
     * @param {String} value
     */
    const setAria = (trigger, value) => {
      trigger.attr("aria-expanded", value);
    };

    const $navbar = $(".mag-nav");
    const $menu = $(".mag-menu");
    const menuId = $menu.attr("id");
    const $navToggle = $(".nav-toggle");
    $navToggle.attr("aria-controls", menuId);
    const $subnavToggle = $(".subnav-toggle");

    $navToggle.on("click", function (e) {
      const $this = $(this);
      $this.toggleClass("nav-toggle--active");
      $navbar.toggleClass("mag-nav--active");

      if ($navbar.hasClass("mag-nav--active")) {
        $('body').addClass('has-fixed-nav');
      } else {
        $('body').removeClass('has-fixed-nav');
      }

      if ($this.attr("aria-expanded") === "false") {
        setAria($this, "true");
      } else {
        setAria($this, "false");
      }
    });

    // Close menu if user clicks on empty space below.
    $menu.bind('click', function(event) {
      if($(event.target).closest('.mag-menu__menu').length === 0) {
        $navToggle.removeClass("nav-toggle--active");
        $navbar.removeClass("mag-nav--active");
        setAria($navToggle, "false");
        $navToggle.focus();
      }
    });

    // Close menu when user tabbed out of it.
    $menu.on('focusout', function () {
      // Delay to ensure that new element is in focus.
      setTimeout(function () {
        // When browser can't find activeElement it returns <body> or null
        // which triggers the false positive for document.activeElement.closest('.mag-menu') === null
        if (
          document.activeElement === document.body ||
          document.activeElement === null
          ) {
          return;
        }

        // Close the menu if the currently focused el.
        //  is not inside the menu
        if (
          document.activeElement.closest('.combined-header-region') === null
        ) {
          console.log("out");
          $navToggle.removeClass("nav-toggle--active");
          $navbar.removeClass("mag-nav--active");
          setAria($navToggle, "false");
          $navToggle.focus()
        }
      }, 150);
    });

    // Only execute subnav extend / collapse code in mobile
    const toggleSubnav = (event) => {
      // Mobile
      if (event.matches) {
        $subnavToggle.on("click", function (e) {
          const $this = $(this);
          const $subnavMenuCollapsable = $this.closest(".mag-menu__item");
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
      }
    };

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    const watchResize = () => {
      // Use MatchMedia to ensure that subnav expand/collapse is only happening in mobile
      const mql = matchMedia("(max-width: 969px)");
      // Detect mobile on page load.
      toggleSubnav(mql);
      // Watch to see if the page size changes.
      mql.addListener(toggleSubnav);
    }
    // Initialize.
    watchResize();
  });
})(jQuery);
