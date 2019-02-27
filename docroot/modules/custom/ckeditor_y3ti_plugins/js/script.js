/**
 * @file
 * Scripts for UCSF dom
 *
 */
(function ($, Drupal, drupalSettings) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  Drupal.behaviors.dom = {
    attach: function (context, settings) {
      _this = this;
      _this.slickNews();

    },
    slickNews: function() {
      if ($('.field--name-field-spotlight-images').length) {

        $('.field--name-field-spotlight-images').slick({
          dots: true,
          infinite: true,
          speed: 300,
          slidesToShow: 1
        });
      }
    },
  };

})(jQuery, Drupal, drupalSettings);
