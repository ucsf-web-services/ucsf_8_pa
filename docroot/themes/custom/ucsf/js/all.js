/* eslint-disable */
(function ($) {
  Drupal.behaviors.dieToolbardie = {
    attach: function (context) {
      window.matchMedia('(min-width: 975px)').addListener( function(event) {
        event.matches ? $('#toolbar-item-administration', context).click() : $('.toolbar-item.is-active', context).click();
      });
    }
  };
  
  
  Drupal.behaviors.slickNav = {
    attach: function (context, settings) {
  
      $('#block-ucsf-main-menu').slicknav({
        prependTo: '.slicknav-placeholder',
        label: '',
      });
    }
  };

})(jQuery);
