(($ => {
  // Wait for the document to be ready.
  $(() => {
    // PUBLICATION YEAR MULTIRANGE SLIDER START
    // Default values for min and max publication year handles
    const currentYear = new Date().getFullYear();
    let currentMinValue = 2019;
    let currentMaxValue = currentYear;

    // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
    const $publicationRange = $('.publication-range');
    $publicationRange.slider({
      range:true,
      min: 2000,
      max: currentYear,
      step: 1,
      values: [currentMinValue, currentMaxValue],

      // Triggered on every mouse move during slide
      slide: function( event, ui ) {
        currentMinValue = ui.values[0];
        currentMaxValue = ui.values[1];
        // Update floating labels for handles
        $('.min-limit').text(currentMinValue);
        $('.max-limit').text(currentMaxValue);

      },
    });

    const publicationMin = $publicationRange.slider("option", "min");
    const publicationMax = $publicationRange.slider("option", "max");

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
        <span>${publicationMin}</span>
        <span class='visually-hidden'>to</span>
        <span>${publicationMax}</span>
      </p>`;
    $('.publication-range').append(trackLabel);
    // PUBLICATION YEAR MULTIRANGE SLIDER END
  });
}))(jQuery);
