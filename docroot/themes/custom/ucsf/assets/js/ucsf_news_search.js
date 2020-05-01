"use strict";

/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function attach(context) {

      // Toggle Advanced Filter by Advanced Filter Icon.
      $(".search-filter__advanced", context).click(function () {
        openAdvancedFilter();
      });

      // Toggle Subcategory by Label.
      $(".search-filter__dropdown legend", context).click(function () {
        $(this).siblings(".fieldset-wrapper").toggleClass('js-search_filter__category-open');
      });

      $("#edit-field-primary-area-target-id-1006", context).click(function () {
        $("#edit-field-primary-area-target-id-1016", context).click();
        $("#edit-field-primary-area-target-id-1011", context).click();
        $("#edit-field-primary-area-target-id-1021", context).click();
        $("#edit-field-primary-area-target-id-1026", context).click();
      });

      // Toggle Advanced Filter and Focus on Date input by Date Change link.
      $(".search-filter__advanced-date", context).click(function () {
        openAdvancedFilter(true);
      });

      // Toggle Advanced Filter Function.
      function openAdvancedFilter(dateFocus) {
        $(".search-filter__dropdown").toggleClass('js-search_filter__dropdown-open');
        $(".search-filter__top").toggleClass('js-search_filter__top-open');
        $(".fieldset-wrapper").removeClass('js-search_filter__category-open');
        $("body").toggleClass('js-search_filter__is-open');

        if (dateFocus == true) {
          setTimeout(function () {
            $('[data-drupal-selector="edit-field-date-and-time-value-1"]').focus();
          }, 350);
        }
      }

      // Remove filter using filter indicators.
      function removeFilter() {
        var filter_item = $('.search-filter__indicator-item');

        filter_item.click(function () {
          var filter_id = $(this).attr('data-tid');

          // Uncheck term filter.
          $('[value="' + filter_id + '"]').prop("checked", false);

          // Submit filter.
          $('[block="block-exposedformnews-filterpage-1"]').submit();
        });

        // Hide label if there are no active filters.
        if (filter_item.length > 0) {
          // console.log(filter_item);
          $('.search-filter__indicator-list label').removeClass('visually-hidden');
        }
      }

      // MULTIRANGE SLIDER START
      $(document).ready(function () {
        $("#slider").slider({
          min: 0,
          max: 100,
          step: 1,
          values: [10, 90],
          slide: function slide(event, ui) {
            for (var i = 0; i < ui.values.length; ++i) {
              $("input.sliderValue[data-index=" + i + "]").val(ui.values[i]);
            }
          }
        });
        var $handle = $(".ui-slider-handle", slider);
        $handle.first().addClass("multirange__handle publication-range__handle");
        $handle.first().attr({
          'data-value': 'min',
          'data-testid': 'puplication-year-min'
        });
        $handle.last().addClass("multirange__handle publication-range__handle");
        $handle.last().attr({
          'data-value': 'max',
          'data-testid': 'puplication-year-max'
        });
        // $("input.sliderValue").change(function() {
        //     var $this = $(this);
        //     $("#slider").slider("values", $this.data("index"), $this.val());
        // });
      });
      // MULTIRANGE SLIDER END

      removeFilter();
    }
  };

  $(function () {
    var waypoint = $('#edit-count').waypoint({
      handler: function handler(direction) {

        if (direction == 'down') {
          $('.block-views-exposed-filter-blocknews-filter-page-1').addClass('scrolled');
        } else {
          $('.block-views-exposed-filter-blocknews-filter-page-1').removeClass('scrolled');
        }
      },
      offset: '0%'
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_news_search.js.map
