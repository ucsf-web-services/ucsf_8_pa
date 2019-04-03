'use strict';

/* eslint-disable */
(function ($) {
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
        label: '',
        openedSymbol: '<i class="fas fa-angle-up">',
        closedSymbol: '<i class="fas fa-angle-down">'
      });
    }
  };

  Drupal.behaviors.desktopDropdownHeight = {
    attach: function attach(context, settings) {

      // Set dropdown heights based on the content within.
      var dropdown = $('[data-level="level-0"]');

      dropdown.each(function () {

        var self = $(this);
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
        childMenu.each(function () {
          $(this).height(totalHeight);
        });
        self.height(totalHeight + 68);
        self.find('.menu-child--label').width(totalHeight + 20);
        //return false;
      });
    }
  };

  Drupal.behaviors.searchMenuAction = {

    attach: function attach(context, settings) {
      $('.menu-parent--wrapper .menu-item.search > a', context).click(function (e) {
        e.preventDefault();
        $('.wrapper--search-menu').toggleClass('active');
        $('.menu-parent--wrapper .menu-item.search > a').toggleClass('active');
        $('.wrapper--search-menu .home-search__form-input').focus();
      });

      $('.menu-parent--wrapper .menu-item', context).hover(function (e) {
        if ($(this).hasClass('search')) {
          //console.log('search menu');
        } else {
          $('.wrapper--search-menu').removeClass('active');
          $('.menu-parent--wrapper .menu-item.search > a').removeClass('active');
        }
      });
    }
  };

  /**
   * Idea came from Stackoverflow
   * Basically wrap all your breakpoints not IE compatible with the body:not(.explorer) {} expression
   * https://stackoverflow.com/questions/15388967/possible-to-disable-media-queries-or-force-a-resolution-reason-allow-an-iphon
   */
  Drupal.behaviors.fixExplorer = {
    attach: function attach(context, settings) {

      if (detectIE()) {
        $('body').addClass('explorer');
      }
    }
  };

  /**
   * detect IE
   * returns version of IE or false, if browser is not Internet Explorer
   */
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
})(jQuery);
//# sourceMappingURL=all.js.map
