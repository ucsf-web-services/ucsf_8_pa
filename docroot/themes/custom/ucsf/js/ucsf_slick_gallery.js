/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickGallery = {
    attach: function (context) {
      
      $('.gallery-container > .field-gallery-items').not('.slick-initialized').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            centerPadding: '0',
            centerMode: true,
            variableWidth: true
          }
      );
      
    }
  };
  
})(jQuery);
