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
        self.height(totalHeight + 58);
        self.find('.menu-child--label').width(totalHeight + 10);
        //return false;
      });
    }
  };
})(jQuery);
//# sourceMappingURL=all.js.map
