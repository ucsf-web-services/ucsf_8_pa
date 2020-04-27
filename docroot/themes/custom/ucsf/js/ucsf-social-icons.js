(($) => {
  Drupal.behaviors.socialIconsMobile = {
    attach: function attach(context, settings) {
      $(window, context).once('social-icon-context').each(function () {

        const $socialIcons = $('.article-meta-share', context);
        const $iconPrint = $('li', context).has('a[title="Print Article"]');
        let lastScroll = 0;

        // Custom Social Navbar behavior for mobile
        const hideAndShow = () => {
          const currentScroll = window.pageYOffset;
          if (currentScroll > lastScroll) {
            // If scrolling down hide icons
            $socialIcons.removeClass('is-visible');
          } else {
            // If scrolling up show icons
            $socialIcons.addClass('is-visible');
          }

          // Update scroll value
          lastScroll = currentScroll;
        };

        // Only use custom Social Navbar up scroll behavior in mobile
        const mobileDetect = (event) => {
          // Mobile
          if (event.matches) {
            // Remove "print" icon
            $iconPrint.css('display', 'none');
            window.addEventListener('scroll', hideAndShow);

            // Social Navbar should not appear if article is not in viewport.
            (function () {
              if ('IntersectionObserver' in window) {
                // Elements where Social Navbar should be visible
                const hasSocialNav = document.querySelectorAll('#block-ucsf-content');

                // Determine if element with Social Navbar is in viewport
                const observer = new IntersectionObserver(entries => {
                  entries.forEach(entry => {
                    // Element with Social Navbar is in viewport
                    if (entry.intersectionRatio > 0) {
                      // show icons on up scroll
                      window.addEventListener('scroll', hideAndShow);
                    } else {
                      // Element with Social Navbar is not in viewport
                      // hide icons and don't trigger the up scroll event.
                      $socialIcons.removeClass('is-visible');
                      window.removeEventListener('scroll', hideAndShow);
                    }
                  })
                })

                  hasSocialNav.forEach(element => {
                  observer.observe(element);
                });
              }

            })();

            // Desktop
          } else {
            window.removeEventListener('scroll', hideAndShow);
            // Add "print" icon
            $iconPrint.css('display', 'initial');
            $socialIcons.removeClass('is-visible');
          }
        };

        // Use MatchMedia to ensure that Social Navbar upscroll is only happening in mobile
        const mql = matchMedia('(max-width: 1049px)');
        // Detect mobile on page load.
        mobileDetect(mql);
        // Watch to see if the page size changes.
        mql.addListener(mobileDetect);

      });
    }
  };
})(jQuery);
