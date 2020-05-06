'use strict';

(function ($) {
  // Wait for the document to be ready.
  $(function () {
    // PUBLICATION YEAR MULTIRANGE SLIDER START
    // Default values for min and max publication year handles
    var currentYear = new Date().getFullYear();
    var currentMinValue = 2019;
    var currentMaxValue = currentYear;

    // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
    var $publicationRange = $('.publication-range');
    $publicationRange.slider({
      range: true,
      min: 2000,
      max: currentYear,
      step: 1,
      values: [currentMinValue, currentMaxValue],

      // Triggered on every mouse move during slide
      slide: function slide(event, ui) {
        currentMinValue = ui.values[0];
        currentMaxValue = ui.values[1];
        // Update floating labels for handles
        $('.min-limit').text(currentMinValue);
        $('.max-limit').text(currentMaxValue);
      }
    });

    var publicationMin = $publicationRange.slider("option", "min");
    var publicationMax = $publicationRange.slider("option", "max");

    // Minimum Publication Year handle
    var handleMin = $('.ui-slider-handle').first();
    handleMin.attr({
      'data-testid': 'puplication-year-min'
    });

    // Create floating labe for Minimum Publication Year handle
    var yearLabelMin = '<span class=\'ui-slider__handle-label min-limit\'>\n        <span class=\'visually-hidden\'>\n          Minimum year of publication:\n        </span>\n        ' + currentMinValue + '\n      </span>';
    handleMin.append(yearLabelMin);

    // Maximum Publication Year handle
    var handleMax = $('.ui-slider-handle').last();
    handleMax.attr({
      'data-testid': 'puplication-year-max'
    });

    // Create floating label for Maximum Publication Year handle
    var yearLabelMax = '<span class=\'ui-slider__handle-label max-limit\'>\n        <span class=\'visually-hidden\'>\n          Maximum year of publication:\n        </span>\n        ' + currentMaxValue + '\n      </span>';
    handleMax.append(yearLabelMax);

    // TRACK FOR RANGE SLIDER
    // Label for the range track
    var trackLabel = '<p class=\'ui-slider__track-label\'>\n        <span class=\'visually-hidden\'>Available publication years range from</span>\n        <span>' + publicationMin + '</span>\n        <span class=\'visually-hidden\'>to</span>\n        <span>' + publicationMax + '</span>\n      </p>';
    $('.publication-range').append(trackLabel);
    // PUBLICATION YEAR MULTIRANGE SLIDER END
  });
})(jQuery);
//# sourceMappingURL=ucsf_multirange-slider.js.map
