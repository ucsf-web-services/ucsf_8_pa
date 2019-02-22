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
        prependTo: '.slicknav-placeholder',
        label: '',
        openedSymbol: '<i class="fas fa-angle-up">',
        closedSymbol: '<i class="fas fa-angle-down">'
      });
    }
  };
})(jQuery);
//# sourceMappingURL=all.js.map
