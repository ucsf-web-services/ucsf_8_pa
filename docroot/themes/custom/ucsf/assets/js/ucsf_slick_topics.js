'use strict';

/* eslint-disable */
(function ($) {

  Drupal.behaviors.slickTopics = {
    attach: function attach(context) {

      $('.topics-list__topics').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        lazyLoad: 'progressive',
        infinite: false,
        dots: false,
        variableWidth: true
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
//# sourceMappingURL=ucsf_slick_topics.js.map
