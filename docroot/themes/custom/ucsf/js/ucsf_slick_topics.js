/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickTopics = {
    attach: function (context) {
  
      $('.topics-list__topics').slick({
        slidesToShow: 4,
      });

    }
  };
  
})(jQuery);
