'use strict';

(function ($) {
  // Wait for the document to be ready.
  $(function () {
    var $publicationRange = $('.publication-range');

    var $selectMin = $('#edit-field-date-and-time-value-1--2');
    var $selectMax = $('#edit-field-date-and-time-value-2--2');

    // Min and Max year range.
    var minRange = parseInt($selectMin.find('option:nth-child(2)').text());
    var maxRange = parseInt($selectMax.find('option:nth-child(2)').text());

    /**
     * Get the current minimum value after preventing min/max from overlapping.
     *
     * @param {number} minValue The minimum value of the current slider.
     * @param {number} maxValue The maximum value of the current slider.
     * @param {number} minLimit The lowest the minimum value can go.
     *   Defaults to the minimum range allowed in the slider.
     */
    var getCurrentMinAfterOverlap = function getCurrentMinAfterOverlap(minValue, maxValue) {
      var minLimit = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : minRange;

      if (minValue >= maxValue) {
        if (minValue <= minLimit) {
          minValue = minLimit;
        } else {
          minValue -= 1;
        }
      }

      return minValue;
    };

    /**
     * Sync multirange slider with filter data from dropdowns.
     */
    var updateSlider = function updateSlider() {
      // Find what dropdown values are selected
      var selectMinOption = parseInt($selectMin.find(':selected').text());
      var selectMaxOption = parseInt($selectMax.find(':selected').text());

      // Sanitize.
      if (isNaN(selectMinOption)) {
        selectMinOption = minRange;
      };
      if (isNaN(selectMaxOption)) {
        selectMaxOption = maxRange;
      };

      selectMinOption = getCurrentMinAfterOverlap(selectMinOption, selectMaxOption);

      // Provide selected values to the multirange slider
      $publicationRange.slider('values', 0, selectMinOption);
      $publicationRange.slider('values', 1, selectMaxOption);

      // Update floating labels for multirange slider handles
      $('.min-limit').text(selectMinOption);
      $('.max-limit').text(selectMaxOption);
    };

    /**
     * Create the jQuery UI Range Slider
     *
     * https://api.jqueryui.com/slider/#event-slide
     */
    var createSlider = function createSlider() {
      // Default values for min and max publication year handles
      var currentMinValue = getCurrentMinAfterOverlap(minRange, maxRange);
      var currentMaxValue = maxRange;

      // JQUERY SLIDER UI OBJECT
      $publicationRange.slider({
        range: true,
        min: minRange,
        max: maxRange,
        step: 1,
        values: [currentMinValue, currentMaxValue],
        animate: "fast",

        // Triggered on every mouse move during slide
        slide: function slide(event, ui) {
          currentMinValue = ui.values[0];
          currentMaxValue = ui.values[1];

          if (currentMinValue >= currentMaxValue) {
            return false;
          }

          // Update floating labels for handles
          $('.min-limit').text(currentMinValue);
          $('.max-limit').text(currentMaxValue);
        },

        stop: function stop(event, ui) {
          currentMinValue = ui.values[0];
          currentMaxValue = ui.values[1];

          // Find out what the select value of a chosen date is.
          var $selectMinOptionVal = $selectMin.find('option:contains(' + currentMinValue + ')').val();
          var $selectMaxOptionVal = $selectMax.find('option:contains(' + currentMaxValue + ')').val();

          // Sanitize.
          if ($selectMinOptionVal === undefined) {
            $selectMinOptionVal = 'All';
          }
          if ($selectMaxOptionVal === undefined) {
            $selectMaxOptionVal = 'All';
          }

          // Set the selected option.
          $selectMin.val($selectMinOptionVal);
          $selectMax.val($selectMaxOptionVal);
        }
      });

      // Minimum Publication Year handle
      var $handleMin = $('.ui-slider-handle').first();
      $handleMin.addClass('ui-slider-handle--min');
      $handleMin.attr({
        'data-testid': 'publication-year-min'
      });

      // Create floating label for Minimum Publication Year handle
      var yearLabelMin = '<span class=\'visually-hidden\'>Minimum year of publication:</span>\n        <span class=\'ui-slider__handle-label min-limit\'></span>';
      $handleMin.append(yearLabelMin);

      // Maximum Publication Year handle
      var $handleMax = $('.ui-slider-handle').last();
      $handleMax.addClass('ui-slider-handle--max');
      $handleMax.attr({
        'data-testid': 'publication-year-max'
      });

      // Create floating label for Maximum Publication Year handle
      var yearLabelMax = '<span class=\'visually-hidden\'>Maximum year of publication:</span>\n        <span class=\'ui-slider__handle-label max-limit\'></span>';
      $handleMax.append(yearLabelMax);

      updateSlider();
    };

    /**
     * Initialize the range slider if a desktop display has been detected.
     *
     * @param {MediaQueryList} mql
     */
    var desktopDetect = function desktopDetect(mql) {
      // Desktop
      if (mql.matches) {
        createSlider();
      } else {
        // Remove the range slider in mobile.
        try {
          $publicationRange.slider('destroy').empty();
        } catch (e) {}
      }
    };

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    var watchResize = function watchResize() {
      // Use MatchMedia to ensure that the range slider only happens in desktop.
      var mql = matchMedia('(min-width: 1027px)');
      // Detect Desktop on page load.
      desktopDetect(mql);
      // Watch to see if the page size changes.
      mql.addListener(desktopDetect);
    };

    // Initialize.
    watchResize();
  });
})(jQuery);
//# sourceMappingURL=ucsf_multirange-slider.js.map
