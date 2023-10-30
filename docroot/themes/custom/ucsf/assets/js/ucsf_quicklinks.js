"use strict";

/* eslint-disable */
(function ($, once) {
  var quicklinksNav = function quicklinksNav() {
    $(document).ready(function () {
      setTimeout(function () {
        $("body").removeClass("loading"); // 'quicklinks-intro' CSS animation breaks if class is removed before animation is finished.
      }, 5000);
    });

    Drupal.behaviors.quicklinks = {
      attach: function attach(context) {
        var $quicklinks = $(".quicklinks", context);
        var $triggerToggle = $(once("triggerToggle", ".quicklinks-trigger", context));
        var $triggerClose = $(".quicklinks-close", context);
        var $quicklinksTriggers = [$triggerToggle, $triggerClose];
        var selectorsArr = [$quicklinks, $(".header-region", context)];

        /**
         * Set aria-expanded attribute value on element that triggers the quicklinks visibility
         * @param {String} value
         */
        var setAria = function setAria(value) {
          $quicklinksTriggers.forEach(function (trigger) {
            return trigger.attr("aria-expanded", value);
          });
        };

        /**
         * Set visibility of the quicklinks panel
         * @param {String} visibility
         * Indicator for how to use classes responsible for quicklinks visibility.
         */
        var setVisibility = function setVisibility(visibility) {
          selectorsArr.forEach(function (selector) {
            if (visibility === "toggle") {
              selector.toggleClass("js-quicklinks-open");
            }if (visibility === "show") {
              selector.addClass("js-quicklinks-open");
            }
            if (visibility === "hide") {
              selector.removeClass("js-quicklinks-open");
            }
          });
        };

        // Toggle visibility of the quicklinks panel
        $triggerToggle.click(function (e) {
          e.preventDefault();
          setVisibility("toggle");

          // Set aria attribute.
          if ($triggerToggle.attr("aria-expanded") === "true") {
            setAria("false");
          } else {
            setAria("true");
          }
        });

        // Hide quicklinks panel
        $triggerClose.click(function (e) {
          e.preventDefault();
          // Hide quicklinks
          setVisibility("hide");
          setAria("false");
        });

        // Show quicklinks panel when user is tabbing through it
        $(".quicklinks-menu", context).on('focusin', function () {
          setVisibility("show");
          setAria("true");
        });

        // Hide quicklinks panel when focus is out
        $quicklinks.on('focusout', function () {
          // Waits and only removes class if newly focused element is outside the quicklinks
          setTimeout(function () {
            if (document.activeElement === document.body || document.activeElement === null) {
              return;
            }
            if (document.activeElement.closest(".quicklinks") === null) {
              setVisibility("hide");
              setAria("false");
            }
          }, 150);
        });
      }
    };
  };

  // Use MatchMedia to ensure that events are only happening in Tablet/Desktop
  var mql = matchMedia('(min-width: 769px)');
  // On page load, check if desktop.
  if (mql.matches) {
    quicklinksNav();
  }
})(jQuery, once);
//# sourceMappingURL=ucsf_quicklinks.js.map
