'use strict';

/* eslint-disable */
(function ($) {

  Drupal.behaviors.slickTopics = {
    attach: function attach(context) {

      $('.topics-list__topics').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        lazyLoad: 'ondemand',
        autoplay: true,
        autoplaySpeed: 2000,
        pauseOnHover: false,
        infinite: true,
        dots: false,
        variableWidth: true,
        arrows: true

        // responsive: [{
        //   breakpoint: 599,
        //   settings: {
        //     arrows: false
        //   }
        // }]
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick_topics.js.map
