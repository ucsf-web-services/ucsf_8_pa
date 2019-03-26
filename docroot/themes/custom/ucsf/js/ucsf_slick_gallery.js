/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickGallery = {
    attach: function (context) {
      
      $('.paragraph--type--gallery .gallery-container > .field-gallery-items').not('.slick-initialized').slick();
      
    }
  };
  
})(jQuery);
