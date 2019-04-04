/* eslint-disable */
(function ($) {

  Drupal.behaviors.slickTopics = {
    attach: function (context) {

      $('.topics-list__topics').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        //mobileFirst: true,
        lazyLoad: 'progressive',
        infinite: false,
        dots: false
        /*
        responsive: [
          {
            breakpoint: 999,
            settings: {
              slidesToShow: 4,
              slidesToScroll: 4
            }
          },
          {
            breakpoint: 849,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          },
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
        ]
        */
      });
    }
  };

})(jQuery);
