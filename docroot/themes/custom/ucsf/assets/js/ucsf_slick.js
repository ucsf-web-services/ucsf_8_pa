'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function attach(context) {

      $('.paragraph--type--gallery > .field-gallery-items').slick();
      $('.main-content.narrow-page').css('overflow', 'hidden');
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick.js.map
