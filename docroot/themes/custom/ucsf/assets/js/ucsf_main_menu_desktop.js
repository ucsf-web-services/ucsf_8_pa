"use strict";

/**
 * Main navigation, Desktop.
 * TODO: write logic for changing '.menu-item-submenu-toggle' aria-expanded based on hover state.
 */
(function ($, window) {
  Drupal.behaviors.desktopDropdownHeight = {
    attach: function attach(context, settings) {
      $(window, context).once("menu-desktop").each(function () {
        // Set dropdown heights based on the content within.
        var dropdown = $('[data-level="level-0"]');
        var resizeMenuPanel = function resizeMenuPanel() {
          dropdown.each(function () {
            var self = $(this);
            // reset the height on screen resize
            self.height("auto");
            var mainHeight = self.height();
            var childMenu = self.find(".menu-child--wrapper");
            var totalHeight = mainHeight;
            var childHeight = 0;

            childMenu.each(function () {
              childHeight = $(this)[0].clientHeight;
              if (childHeight + 48 >= mainHeight) {
                totalHeight = childHeight;
              }
            });

            self.height(totalHeight + 68);
            self.find(".menu-child--label").width(totalHeight + 20);

            // Get the height of the ul .menu-child--menu
            var $innerMenu = self.children(".menu-child--menu");
            var innerMenuHeight = $innerMenu.height();

            // Set the min-height of each of the ul's child data-level="level-1"
            // so that inner menu panel has enough height to hover from parent link to it
            if ($innerMenu.length !== 0) {
              var $innerMenuChild = $innerMenu.find('[data-level="level-1"]');
              $innerMenuChild.css("min-height", innerMenuHeight + "px");
            }
          });
        };

        // Select and loop the container element of the elements you want to equalise
        resizeMenuPanel();

        // At the end of a screen resize.
        var resizeTimer = null;
        $(window).on("resize", context, function () {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function () {
            // resizing has "stopped".
            resizeMenuPanel();
          }, 250);
        });

        var nolink = $(".menu-item-submenu-toggle");
        nolink.each(function () {
          $(this).on("click", function (event) {
            event.preventDefault();
          });
        });

        /**
         * Set aria-expanded attribute value on element that triggers the submenu visibility
         * @param {jQuery Object} trigger
         * @param {String} value
         */
        var setAria = function setAria(trigger, value) {
          trigger.attr("aria-expanded", value);
        };

        $(".menu-item-parent").on("click touchstart", function () {
          var $this = $(this);
          // do not add 'menu-item-open' class if the menu item is search
          if ($this.hasClass("search")) {
            $this.siblings().removeClass("menu-item-open");
            return;
          }
          // Toggle visibility of submenu panel on btn click. Automatically close other panels
          $this.toggleClass("menu-item-open").siblings().removeClass("menu-item-open");
          // reset area of previously opened panels.
          $this.siblings().find(".menu-item-top-level").attr("aria-expanded", false);

          // Set aria attribute based on panel visibility.
          var $triggerToggle = $this.find(".menu-item-top-level");
          if ($this.hasClass("menu-item-open")) {
            setAria($triggerToggle, "true");
            setAria($(".menu-item-close"), "true");
          } else {
            setAria($triggerToggle, "false");
            setAria($(".menu-item-close"), "false");
          }
        });

        $(".menu-item-close").on("click touchstart", function (e) {
          e.stopPropagation(); // Key line to work perfectly
          if ($(this).parent().parent().hasClass("menu-item-open")) {
            $(this).parent().parent().removeClass("menu-item-open");
            setAria($(".menu-item-close"), "false");
            setAria($(".menu-item-parent").find(".menu-item-top-level"), "false");
          }
        });

        // Shows menus when it's being tabbed through
        var $dropdown = $(".menu-item--expanded", context);
        $dropdown.on("focusin", function () {
          // Menu dropdowns open on focus.
          $(this).parents(".menu-item--expanded").addClass("menu-item-open");
        });

        // Menu dropdown closes when focus is out.
        $dropdown.on("focusout", function () {
          var $this = $(this);
          // Waits and only removes class if newly focused element is outside the dropdown
          setTimeout(function () {
            // Closes second level subnav
            if ($(document.activeElement).parents(".menu-child--wrapper").length === 0) {
              $this.parents(".menu-item-parent").removeClass("menu-item-open");
            }
            // Closes the third level subnav if the current focused element is not in it.
            else if ($this.has(document.activeElement).length === 0) {
                $this.parents(".menu-item--expanded").first().removeClass("menu-item-open");
              }
          }, 500);
        });

        var $submenuTriggerToggle = $(".menu-item-submenu-toggle");
        $submenuTriggerToggle.on("click touchstart", function (e) {
          var $submenuWrapper = $(this).parents(".menu-item--expanded").first();
          e.preventDefault();
          e.stopPropagation();
          $submenuWrapper.toggleClass("menu-item-open");
          if ($submenuWrapper.hasClass("menu-item-open")) {
            setAria($submenuTriggerToggle, "true");
          } else {
            setAria($submenuTriggerToggle, "false");
          }
        });
      });
    }
  };

  Drupal.behaviors.keyboardAcessibleSeacrhForm = {
    attach: function attach(context, settings) {
      var $searchToggle = $(".menu-item-search-menu", context);
      var $search = $(".wrapper--search-menu", context);
      $searchToggle.click(function (e) {
        e.preventDefault();

        $search.toggleClass("active");
        $searchToggle.toggleClass("active");
        if ($searchToggle.hasClass("active")) {
          $(".wrapper--search-menu .home-search__form-input").focus();
        }
      });

      $(".menu-parent--wrapper .menu-item", context).click(function (e) {
        // If other menu item is clicked close search form
        if (!$(this).hasClass("search")) {
          $search.removeClass("active");
          $searchToggle.removeClass("active");
        }
      });

      //Search form opens when focus is inside.
      $search.on("focusin", function () {
        $search.addClass("active");
        $searchToggle.addClass("active"); // changes toggle icon
      });

      //Search form closes when tabbing away.
      $search.on("focusout", function () {
        //Wait and only remove classes if newly focused element is outside the search form
        setTimeout(function () {
          // When browser cant find activeElement it returns <body> or null
          // which triggers the false positive for document.activeElement.closest('.wrapper--search-menu') === null
          // Clicking on the label inside search box caused this behavior, since labels don't receive focus
          if (document.activeElement === document.body || document.activeElement === null) {
            return;
          }

          // Close the search box if the currently focused el.
          //  is not inside the search box
          if (document.activeElement.closest(".wrapper--search-menu") === null) {
            $search.removeClass("active");
            $searchToggle.removeClass("active");
          }
          // delay needs to be at least 150 to avoid a race condition with $searchToggle.toggleClass('active');
        }, 150);
      });
    }
  };

  // Main menu search form redirect
  Drupal.behaviors.mainMenuSearchFilter = {
    attach: function attach(context, settings) {
      // Selector for the form.
      var $form = $(".search__form", context);
      $form.submit(function () {
        var $this = $(this);
        // Find checked radio button inside the form and get it's value.
        var option = $this.find(".search-filter__radio:checked").val();
        // If value is "News" redirect to news filter search.
        if (option === "News") {
          $this.attr("action", "/news/filter");
          $this.find(".home-search__form-input").attr("name", "combine");
        }

        return true;
      });
    }
  };
})(jQuery, window);
//# sourceMappingURL=ucsf_main_menu_desktop.js.map
