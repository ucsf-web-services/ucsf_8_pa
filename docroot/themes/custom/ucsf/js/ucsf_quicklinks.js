/* eslint-disable */
(function ($) {
  Drupal.behaviors.quicklinks = {
    attach: function (context) {
  
      $(".quicklinks, .quicklinks-close", context).click(function() {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
      });

    }
  };
  
})(jQuery);
