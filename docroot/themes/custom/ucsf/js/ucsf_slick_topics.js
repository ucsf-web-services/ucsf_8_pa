/* eslint-disable */
(function ($) {

  Drupal.behaviors.slickTopics = {
    attach: function (context) {

      $('.topics-list__topics').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 1,
        lazyLoad: 'progressive',
        infinite: true,
        dots: false,
        variableWidth: true,
        arrows: true,
        reponsive: [{
          breakpoint: 599,
          settings: {
            arrows: false
          }
        }]
      });
    }
  };

})(jQuery);
