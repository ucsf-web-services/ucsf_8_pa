/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function (context) {
  
      $('.paragraph--type--gallery .gallery-container > .field-gallery-items').slick();

    }
  };
  
})(jQuery);
