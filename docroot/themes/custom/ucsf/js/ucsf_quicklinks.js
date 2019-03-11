/* eslint-disable */
(function ($) {
  Drupal.behaviors.quicklinks = {
    attach: function (context) {
  
      $(".quicklinks, .quicklinks-close", context).click(function() {
        $(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".main").toggleClass('js-quicklinks-open');
        // @todo Add scroll up function on small screens because quicklinks toggle is fixed?
      });

    }
  };
  
})(jQuery);
