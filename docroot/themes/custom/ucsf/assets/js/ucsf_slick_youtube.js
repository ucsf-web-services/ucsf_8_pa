'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickYoutube = {
    attach: function attach(context) {

      $('.youtube-list__videos').slick({
        slidesToShow: 5,
        infinite: false
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick_youtube.js.map
