(($) => {
  Drupal.behaviors.socialIconsMobile = {
    attach: function attach(context, settings) {
      $(window, context).once("social-icon-context").each(function () {

        const $socialIcons = $(".article-meta-share", context);
        const $iconPrint = $("li", context).has('a[title="Print Article"]');
        // Toggle the .is-veisibl class on scroll.
        let lastScroll = 0;

        const hideAndShow = () => {
          // calculate weather user is scrolling up or down
          const currentScroll = window.pageYOffset;
          if (currentScroll > lastScroll) {
            // If scrolling down
            $socialIcons.removeClass("is-visible");
          } else {
            // If scrolling up
            $socialIcons.addClass("is-visible");
          }

          lastScroll = currentScroll;
        };

        // Only execute Social Navbar upscroll code in mobile
        const mobileDetect = (event) => {
          // Mobile
          if (event.matches) {
            // Remove print icon
            $iconPrint.css("display", "none");

            window.addEventListener("scroll", hideAndShow);

            // Desktop
          } else {
            window.removeEventListener("scroll", hideAndShow);
            // Add print icon
            $iconPrint.css("display", "initial");
            $socialIcons.removeClass("is-visible");
          }
        };

        // Use MatchMedia to ensure that Social Navbar upscroll is only happening in mobile
        const mql = matchMedia("(max-width: 1049px)");
        // Detect mobile on page load.
        mobileDetect(mql);
        // Watch to see if the page size changes.
        mql.addListener(mobileDetect);

      });
    }
  };
})(jQuery);
