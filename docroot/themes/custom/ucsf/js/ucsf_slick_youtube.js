/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickYoutube = {
    attach: function (context) {

      $('.youtube-list__videos').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        infinite: false,
        responsive: [
          {
            breakpoint: 1050,
            settings: {
              slidesToShow: 4,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 850,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 769,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 320,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
        ]
      });

    }
  };

})(jQuery);
