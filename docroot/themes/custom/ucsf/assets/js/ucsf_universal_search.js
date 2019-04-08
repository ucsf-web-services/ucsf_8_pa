'use strict';

/**
 * Created by eguerin on 4/7/19.
 */
(function ($, drupalSettings) {

    /**
     * Use this behavior as a template for custom Javascript.
     */
    Drupal.behaviors.mobileUniversalSearch = {
        attach: function attach(context, settings) {

            var waypoint = $('#universalDirectory', context).waypoint({
                handler: function handler(direction) {
                    //console.log('Hit midpoint of my context: ' + direction);
                    if ($(window).width() <= 600) {
                        if (direction == 'down') {
                            $('.search-box-container').addClass('scrolled');
                        } else {
                            $('.search-box-container').removeClass('scrolled');
                        }
                    }
                },
                offset: '0%'
                //context: '.results-wrapper',
            });
        }
    };

    Drupal.behaviors.mobileAdvanced = {

        attach: function attach(context, setttings) {

            $('#home-search-button__advanced', context).click(function (e) {
                e.preventDefault();
                if ($(this).hasClass('active')) {
                    $('.search-box-container fieldset').removeClass('active');
                    $(this).removeClass('active');
                    //console.log('goodbye');
                } else {
                    $('.search-box-container fieldset').addClass('active');
                    $(this).addClass('active');
                    //console.log('hhellllo');
                }
            });

            $('.home-search__form-submit-advanced', context).click(function (e) {
                e.preventDefault();
                var option = $('input[name="searchSelect"]:checked').val();
                //console.log(option);
                $('#universal-search').attr('action', '/search' + option);
                $('#universal-search').submit();
            });
        }

    };
})(jQuery, drupalSettings);
//# sourceMappingURL=ucsf_universal_search.js.map
