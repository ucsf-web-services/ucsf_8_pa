'use strict';

/* eslint-disable */
(function ($) {
    Drupal.behaviors.slickGallery = {
        attach: function attach(context) {
            $('.gallery-container > .field-gallery-items').not('.slick-initialized').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: true,
                centerPadding: '0',
                centerMode: true,
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
//# sourceMappingURL=ucsf_slick_gallery.js.map
