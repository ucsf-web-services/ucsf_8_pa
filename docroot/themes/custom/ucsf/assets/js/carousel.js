"use strict";

(function ($) {
  'use strict';

  Drupal.behaviors.carouselSlider = {
    attach: function attach(context, settings) {
      $(context).find(".quote-slider").once("quote-slider-initialized").each(function () {
        $('.quote-slider').slick({
          dots: true,
          adaptiveHeight: true
        });
      });

      $(context).find(".people-slideshow").once("quote-slider-initialized").each(function () {
        $('.people-slideshow').slick({
          dots: true,
          adaptiveHeight: true
        });
      });
    }
  };
})(jQuery);
//# sourceMappingURL=carousel.js.map
