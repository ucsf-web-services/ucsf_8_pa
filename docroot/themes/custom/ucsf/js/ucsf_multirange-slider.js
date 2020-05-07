(($ => {
  // Wait for the document to be ready.
  $(() => {
    const $selectMin = $("#edit-field-date-and-time-value-1");
    const $selectMax = $("#edit-field-date-and-time-value-2");

    // Min and Max year range.
    const minRange = parseInt($selectMin.find('option:nth-child(2)').text());
    const maxRange = parseInt($selectMax.find('option:nth-child(2)').text());

    // Default values for min and max publication year handles
    let currentMinValue = minRange;
    let currentMaxValue = maxRange;



    // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
    const $publicationRange = $('.publication-range');
    $publicationRange.slider({
      range: true,
      min: minRange,
      max: maxRange,
      step: 1,
      values: [currentMinValue, currentMaxValue],

      // Triggered on every mouse move during slide
      slide: function(event, ui) {
        currentMinValue = ui.values[0];
        currentMaxValue = ui.values[1];

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
      }
    });

    // Minimum Publication Year handle
    const handleMin = $('.ui-slider-handle').first();
    handleMin.attr({
      'data-testid':'puplication-year-min',
    });

    // Create floating labe for Minimum Publication Year handle
    const yearLabelMin =
      `<span class='ui-slider__handle-label min-limit'>
        <span class='visually-hidden'>
          Minimum year of publication:
        </span>
        ${currentMinValue}
      </span>`;
    handleMin.append(yearLabelMin);


    // Maximum Publication Year handle
    const handleMax = $('.ui-slider-handle').last();
    handleMax.attr({
      'data-testid':'puplication-year-max',
    });

    // Create floating label for Maximum Publication Year handle
    const yearLabelMax =
      `<span class='ui-slider__handle-label max-limit'>
        <span class='visually-hidden'>
          Maximum year of publication:
        </span>
        ${currentMaxValue}
      </span>`;
    handleMax.append(yearLabelMax);

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
  });
}))(jQuery);
