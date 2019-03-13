/* eslint-disable */
(function ($) {
  Drupal.behaviors.gallery = {
    attach: function (context) {
  
      $('.paragraph--type--gallery .gallery-container > .field-gallery-items').slick();
  
      var windowWidth = $(window).width();
      $('.paragraph--type--gallery').css('width', windowWidth);
      
    }
  };
  
})(jQuery);
