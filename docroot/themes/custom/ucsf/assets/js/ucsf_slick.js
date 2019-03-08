'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function attach(context) {

      $('.paragraph--type--gallery > .field-gallery-items').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick.js.map
