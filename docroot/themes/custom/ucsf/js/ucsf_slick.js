/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function (context) {
  
      $('.paragraph--type--gallery > .field-gallery-items').slick();
      
    }
  };
  
})(jQuery);
