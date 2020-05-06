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

      // PUBLICATION YEAR MULTIRANGE SLIDER START
      $(document).ready(function () {
        // Default values for min and max publication year handles
        // TODO: Update next year
        // let currentMinValue = 2019;
        // let currentMaxValue = 2020;

        // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
        var $publicationRange = $('.publication-range', context);
        $publicationRange.slider({
          range: true,
          min: 2000,
          max: 2020,
          step: 1,
          values: [2019, 2020]

          // Triggered on every mouse move during slide
          // slide: function( event, ui ) {
          //   $('.min-limit').val( ui.values[0] );
          //   $('.min-limit').attr({value:ui.values[0]})

          //   $('.max-limit').val( ui.values[1] );
          //   $('max-limit').attr({value:ui.values[1]})
          // },

          // // Triggered after the user slides a handle.
          // stop: function( event, ui ) {
          //   currentMinValue = ui.values[0];
          //   currentMaxValue = ui.values[1];
          //   updateHandleLabel(currentMinValue, currentMaxValue);
          // },
        });

        var publicationMin = $publicationRange.slider("option", "min");
        var publicationMax = $publicationRange.slider("option", "max");

        // Update values for the floating input
        // const updateHandleLabel = (minValue, maxValue) => {
        //   $('.min-limit').attr({
        //     value: minValue,
        //     placeholder: minValue,
        //     max: maxValue,
        //   });

        //   $('.max-limit').attr({
        //     value: maxValue,
        //     placeholder: maxValue,
        //     min: minValue
        //   });
        // }

        // Minimum Publication Year handle
        var $handle = $('.ui-slider-handle');
        var handleMin = $handle.first();
        handleMin.attr({
          'data-value': 'min',
          'data-testid': 'puplication-year-min'
        });

        // Create label and input for Minimum Publication Year handle
        var yearLabelMin = "<label for='min-limit' class='visually-hidden'>\n              Minimum year of publication:\n            </label>\n            <input type='number' value='2019' min='" + publicationMin + "' id='min-limit' class='ui-slider__handle-label min-limit' maxlength='4' pattern='d{4}'/>\n          ";
        handleMin.append(yearLabelMin);

        // Maximum Publication Year handle
        var handleMax = $handle.last();
        handleMax.attr({
          'data-value': 'max',
          'data-testid': 'puplication-year-max'
        });

        // Create label and input for Maximum Publication Year handle
        var yearLabelMax = "<label for='max-limit' class='visually-hidden'>\n              Maximum year of publication:\n            </label>\n            <input type='number' value='2020' max='" + publicationMax + "' id='max-limit' class='ui-slider__handle-label max-limit' maxlength='4' pattern='d{4}'/>\n          ";
        handleMax.append(yearLabelMax);

        // $('.min-limit').one('focus', function() {
        //   $(this).val('');
        // });

        // $('.max-limit').one('focus', function() {
        //   $(this).val('');
        // });

        // If user typed in a year move handle to that year

        var minLimit = $('.min-limit');
        var maxLimit = $('.max-limit');

        var validateMinMax = function validateMinMax(value) {
          var year = new Date().getFullYear();
          var newValue = parseInt(value);

          if (newValue < 2000) {
            newValue = 2000;
          } else if (newValue > year) {
            newValue = year;
          }

          return newValue;
        };

        minLimit.change(function () {
          var newYear = validateMinMax($(this).val());
          $publicationRange.slider('values', 0, newYear);
          console.log($(this).val());
        });

        maxLimit.change(function () {
          var newYear = validateMinMax($(this).val());
          $publicationRange.slider('values', 1, newYear);
          console.log($(this).val());
        });

        // TRACK FOR RANGE SLIDER
        // Label for the range track
        var trackLabel = "<p class='ui-slider__track-label'>\n            <span class='visually-hidden'>Available publication years range from</span>\n            <span>" + publicationMin + "</span>\n            <span class='visually-hidden'>to</span>\n            <span>" + publicationMax + "</span>\n          </p>";
        $('.publication-range', context).append(trackLabel);
      });
      // PUBLICATION YEAR MULTIRANGE SLIDER END

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
