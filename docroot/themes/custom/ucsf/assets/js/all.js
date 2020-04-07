'use strict';

/* eslint-disable */
(function ($) {
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

    Drupal.behaviors.desktopDropdownHeight = {
        attach: function attach(context, settings) {
            $(window, context).once('menu-desktop').each(function () {

                // Set dropdown heights based on the content within.
                var dropdown = $('[data-level="level-0"]');

                var resizeMenuPanel = function resizeMenuPanel() {

                    dropdown.each(function () {

                        var self = $(this);
                        // reset the height on screen resize
                        self.height("auto");
                        var mainHeight = self.height();
                        var childMenu = self.find('.menu-child--wrapper');
                        var totalHeight = mainHeight;
                        var childHeight = 0;

                        childMenu.each(function () {
                            childHeight = $(this)[0].clientHeight;
                            if (childHeight + 48 >= mainHeight) {
                                totalHeight = childHeight;
                            }
                        });

                        self.height(totalHeight + 68);
                        self.find('.menu-child--label').width(totalHeight + 20);
                    });
                };

                // Select and loop the container element of the elements you want to equalise
                resizeMenuPanel();

                // At the end of a screen resize.
                var resizeTimer = null;
                $(window).on('resize', context, function () {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function () {
                        // resizing has "stopped".
                        resizeMenuPanel();
                    }, 250);
                });

                var nolink = $('.menu-item a.nolink');
                nolink.each(function () {
                    $(this).on('click', function (event) {
                        event.preventDefault();
                    });
                });

                $('.menu-item-parent').click(function () {
                    var $this = $(this);
                    // do not add 'menu-item-open' class if the menu item is search
                    if ($this.hasClass('search')) {
                        $this.siblings().removeClass('menu-item-open');
                        return;
                    };
                    $this.addClass('menu-item-open').siblings().removeClass('menu-item-open');
                });

                $('.menu-item-close').click(function (e) {
                    e.stopPropagation(); // Key line to work perfectly
                    if ($(this).parent().parent().hasClass('menu-item-open')) {
                        $(this).parent().parent().removeClass('menu-item-open');
                    };
                });

                // Shows menus when it's being tabbed through
                var $dropdown = $('.menu-child--wrapper', context);
                $dropdown.on('focusin', function () {
                    // Menu dropdowns open on focus.
                    $(this).parents('.menu-item--expanded').addClass('menu-item-open');
                });

                // Menu dropdown closes when focus is out.
                $dropdown.on('focusout', function () {
                    var $this = $(this);
                    // Waits and only removes class if newly focused element is outside the dropdown
                    setTimeout(function () {
                        // Closes second level subnav
                        if ($(document.activeElement).parents('.menu-child--wrapper').length === 0) {
                            $this.parents('.menu-item-parent').removeClass('menu-item-open');
                        }
                        // Closes the third level subnav if the current focused element is not in it.
                        else if ($this.has(document.activeElement).length === 0) {
                                $this.parents('.menu-item--expanded').first().removeClass('menu-item-open');
                            }
                    }, 50);
                });
            });
        }
    };

    Drupal.behaviors.searchMenuAction = {

        attach: function attach(context, settings) {
            var $searchToggle = $('.menu-parent--wrapper .menu-item.search > a', context);
            var $search = $('.wrapper--search-menu', context);
            $searchToggle.click(function (e) {
                e.preventDefault();
                $search.toggleClass('active');
                $searchToggle.toggleClass('active');
                if ($searchToggle.hasClass('active')) {
                    $('.wrapper--search-menu .home-search__form-input').focus();
                }
            });

            $('.menu-parent--wrapper .menu-item', context).click(function (e) {
                // If other menu item is clicked close search form
                if (!$(this).hasClass('search')) {
                    $search.removeClass('active');
                    $searchToggle.removeClass('active');
                }
            });

            //Search form opens when focus is inside.
            $search.on('focusin', function () {
                $search.addClass('active');
                $searchToggle.addClass('active'); // changes toggle icon
            });

            //Search form closes when tabbing away.
            $search.on('focusout', function () {
                //Wait and only remove classes if newly focused element is outside the search form
                setTimeout(function () {
                    // When browser cant find activeElement it returns <body> or null
                    // which triggers the false positive for document.activeElement.closest('.wrapper--search-menu') === null
                    // Clicking on the label inside search box caused this behavior, since labels don't receive focus
                    if (document.activeElement === document.body || document.activeElement === null) {
                        return;
                    }

                    // Close the search box if the currently focused el.
                    //  is not inside the search box
                    if (document.activeElement.closest('.wrapper--search-menu') === null) {
                        $search.removeClass('active');
                        $searchToggle.removeClass('active');
                    }
                    // delay needs to be at least 150 to avoid a race condition with $searchToggle.toggleClass('active');
                }, 150);
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
        attach: function attach(context, settings) {
            var version = detectIE();

            if (version) {
                if (Number(version) <= 11) {
                    $('body', context).once('fixExplorer').addClass('explorer');
                    $('.skip-link').once('fixExplorer').after('<div class="ie-message">' + 'Looks like you’re visiting us on Internet Explorer. For the best UCSF.edu experience, ' + 'please use <a href="https://www.google.com/chrome/" target="_blank">Chrome</a> or <a href="https://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox</a>. ' + '</div>');
                }
            }
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

    // for emergency alerts data-emergency-alert-toggle
    $('.emergency-alert__toggle').on('click', function (e) {
        var $this = $(this);
        $('.emergency-alert').toggleClass("emergency-alert--collapsed"); //you can list several class names

        if ($this.attr("aria-expanded") === "true") {
            $this.attr("aria-expanded", "false");
        } else {
            $this.attr("aria-expanded", "true");
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

    // Main menu search form redirect
    Drupal.behaviors.mainMenuSearchFilter = {
        attach: function attach(context, settings) {
            // Selector for the form.
            var $form = $('.search__form', context);
            $form.submit(function () {
                var $this = $(this);
                // Find checked radio button inside the form and get it's value.
                var option = $this.find('.search-filter__radio:checked').val();
                // If value is "News" redirect to news filter search.
                if (option === 'News') {
                    $this.attr('action', '/news/filter');
                    $this.find('.home-search__form-input').attr('name', 'combine');
                }

                return true;
            });
        }
    };
})(jQuery);
//# sourceMappingURL=all.js.map
