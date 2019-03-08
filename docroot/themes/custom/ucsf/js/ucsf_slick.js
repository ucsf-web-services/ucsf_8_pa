/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function (context) {
  
      $('.paragraph--type--gallery > .field-gallery-items').slick({
        infinite: true,
        slidesToShow: 1,
        slidesToScroll: 1
      });
      
    }
  };
  
})(jQuery);
