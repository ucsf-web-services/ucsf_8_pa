/* eslint-disable */
(function ($) {
  Drupal.behaviors.quicklinks = {
    attach: function (context) {
  
      $(".quicklinks-trigger, .quicklinks-close", context).click(function() {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".quicklinks").toggleClass('js-quicklinks-open');
      });

    }
  };
  
})(jQuery);
