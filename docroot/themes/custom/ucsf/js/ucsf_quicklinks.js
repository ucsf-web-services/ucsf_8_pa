/* eslint-disable */
(function ($, once) {
  const quicklinksNav = () => {
    $(document).ready(function () {
      setTimeout(function () {
        $("body").removeClass("loading"); // 'quicklinks-intro' CSS animation breaks if class is removed before animation is finished.
      }, 5000);
    });

    Drupal.behaviors.quicklinks = {
      attach: function (context) {
        const $quicklinks = $(".quicklinks", context);
        const $triggerToggle = $(once("triggerToggle",".quicklinks-trigger", context));
        const $triggerClose = $(".quicklinks-close", context);
        const $quicklinksTriggers = [ $triggerToggle, $triggerClose];
        const selectorsArr = [ $quicklinks, $(".header-region", context) ];

        /**
         * Set aria-expanded attribute value on element that triggers the quicklinks visibility
         * @param {String} value
         */
        const setAria = ( value ) => {
          $quicklinksTriggers.forEach(trigger => trigger.attr("aria-expanded", value));
        }

        /**
         * Set visibility of the quicklinks panel
         * @param {String} visibility
         * Indicator for how to use classes responsible for quicklinks visibility.
         */
        const setVisibility = ( visibility ) => {
          selectorsArr.forEach(selector => {
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
        $triggerToggle.click((e) => {
          console.log('made it')
          e.preventDefault();
          setVisibility("toggle" );

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
        $(".quicklinks-menu", context).on('focusin', function() {
          setVisibility( "show" );
          setAria("true");
        });

        // Hide quicklinks panel when focus is out
        $quicklinks.on('focusout', function() {
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
      },
    };
  }

  // Use MatchMedia to ensure that events are only happening in Tablet/Desktop
  const mql = matchMedia('(min-width: 769px)');
  // On page load, check if desktop.
  if (mql.matches) {
    quicklinksNav();
  }
})(jQuery, once);
