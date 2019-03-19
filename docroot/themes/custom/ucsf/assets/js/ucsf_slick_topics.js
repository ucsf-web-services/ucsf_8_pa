'use strict';

/* eslint-disable */
(function ($) {
  Drupal.behaviors.slickTopics = {
    attach: function attach(context) {

      $('.topics-list__topics').slick({
        slidesToShow: 4
      });
    }
  };
})(jQuery);
//# sourceMappingURL=ucsf_slick_topics.js.map
