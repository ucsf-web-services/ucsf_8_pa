"use strict";

(function ($) {
  'use strict';

  Drupal.behaviors.carouselSlider = {
    attach: function attach(context, settings) {
      $(context).find(".paragraph--slideshow").once("slideshow-initialized").each(function () {

        var hero_default = $('.paragraph--slideshow .field--name-field-media');

        if (hero_default.children().length > 1) {
          hero_default.slick({
            dots: true,
            infinite: true,
            slidesToShow: 1,
            centerMode: true,
            prevArrow: '<button type="button" class="slick-prev">' + '<span class="visually-hidden">Previous</span>' + '<i class="fas fa-arrow-left"></i>' + '</button>',
            nextArrow: '<button type="button" class="slick-next">' + '<i class="fas fa-arrow-right"></i>' + '<span class="visually-hidden">Next</span>' + '</button>',
            variableWidth: true
          });
        }
      });
    }
  };
})(jQuery);
//# sourceMappingURL=slideshow.js.map
