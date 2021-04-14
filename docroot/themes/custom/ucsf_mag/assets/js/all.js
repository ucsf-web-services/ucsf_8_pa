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
    console.log(menuId);
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

    var magazineDesktopMenu = function magazineDesktopMenu() {
      var menuButton = $('magazine-nav__toggle, .magazine-submenu__toggle, .menu-item-close--magazine');
      menuButton.each(function () {
        $(this).on('click', function (event) {
          event.preventDefault();
        });
      });

      // Toggle open the sub panels on main nav button click.
      $('.magazine-nav__toggle').on('click touchstart', function (e) {
        var $this = $(this);
        var $parent = $this.parent('.magazine-nav__submenu-wrapper, .search');
        var $otherPanels = $parent.siblings();
        $parent.toggleClass('menu-item-open');
        $otherPanels.removeClass('menu-item-open');
        // close previously opened level-1 submenues
        $otherPanels.find('.magazine-submenu__toggle').parent('.menu-item--expanded').removeClass('menu-item-open');

        // Set aria attribute based on panel visibility.
        if ($parent.hasClass('menu-item-open')) {
          setAria($this, 'true');
          setAria($('.menu-item-close--magazine'), 'true');
          // reset area of previously opened panels.
          $otherPanels.find('.magazine-nav__toggle').attr('aria-expanded', false);
        } else {
          setAria($this, 'false');
          setAria($('.menu-item-close--magazine'), 'false');
        }
      });

      // The "x" button inside menu panel.
      $('.menu-item-close--magazine').on('click touchstart', function (e) {
        var $this = $(this);
        var $parent = $this.parents('.magazine-nav__submenu-wrapper');
        $parent.removeClass('menu-item-open');
        $parent.find('.magazine-nav__toggle').focus();
        // close previously opened level-1 submenues
        $('.magazine-submenu__toggle').parent('.menu-item--expanded').removeClass('menu-item-open');
      });

      // Toggle submenu open / close on btn click
      $('.magazine-submenu__toggle').on('click touchstart', function (e) {
        e.stopPropagation();
        var $this = $(this);
        var $parent = $this.parent('.menu-item--expanded');
        var $otherSubmenues = $parent.siblings();

        $parent.toggleClass('menu-item-open');
        $otherSubmenues.removeClass('menu-item-open');

        // Set aria attribute based on panel visibility.
        if ($parent.hasClass('menu-item-open')) {
          setAria($this, 'true');
          // reset area of previously opened submenues.
          $otherSubmenues.find('.magazine-nav__toggle').attr('aria-expanded', false);
        } else {
          setAria($this, 'false');
        }
      });
    };

    var getMenuPanelHeight = function getMenuPanelHeight() {
      // Set dropdown heights based on the content within.
      var dropdown = $('[data-level="level-0"]');
      var resizeMenuPanel = function resizeMenuPanel() {
        dropdown.each(function () {
          var self = $(this);
          // reset the height on screen resize
          self.height('auto');
          var mainHeight = self.height();
          var childMenu = self.find('[data-level="level-1"]');
          var totalHeight = mainHeight;
          var childHeight = 0;

          childMenu.each(function () {
            childHeight = $(this)[0].clientHeight;
            if (childHeight + 48 >= mainHeight) {
              totalHeight = childHeight;
            }
          });

          // Get the height of the ul .magazine-submenu__menu
          var $innerMenu = self.children('.magazine-submenu__menu');
          var innerMenuHeight = $innerMenu.height();

          // Set the min-height of each of the ul's child data-level="level-1"
          // so that inner menu panel has enough height to hover from parent link to it
          if ($innerMenu.length !== 0) {
            var $innerMenuChild = $innerMenu.find('[data-level="level-1"]');
            $innerMenuChild.css('min-height', innerMenuHeight + 'px');
          }
        });
      };

      // Select and loop the container element of the elements you want to equalise
      resizeMenuPanel();

      // At the end of a screen resize.
      var resizeTimer = null;
      $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          // resizing has "stopped".
          resizeMenuPanel();
        }, 250);
      });
    };

    /**
     * Run code if medium/desktop display has been detected.
     *
     * @param {MediaQueryList} mql
     */
    var desktopDetect = function desktopDetect(mql) {
      // Desktop
      if (mql.matches) {
        magazineDesktopMenu();
        getMenuPanelHeight();
      } else {
        return;
      }
    };

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    var watchResize = function watchResize() {
      // Use MatchMedia to ensure that desktop related code runs only on desktop
      var mql = matchMedia('(min-width: 850px)');
      // Detect Desktop on page load.
      desktopDetect(mql);
      // Watch to see if the page size changes.
      mql.addListener(desktopDetect);
    };

    // Initialize.
    watchResize();
  });
})(jQuery);
//# sourceMappingURL=all.js.map
