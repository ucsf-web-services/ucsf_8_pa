'use strict';

(function ($, once) {
  // All of this code is used for small screen:
  Drupal.behaviors.socialIconsMobile = {
    attach: function attach(context, settings) {
      once('social-icon-context', 'html', context).forEach(function () {
        var $socialIcons = $('.article-meta-share', context);
        var $iconPrint = $('li', context).has('a[title*="Print"]');
        var lastScroll = 0;

        // Social Navbar behavior
        var hideAndShow = function hideAndShow() {
          var currentScroll = window.pageYOffset;
          if (currentScroll > lastScroll) {
            // If scrolling down, hide icons
            $socialIcons.removeClass('is-visible');
          } else {
            // If scrolling up, show icons
            $socialIcons.addClass('is-visible');
          }

          // Update scroll value
          lastScroll = currentScroll;
        };

        // Detect if article is not in viewport.
        var articleDetect = function articleDetect() {
          if ('IntersectionObserver' in window) {
            // Article content wrapper
            var hasSocialNav = document.querySelectorAll('.block-system-main-block');

            var observer = new IntersectionObserver(function (entries) {
              entries.forEach(function (entry) {
                // Article content is in viewport
                if (entry.intersectionRatio > 0) {
                  // show icons on up scroll
                  window.addEventListener('scroll', hideAndShow);
                } else {
                  // Article content is not in viewport
                  // hide icons and don't trigger the up scroll event.
                  $socialIcons.removeClass('is-visible');
                  window.removeEventListener('scroll', hideAndShow);
                }
              });
            });

            // Check if specified element is in viewport
            hasSocialNav.forEach(function (element) {
              observer.observe(element);
            });
          }
        };

        // Only use custom Social Navbar up scroll behavior in mobile
        var mobileDetect = function mobileDetect(event) {
          // Mobile
          if (event.matches) {
            // Remove "print" icon
            $iconPrint.css('display', 'none');
            window.addEventListener('scroll', hideAndShow);
            articleDetect();

            // Desktop
          } else {
            window.removeEventListener('scroll', hideAndShow);
            // Add "print" icon
            $iconPrint.css('display', 'initial');
            $socialIcons.removeClass('is-visible');
          }
        };

        // Use MatchMedia breakpoint to trigger small screen behavior and reset behavior for Desktop.
        var mql = matchMedia('(max-width: 1049px)');
        // Detect mobile on page load.
        mobileDetect(mql);
        // Watch to see if the page size changes.
        mql.addListener(mobileDetect);
      });
    }
  };
})(jQuery, once);
//# sourceMappingURL=ucsf_social_icons.js.map
