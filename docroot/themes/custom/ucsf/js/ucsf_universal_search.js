/**
 * Created by eguerin on 4/7/19.
 */
(function ($, drupalSettings, once) {

    /**
     * Use this behavior as a template for custom Javascript.
     */
    Drupal.behaviors.mobileUniversalSearch = {
        attach: function (context, settings) {

            var waypoint = $('.gsc-result-info__new', context).waypoint({
                handler: function (direction) {


                        if (direction == 'down') {
                            $('.search-box-container').addClass('scrolled');
                        } else {
                            $('.search-box-container').removeClass('scrolled');
                        }

                },
                offset: '0%'
                //context: '.results-wrapper',
            });
        }
    };

    Drupal.behaviors.mobileAdvanced = {

        attach: function(context, settings) {

            $('#home-search-button__advanced', context).click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('active')) {
                    $('.search-box-container fieldset').removeClass('active');
                    $(this).removeClass('active');
                } else {
                    $('.search-box-container fieldset').addClass('active');
                    $(this).addClass('active');
                }
            });

            $('.home-search__form-submit-advanced', context).click(function(e) {
                e.preventDefault();
                var option = $('input[name="searchSelect"]:checked').val();
                $('#universal-search').attr('action', '/search'+option);
                $('#universal-search').submit();
            });


            $(once('mobileAdvanced', '.mobile-search-reveal', context)).click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('active')) {
                    $('.home-search__form').removeClass('active');
                    $('.header--search').removeClass('header--search-active');
                    $(this).removeClass('active');
                } else {
                    $('.home-search__form').addClass('active');
                    $('.header--search').addClass('header--search-active');
                    $(this).addClass('active');
                }
            });
        }

    };

})(jQuery, drupalSettings, once);
