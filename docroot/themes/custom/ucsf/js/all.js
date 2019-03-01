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
        duplicate: false,
        prependTo: '.slicknav-placeholder',
        label: '',
        openedSymbol: '<i class="fas fa-angle-up">',
        closedSymbol: '<i class="fas fa-angle-down">',
      });
    }
  };
  
  Drupal.behaviors.desktopDropdownHeight = {
    attach: function (context, settings) {

      // Set dropdown heights based on the content within.
      var dropdown = $('[data-level="level-0"]');
  
      dropdown.each(function() {
        
        var self = $(this);
        var childMenuHeight = self.find('.menu-child--menu').height();
        var grandChildMenuHeight = self.find('[data-level="level-1"] > .menu-child--menu').height();
        
        if (grandChildMenuHeight > childMenuHeight) {
          var dropdDownHeight = grandChildMenuHeight + 160;
        } else {
          var dropdDownHeight = childMenuHeight + 128;
        }
        
        self.height(dropdDownHeight);
        self.find('.menu-child--label').width(dropdDownHeight - 48);
        
      })
    }
  };

})(jQuery);
