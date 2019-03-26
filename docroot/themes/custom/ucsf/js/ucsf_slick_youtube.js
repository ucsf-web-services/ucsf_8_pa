/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickYoutube = {
    attach: function (context) {

      $('.youtube-list__videos').not('.slick-initialized').slick({
        slidesToShow: 4,
        infinite: false
      });

    }
  };

})(jQuery);
