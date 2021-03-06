(($ => {
  // Wait for the document to be ready.
  $(() => {
    const $publicationRange = $('.publication-range');

    const $selectMin = $('#edit-field-date-and-time-value-1--2');
    const $selectMax = $('#edit-field-date-and-time-value-2--2');

    // Min and Max year range.
    const minRange = parseInt($selectMin.find('option:nth-child(2)').text());
    const maxRange = parseInt($selectMax.find('option:nth-child(2)').text());

    /**
     * Get the current minimum value after preventing min/max from overlapping.
     *
     * @param {number} minValue The minimum value of the current slider.
     * @param {number} maxValue The maximum value of the current slider.
     * @param {number} minLimit The lowest the minimum value can go.
     *   Defaults to the minimum range allowed in the slider.
     */
    const getCurrentMinAfterOverlap = (minValue, maxValue, minLimit = minRange) => {
      if (minValue >= maxValue) {
        if (minValue <= minLimit) {
          minValue = minLimit
        } else {
          minValue -= 1;
        }
      }

      return minValue
    }

    /**
     * Sync multirange slider with filter data from dropdowns.
     */
    const updateSlider = () => {
      // Find what dropdown values are selected
      let selectMinOption = parseInt($selectMin.find(':selected').text());
      let selectMaxOption = parseInt($selectMax.find(':selected').text());

      // Sanitize.
      if (isNaN(selectMinOption)) {
        selectMinOption = minRange;
      };
      if (isNaN(selectMaxOption)) {
        selectMaxOption = maxRange;
      };

      selectMinOption = getCurrentMinAfterOverlap(selectMinOption, selectMaxOption)

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
    const createSlider = () => {
      // Default values for min and max publication year handles
      let currentMinValue = getCurrentMinAfterOverlap(minRange, maxRange);
      let currentMaxValue = maxRange;

      // JQUERY SLIDER UI OBJECT
      $publicationRange.slider({
        range: true,
        min: minRange,
        max: maxRange,
        step: 1,
        values: [currentMinValue, currentMaxValue],
        animate: "fast",

        // Triggered on every mouse move during slide
        slide: function(event, ui) {
          currentMinValue = ui.values[0];
          currentMaxValue = ui.values[1];

          if (currentMinValue >= currentMaxValue) {
            return false;
          }

          // Update floating labels for handles
          $('.min-limit').text(currentMinValue);
          $('.max-limit').text(currentMaxValue);
        },

        stop: function(event, ui) {
          currentMinValue = ui.values[0];
          currentMaxValue = ui.values[1];

          // Find out what the select value of a chosen date is.
          let $selectMinOptionVal = $selectMin.find(`option:contains(${currentMinValue})`).val();
          let $selectMaxOptionVal = $selectMax.find(`option:contains(${currentMaxValue})`).val();

          // Sanitize.
          if ($selectMinOptionVal === undefined) {
            $selectMinOptionVal = 'All'
          }
          if ($selectMaxOptionVal === undefined) {
            $selectMaxOptionVal = 'All'
          }

          // Set the selected option.
          $selectMin.val($selectMinOptionVal);
          $selectMax.val($selectMaxOptionVal);
        },
      });

      // Minimum Publication Year handle
      const $handleMin = $('.ui-slider-handle').first();
      $handleMin.addClass('ui-slider-handle--min')
      $handleMin.attr({
        'data-testid':'publication-year-min',
      });

      // Create floating label for Minimum Publication Year handle
      const yearLabelMin =
        `<span class='visually-hidden'>Minimum year of publication:</span>
        <span class='ui-slider__handle-label min-limit'></span>`;
      $handleMin.append(yearLabelMin);


      // Maximum Publication Year handle
      const $handleMax = $('.ui-slider-handle').last();
      $handleMax.addClass('ui-slider-handle--max')
      $handleMax.attr({
        'data-testid':'publication-year-max',
      });

      // Create floating label for Maximum Publication Year handle
      const yearLabelMax =
        `<span class='visually-hidden'>Maximum year of publication:</span>
        <span class='ui-slider__handle-label max-limit'></span>`;
      $handleMax.append(yearLabelMax);

      updateSlider();
    };

    /**
     * Initialize the range slider if a desktop display has been detected.
     *
     * @param {MediaQueryList} mql
     */
    const desktopDetect = (mql) => {
      // Desktop
      if (mql.matches) {
        createSlider();
      } else {
        // Remove the range slider in mobile.
        try {
          $publicationRange.slider('destroy').empty();
        } catch (e) {}
      }
    }

    /**
     * Watch for when the screen resizes horizontally from mobile to desktop.
     */
    const watchResize = () => {
      // Use MatchMedia to ensure that the range slider only happens in desktop.
      const mql = matchMedia('(min-width: 1027px)');
      // Detect Desktop on page load.
      desktopDetect(mql);
      // Watch to see if the page size changes.
      mql.addListener(desktopDetect);
    }

    // Initialize.
    watchResize();

  });
}))(jQuery);
