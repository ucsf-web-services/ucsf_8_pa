'use strict';

/**
 * Main navigation, Desktop.
 */

(function ($) {

  // Wait for the document to be ready.
  $(function () {
    var desktopMenu = function desktopMenu() {
      var menuButton = $('main-nav__toggle, .main-submenu__toggle, .menu-item-close');
      menuButton.each(function () {
        $(this).on('click', function (event) {
          event.preventDefault();
        });
      });

      /**
       * Set aria-expanded attribute value on element that triggers the submenu visibility
       * @param {jQuery Object} trigger
       * @param {String} value
       */
      var setAria = function setAria(trigger, value) {
        trigger.attr('aria-expanded', value);
      };

      // Toggle open the sub panels on main nav button click.
      $('.main-nav__toggle').on('click touchstart', function (e) {
        var $this = $(this);
        var $parent = $this.parent('.main-nav__submenu-wrapper, .search');
        var $otherPanels = $parent.siblings();

        $parent.toggleClass('menu-item-open');
        $otherPanels.removeClass('menu-item-open');

        // Set aria attribute based on panel visibility.
        if ($parent.hasClass('menu-item-open')) {
          setAria($this, 'true');
          setAria($('.menu-item-close'), 'true');
          // reset area of previously opened panels.
          $otherPanels.find('.main-nav__toggle').attr('aria-expanded', false);
        } else {
          setAria($this, 'false');
          setAria($('.menu-item-close'), 'false');
        }
      });

      // The "x" button inside menu panel.
      $('.menu-item-close').on('click touchstart', function (e) {
        var $this = $(this);
        var $parent = $this.parents('.main-nav__submenu-wrapper');
        $parent.removeClass('menu-item-open');
        $parent.find('.main-nav__toggle').focus();
      });

      // Toggle submenu open / close on btn click
      // const $submenuTriggerToggle = $(".main-submenu__toggle");
      $('.main-submenu__toggle').on('click touchstart', function (e) {
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
          $otherSubmenues.find('.main-nav__toggle').attr('aria-expanded', false);
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

          self.height(totalHeight + 68);
          self.find('.main-submenu__label').width(totalHeight + 20);

          // Get the height of the ul .main-submenu__menu
          var $innerMenu = self.children('.main-submenu__menu');
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

    var keyboardAccessibleSearchForm = function keyboardAccessibleSearchForm() {
      var $searchToggle = $('.menu-item-search-menu', context);
      var $search = $('.search', context);

      /**
       * Set aria-expanded attribute value on element that triggers the submenu visibility
       * @param {jQuery Object} trigger
       * @param {String} value
       */
      var setAria = function setAria(trigger, value) {
        trigger.attr('aria-expanded', value);
      };

      $searchToggle.click(function (e) {
        e.preventDefault();
        if ($search.hasClass('menu-item-open')) {
          $('.main-nav__search .home-search__form-input').focus();
        }
      });

      //Search form closes when tabbing away.
      $search.on('focusout', function () {
        //Wait and only remove classes if newly focused element is outside the search form
        setTimeout(function () {
          // When browser cant find activeElement it returns <body> or null
          // which triggers the false positive for document.activeElement.closest('.main-nav__search') === null
          // Clicking on the label inside search box caused this behavior, since labels don't receive focus
          if (document.activeElement === document.body || document.activeElement === null) {
            return;
          }

          // Close the search box if the currently focused el.
          //  is not inside the search box
          if (document.activeElement.closest('.main-nav__search') === null) {
            $search.removeClass('menu-item-open');
            setAria($searchToggle, false);
          }
          // delay needs to be at least 150 to avoid a race condition with $searchToggle.toggleClass('active');
        }, 150);
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
        desktopMenu();
        getMenuPanelHeight();
        keyboardAccessibleSearchForm();
      } else {
        console.log("mobile");
        return;
      }
    };

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    var watchResize = function watchResize() {
      // Use MatchMedia to ensure that the range slider only happens in desktop.
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

(function ($, window) {
  // Main menu search form redirect
  Drupal.behaviors.mainMenuSearchFilter = {
    attach: function attach(context, settings) {
      // Selector for the form.
      var $form = $('.search__form', context);
      $form.submit(function () {
        var $this = $(this);
        // Find checked radio button inside the form and get it's value.
        var option = $this.find('.search-filter__radio:checked').val();
        // If value is "News" redirect to news filter search.
        if (option === 'News') {
          $this.attr('action', '/news/filter');
          $this.find('.home-search__form-input').attr('name', 'combine');
        }

        return true;
      });
    }
  };
})(jQuery, window);
//# sourceMappingURL=ucsf_main_menu_desktop.js.map
