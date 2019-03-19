'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickGallery = {
    attach: function attach(context) {

      $('.paragraph--type--gallery .gallery-container > .field-gallery-items').slick();
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick_gallery.js.map
