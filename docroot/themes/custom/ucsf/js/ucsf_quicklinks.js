/* eslint-disable */
(function ($) {

  $(document).ready(function() {
    setTimeout(function() {
      $('body').removeClass('loading'); // 'quicklinks-intro' CSS animation breaks if class is removed before animation is finished.
    }, 5000);
  });

  Drupal.behaviors.quicklinks = {
    attach: function (context) {

      $(".quicklinks-trigger, .quicklinks-close", context).click(function() {
        //$(".quicklinks-menu").toggleClass('js-quicklinks-open');
        $(".quicklinks").toggleClass('js-quicklinks-open');
        $(".header-region").toggleClass('js-quicklinks-open');
      });

    }
  };

})(jQuery);
