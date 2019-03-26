/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickTopics = {
    attach: function (context) {

      $('.topics-list__topics').not('.slick-initialized').slick({
        slidesToShow: 4
      });

    }
  };
  
})(jQuery);
