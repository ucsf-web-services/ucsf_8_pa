/* eslint-disable */
(function ($) {
  Drupal.behaviors.responsiveMenu = {
    attach: function (context, settings) {

      $('.ohm-hamburger-menu', context).on('click', function(e) {
        $('[data-hide-below-narrow]').toggle();
        $(e.target).closest('.ohm-hamburger-menu').toggleClass('open');
        $('body', context).toggleClass('is-open--mobile-nav');
      });

      $('.ohm-navbar ul.menu .menu-item--expanded > a', context).parent().addClass('expandable').append('<a class="ohm-mobile-toggle"><span class="open"></span></a>');

      $('.ohm-mobile-toggle', context).on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(e.target).closest('.expandable').toggleClass('expanded');
        $(e.target).siblings('.menu').toggle();
      });

      // Search placeholder.
      $('.ohm-navbar__search--container input[type="search"]', context).prop('placeholder', 'Search');

      // Show on resize, big screens.
      $(window).on('resize', function(e) {
        if (window.innerWidth > 1024) {
          $('[data-hide-below-narrow="true"]').show();
        }
      });

    }
  };
})(jQuery);
