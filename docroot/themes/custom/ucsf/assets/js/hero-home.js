'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.hero_home = {
    attach: function attach() {

      var mq = window.matchMedia("(min-width: 1023px)");
      var container = $('.has-hero-home-image');
      var homeHeroUrl = 'url(' + drupalSettings.homeHeroImage + ')';

      mq.addListener(WidthChange);
      WidthChange(mq);

      function WidthChange(mq) {
        if (mq.matches) {
          container.css('background-image', homeHeroUrl);
        } else {
          container.css('background-image', 'none');
        }
      }
    }
  };
})(jQuery);
//# sourceMappingURL=hero-home.js.map
