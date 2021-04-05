/* eslint-disable */
(function ($, window) {
    'use strict';

    Drupal.behaviors.dieToolbardie = {
        attach: function (context) {
            window.matchMedia('(min-width: 975px)').addListener(function (event) {
                event.matches ? $('#toolbar-item-administration', context).click() : $('.toolbar-item.is-active', context).click();
            });
        }
    };


    Drupal.behaviors.slickNav = {
        attach: function (context, settings) {

            $('#block-ucsf-main-menu').slicknav({
                duplicate: false,
                prependTo: '.slicknav-placeholder',
                label: 'Main Menu',
                openedSymbol: '<span class="fas fa-angle-up">',
                closedSymbol: '<span class="fas fa-angle-down">'
            });

            //var height = $(window).height();
            //$('#block-ucsf-main-menu').css({'height': height - 90});
            //$('.menu-parent-wrapper').css({'height': height - 150, 'overflow-y':'scroll'});

            $('.slicknav_menu .slicknav_btn').on('click', function() {
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


    /**
     * Idea came from Stackoverflow
     * Basically wrap all your breakpoints not IE compatible with the body:not(.explorer) {} expression
     * https://stackoverflow.com/questions/15388967/possible-to-disable-media-queries-or-force-a-resolution-reason-allow-an-iphon
     * This wasn't viable since the breakpoint and call order was backward in the SCSS files of course!
     */
    Drupal.behaviors.fixExplorer = {
        attach: function (context, settings) {
            var version = detectIE();

            if (version) {
                if (Number(version) <= 11) {
                    $('body', context).once('fixExplorer').addClass('explorer');
                    $('.skip-link').once('fixExplorer').after('<div class="ie-message">' +
                        'Looks like you’re visiting us on Internet Explorer. For the best UCSF.edu experience, ' +
                        'please use <a href="https://www.google.com/chrome/" target="_blank">Chrome</a> or <a href="https://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>. ' +
                        '</div>'
                    );
                }
            }
        }
    };

    Drupal.behaviors.fixHeights = {
        attach: function (context, settings) {
            $(window, context).once('fixheight').each( function() {
                // Select and loop the container element of the elements you want to equalise
                resizeCards();

                var intstop = 0;
                var intervals = setInterval(function() {
                    resizeCards();
                    intstop++;
                    // console.log('interval number: '+ intstop);
                    if (intstop==2) clearInterval(intervals);
                 }, 3500);


                // At the end of a screen resize, card sizes.
                let resizeTimer = null;
                $(window).on('resize', context, () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        // resizing has "stopped".
                        resizeCards();
                    }, 250);
                });
            });
        }
    };

    /**
     * detect IE
     * returns version of IE or false, if browser is not Internet Explorer

    function detectIE() {
        var ua = window.navigator.userAgent;

        var msie = ua.indexOf('MSIE ');
        if (msie > 0) {
            // IE 10 or older => return version number
            return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
        }

        var trident = ua.indexOf('Trident/');
        if (trident > 0) {
            // IE 11 => return version number
            var rv = ua.indexOf('rv:');
            return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
        }
        // other browser
        return false;
     }
     */


    function detectIE() {
        var ua = window.navigator.userAgent;

        // Test values; Uncomment to check result …

        // IE 10
        // ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';

        // IE 11
        // ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';

        // Edge 12 (Spartan)
        // ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0';

        // Edge 13
        // ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';

        var msie = ua.indexOf('MSIE ');
        if (msie > 0) {
            // IE 10 or older => return version number
            return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
        }

        var trident = ua.indexOf('Trident/');
        if (trident > 0) {
            // IE 11 => return version number
            var rv = ua.indexOf('rv:');
            return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
        }

        var edge = ua.indexOf('Edge/');
        if (edge > 0) {
            // Edge (IE 12+) => return version number
            return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
        }

        // other browser
        return false;
    }

    function resizeCards() {
        $('.layout-columns__2, .layout-columns__3, .layout-columns__4').each(function() {
            // Cache the highest
            let highestBox = 0;

            // FYI, There are some cards that need the sizing done on a parent
            // wrapper, and some that need it on an inner child. This is
            // confusing but due to the way the markup is, we have no other
            // option.
            // For example: .field-content-promotional-conten can be by itself
            // or it can be a sibling to an image. This causes boxes to get
            // miscalculated. So if .field-content-promotional-image is a
            // previous sibling we have to apply the style to the parent
            // wrapper.
            let $items = $('.fact-card, .promo-list__card, .field-content-promotional-conten, .field-column-content-content:has(.field-content-promotional-image)', this);

            // Exit if this column doesn't have anything needing resized.
            if ($items.length < 1) {
                return;
            }

            // Remove a selector if it is actually a child of the real box selector.
            $items = $items.filter(function () {
                if (!$(this).prev('.field-content-promotional-image').length) {
                    return $(this)
                }
            });

            // Clear the current height values.
            $items.css('height', 'auto');

            // Select and loop the elements you want to equalise
            $items.each(function () {
                const $this = $(this);
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
    $('.emergency-alert__toggle').on('click', function(e) {
        const $this = $(this);
        $this.closest('.emergency-alert').toggleClass('emergency-alert--collapsed'); //you can list several class names

        if ($this.attr('aria-expanded') === 'true') {
            $this.attr('aria-expanded', 'false')
        } else {
            $this.attr('aria-expanded', 'true')
        }
        e.preventDefault();
    });

    $.urlParam = function(name){
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if (results==null){
         return '';
      }
      else{
         return results[1] || 0;
      }
    }

    $('#email').val(decodeURIComponent($.urlParam('email')));

  // Need a class on center aligned videos to fix width issue;
  $('.align-center').each(function() {
    if ($(this).find('.media--type-remote-video').length > 0) {
      $(this).addClass('align-center--video');
    };
  });

})(jQuery, window);
