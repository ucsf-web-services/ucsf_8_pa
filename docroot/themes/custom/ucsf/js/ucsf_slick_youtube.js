/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickYoutube = {
    attach: function (context) {

      $('.youtube-list__videos').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        lazyLoad: 'ondemand',
        infinite: false,
        variableWidth: true,
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
              slidesToShow: 4,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 599,
            settings: {
              slidesToShow: 1.3,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 320,
            settings: {
              slidesToShow: 1.2,
              slidesToScroll: 1
            }
          }
        ]
      });

    }
  };

})(jQuery);
