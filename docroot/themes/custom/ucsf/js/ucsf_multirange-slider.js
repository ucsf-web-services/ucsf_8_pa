(($ => {
  // Wait for the document to be ready.
  $(() => {
    const $dropdownPanel = $('.search-filter__dropdown');

    const $selectMin = $('#edit-field-date-and-time-value-1');
    const $selectMax = $('#edit-field-date-and-time-value-2');

    // Min and Max year range.
    const minRange = parseInt($selectMin.find('option:nth-child(2)').text());
    const maxRange = parseInt($selectMax.find('option:nth-child(2)').text());

    // Default values for min and max publication year handles
    let currentMinValue = minRange;
    let currentMaxValue = maxRange;

    if (currentMinValue >= currentMaxValue) {
      currentMinValue -= 1;
    }

    // Only execute subnav extend / collapse code in mobile
    const desktopDetect = (event) => {
      const $publicationRange = $('.publication-range');

      // Desktop
      if (event.matches) {
        // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
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
        $handleMin.attr({
          'data-testid':'puplication-year-min',
        });

        // Create floating labe for Minimum Publication Year handle
        const yearLabelMin =
          `<span class='visually-hidden'>Minimum year of publication:</span>
          <span class='ui-slider__handle-label min-limit'></span>`;
        $handleMin.append(yearLabelMin);


        // Maximum Publication Year handle
        const $handleMax = $('.ui-slider-handle').last();
        $handleMax.attr({
          'data-testid':'puplication-year-max',
        });

        // Create floating label for Maximum Publication Year handle
        const yearLabelMax =
          `<span class='visually-hidden'>Maximum year of publication:</span>
          <span class='ui-slider__handle-label max-limit'></span>`;
        $handleMax.append(yearLabelMax);

        // TRACK FOR RANGE SLIDER
        // Label for the range track
        const trackLabel =
          `<p class='ui-slider__track-label'>
            <span class='visually-hidden'>Available publication years range from</span>
            <span>${minRange}</span>
            <span class='visually-hidden'>to</span>
            <span>${maxRange}</span>
          </p>`;
        $('.publication-range').append(trackLabel);


        // MAKING SLIDER HANDLES STAY AT THE POSITION OF PREVIOUSLY SUBMITTED QUERY WHEN PANEL IS REOPENED
        // Sync multirange slider with filter data from dropdowns.
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

          if (selectMinOption >= selectMaxOption) {
            selectMinOption -= 1;
          }

          // Provide selected values to the multirange slider
          $publicationRange.slider('values', 0, selectMinOption);
          $publicationRange.slider('values', 1, selectMaxOption);

          // Update floating labels for multirange slider handles
          $('.min-limit').text(selectMinOption);
          $('.max-limit').text(selectMaxOption);
        };

        // Check if Advanced Filter Panel is open.
        $('.search-filter__advanced').one().click(function() {
          if ($dropdownPanel.hasClass('js-search_filter__dropdown-open')) {
            updateSlider();
          }
        });
        updateSlider()
      } else {
        $publicationRange.slider('destroy').empty();
      }
    }

    // Use MatchMedia to ensure that subnav expand/collapse is only happening in mobile
    const mql = matchMedia('(min-width: 1027px)');
    // Detect Desktop on page load.
    desktopDetect(mql);
    // Watch to see if the page size changes.
    mql.addListener(desktopDetect);
  });
}))(jQuery);
