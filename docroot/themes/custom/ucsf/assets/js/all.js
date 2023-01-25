'use strict';

/* eslint-disable */
(function ($, window) {
    'use strict';

    Drupal.behaviors.dieToolbardie = {
        attach: function attach(context) {
            window.matchMedia('(min-width: 975px)').addListener(function (event) {
                event.matches ? $('#toolbar-item-administration', context).click() : $('.toolbar-item.is-active', context).click();
            });
        }
    };

    Drupal.behaviors.slickNav = {
        attach: function attach(context, settings) {

            $('.main-menu--mobile').slicknav({
                duplicate: false,
                prependTo: '.slicknav-placeholder',
                label: 'Main Menu',
                openedSymbol: '<span class="fas fa-angle-up">',
                closedSymbol: '<span class="fas fa-angle-down">'
            });

            //var height = $(window).height();
            //$('#block-ucsf-main-menu').css({'height': height - 90});
            //$('.menu-parent-wrapper').css({'height': height - 150, 'overflow-y':'scroll'});

            $('.slicknav_menu .slicknav_btn').on('click', function () {
                if ($(this).hasClass('slicknav_collapsed')) {
                    //console.log('menu is being closed');
                    $('.combined-header-region').removeClass('fixed');
                    $('body').removeClass('fixed');
                } else {
                    //console.log('menu is being opened');
                    $('body').addClass('fixed');
                }
            });
        }
    };

    Drupal.behaviors.fixHeights = {
        attach: function attach(context, settings) {
            $(window, context).once('fixheight').each(function () {
                // Select and loop the container element of the elements you want to equalise
                resizeCards();

                var intstop = 0;
                var intervals = setInterval(function () {
                    resizeCards();
                    intstop++;
                    // console.log('interval number: '+ intstop);
                    if (intstop == 2) clearInterval(intervals);
                }, 3500);

                // At the end of a screen resize, card sizes.
                var resizeTimer = null;
                $(window).on('resize', context, function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function () {
                        // resizing has "stopped".
                        resizeCards();
                    }, 250);
                });
            });
        }
    };

    function resizeCards() {
        $('.layout-columns__2, .layout-columns__3, .layout-columns__4').each(function () {
            // Cache the highest
            var highestBox = 0;

            // FYI, There are some cards that need the sizing done on a parent
            // wrapper, and some that need it on an inner child. This is
            // confusing but due to the way the markup is, we have no other
            // option.
            // For example: .field-content-promotional-conten can be by itself
            // or it can be a sibling to an image. This causes boxes to get
            // miscalculated. So if .field-content-promotional-image is a
            // previous sibling we have to apply the style to the parent
            // wrapper.
            var $items = $('.fact-card, .promo-list__card, .field-content-promotional-conten, .field-column-content-content:has(.field-content-promotional-image)', this);

            // Exit if this column doesn't have anything needing resized.
            if ($items.length < 1) {
                return;
            }

            // Remove a selector if it is actually a child of the real box selector.
            $items = $items.filter(function () {
                if (!$(this).prev('.field-content-promotional-image').length) {
                    return $(this);
                }
            });

            // Clear the current height values.
            $items.css('height', 'auto');

            // Select and loop the elements you want to equalise
            $items.each(function () {
                var $this = $(this);
                // If this box is higher than the cached highest then store it
                if ($this.innerHeight() > highestBox) {
                    highestBox = $this.innerHeight();
                }
            });

            // Set the height of all those children to whichever was highest
            $items.css('height', highestBox + 'px');
        });
    }

    // for emergency alerts
    $('.emergency-alert__toggle').on('click', function (e) {
        var $this = $(this);
        $this.closest('.emergency-alert').toggleClass('emergency-alert--collapsed'); //you can list several class names

        if ($this.attr('aria-expanded') === 'true') {
            $this.attr('aria-expanded', 'false');
        } else {
            $this.attr('aria-expanded', 'true');
        }
        e.preventDefault();
    });

    $.urlParam = function (name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return '';
        } else {
            return results[1] || 0;
        }
    };

    $('#email').val(decodeURIComponent($.urlParam('email')));

    // Need a class on center aligned videos to fix width issue;
    $('.align-center').each(function () {
        if ($(this).find('.media--type-remote-video').length > 0) {
            $(this).addClass('align-center--video');
        };
    });

    // when article ckeditor body is left empty because content lives in Paragraphs,
    //  it leaves a gap between header and those elements.
    var removeSpaceFromEmptyElement = function removeSpaceFromEmptyElement() {
        if (document.querySelector('.page_narrow')) {
            var emptyNarrowPageElement = document.querySelectorAll('.page_narrow');
            var styles = '\n        padding-top:0;\n        margin-top:-1px;\n        margin-bottom:-1px;';

            emptyNarrowPageElement.forEach(function (element) {
                if (!element.hasChildNodes()) {
                    element.style = styles;
                }
            });
        }
    };

    var checkIfHasCardWithOffsetColor = function checkIfHasCardWithOffsetColor() {
        var offsetCardWithColor = document.querySelectorAll('.card-with-offset-color');
        // console.log(offsetCardWithColor)

        offsetCardWithColor.forEach(function (element) {

            $(element).closest('.layout-column').addClass("has-card-with-offset-color");
        });
    };

    // Check screen size.
    var mql = window.matchMedia('(max-width: 1049px)');

    function screenTest(event) {
        if (event.matches) {
            /* the viewport is 1049 pixels wide or less */
            checkIfHasCardWithOffsetColor();
        } else {
            /* the viewport is more than 1049 pixels wide */
            removeSpaceFromEmptyElement();
        }
    }

    screenTest(mql);
    mql.addListener(screenTest);
})(jQuery, window);
//# sourceMappingURL=all.js.map
