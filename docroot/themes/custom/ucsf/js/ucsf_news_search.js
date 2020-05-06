/* eslint-disable */
(function ($) {
  Drupal.behaviors.search_news = {
    attach: function (context) {

      // Toggle Advanced Filter by Advanced Filter Icon.
      $(".search-filter__advanced", context).click(function() {
        openAdvancedFilter();
      });

      // Toggle Subcategory by Label.
      $(".search-filter__dropdown legend", context).click(function() {
        $(this).siblings(".fieldset-wrapper").toggleClass('js-search_filter__category-open');
      });

      $("#edit-field-primary-area-target-id-1006", context).click(function() {
        $("#edit-field-primary-area-target-id-1016", context).click();
        $("#edit-field-primary-area-target-id-1011", context).click();
        $("#edit-field-primary-area-target-id-1021", context).click();
        $("#edit-field-primary-area-target-id-1026", context).click();
      });

      // Toggle Advanced Filter and Focus on Date input by Date Change link.
      $(".search-filter__advanced-date", context).click(function() {
        openAdvancedFilter(true);
      });

      // Toggle Advanced Filter Function.
      function openAdvancedFilter(dateFocus) {
        $(".search-filter__dropdown").toggleClass('js-search_filter__dropdown-open');
        $(".search-filter__top").toggleClass('js-search_filter__top-open');
        $(".fieldset-wrapper").removeClass('js-search_filter__category-open');
        $("body").toggleClass('js-search_filter__is-open');

        if (dateFocus == true) {
          setTimeout(function() {
              $('[data-drupal-selector="edit-field-date-and-time-value-1"]').focus();
            }, 350);
        }
      }

      // Remove filter using filter indicators.
      function removeFilter() {
        const filter_item = $('.search-filter__indicator-item');

        filter_item.click(function () {
          const filter_id = $(this).attr('data-tid');

          // Uncheck term filter.
          $('[value="' + filter_id + '"]').prop("checked", false);

          // Submit filter.
          $('[block="block-exposedformnews-filterpage-1"]').submit();
        })

        // Hide label if there are no active filters.
        if (filter_item.length > 0) {
          // console.log(filter_item);
          $('.search-filter__indicator-list label').removeClass('visually-hidden')
        }
      }

      // PUBLICATION YEAR MULTIRANGE SLIDER START
      $(document).ready(function() {
        // Default values for min and max publication year handles
        // TODO: Update next year
        let currentMinValue = 2019;
        let currentMaxValue = 2020;

        // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
        const $publicationRange = $('.publication-range');
        $publicationRange.slider({
          range:true,
          min: 2000,
          // TODO: Update next year
          max: 2020,
          step: 1,
          values: [currentMinValue, currentMaxValue],

          // Triggered on every mouse move during slide
          slide: function( event, ui ) {
            currentMinValue = ui.values[0];
            currentMaxValue = ui.values[1];
            updateHandleLabel(currentMinValue, currentMaxValue);
          },

          // Triggered after the user slides a handle.
          stop: function( event, ui ) {
            currentMinValue = ui.values[0];
            currentMaxValue = ui.values[1];
            updateHandleLabel(currentMinValue, currentMaxValue);
          },
        })

        // Min and max allowed values
        const  publicationMin= $(".publication-range").slider("option", "min");
        const publicationMax = $(".publication-range").slider("option", "max");

        // HANDLES FOR RANGE SLIDER
        const $handle = $('.ui-slider-handle', context);
        // Update values based off handle position on the track
        const updateHandleLabel = (minValue, maxValue) => {
          $('.min-limit').attr({
            value: minValue,
            placeholder: minValue,
            max: maxValue,
          });

          $('.max-limit').attr({
            value: maxValue,
            placeholder: maxValue,
            min: minValue
          });
        }

        // Minimum Publication Year handle
        const handleMin = $handle.first();
        handleMin.attr({
          'data-value': 'min',
          'data-testid':'puplication-year-min',
        })

        // Create label and input for Minimum Publication Year handle
        const yearLabelMin =
          `<label for='min-limit' class='visually-hidden'>
            Minimum year of publication:
          </label>
          <input type='number' min='${publicationMin}' max='${currentMaxValue}' value ='${currentMinValue}' id='min-limit' class='ui-slider__handle-label min-limit' maxlength='4' pattern='\d{4}'/>`;
        handleMin.append(yearLabelMin);


        // Maximum Publication Year handle
        const handleMax = $handle.last();
        handleMax.attr({
          'data-value': 'max',
          'data-testid':'puplication-year-max',
        })

        // Create label and input for Maximum Publication Year handle
        const yearLabelMax =
          `<label for='max-limit' class='visually-hidden'>
            Maximum year of publication:
          </label>
          <input type='number' min='${currentMinValue}' max='${publicationMax}' value ='${currentMaxValue}' id='max-limit' class='ui-slider__handle-label max-limit' maxlength='4' pattern='\d{4}'/>`;
        handleMax.append(yearLabelMax);

        // TRACK FOR RANGE SLIDER
        // Label for the range track
        const trackLabel =
          `<p class='ui-slider__track-label'>
            <span class='visually-hidden'>Available publication years range from</span>
            <span>${publicationMin}</span>
            <span class='visually-hidden'>to</span>
            <span>${publicationMax}</span>
          </p>`
        $('.publication-range', context).append(trackLabel);
      });
      // PUBLICATION YEAR MULTIRANGE SLIDER END

      removeFilter();
    }
  };

  $(function() {
      var waypoint = $('#edit-count').waypoint({
          handler: function (direction) {

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
