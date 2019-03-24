/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickYoutube = {
    attach: function (context) {

      $('.youtube-list__videos').slick({
        slidesToShow: 5,
        infinite: false
      });

    }
  };

})(jQuery);
