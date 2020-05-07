'use strict';

(function ($) {
  // Wait for the document to be ready.
  $(function () {
    var $dropdownPanel = $('.search-filter__dropdown');

    var $selectMin = $('#edit-field-date-and-time-value-1');
    var $selectMax = $('#edit-field-date-and-time-value-2');

    // Min and Max year range.
    var minRange = parseInt($selectMin.find('option:nth-child(2)').text());
    var maxRange = parseInt($selectMax.find('option:nth-child(2)').text());

    // Default values for min and max publication year handles
    var currentMinValue = minRange;
    var currentMaxValue = maxRange;

    // JQUERY SLIDER UI OBJECT https://api.jqueryui.com/slider/#event-slide
    var $publicationRange = $('.publication-range');
    $publicationRange.slider({
      range: true,
      min: minRange,
      max: maxRange,
      step: 1,
      values: [currentMinValue, currentMaxValue],

      // Triggered on every mouse move during slide
      slide: function slide(event, ui) {
        currentMinValue = ui.values[0];
        currentMaxValue = ui.values[1];

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
    var handleMin = $('.ui-slider-handle').first();
    handleMin.attr({
      'data-testid': 'puplication-year-min'
    });

    // Create floating labe for Minimum Publication Year handle
    var yearLabelMin = '<span class=\'visually-hidden\'>Minimum year of publication:</span>\n      <span class=\'ui-slider__handle-label min-limit\'></span>';
    handleMin.append(yearLabelMin);

    // Maximum Publication Year handle
    var handleMax = $('.ui-slider-handle').last();
    handleMax.attr({
      'data-testid': 'puplication-year-max'
    });

    // Create floating label for Maximum Publication Year handle
    var yearLabelMax = '<span class=\'visually-hidden\'>Maximum year of publication:</span>\n      <span class=\'ui-slider__handle-label max-limit\'></span>';
    handleMax.append(yearLabelMax);

    // TRACK FOR RANGE SLIDER
    // Label for the range track
    var trackLabel = '<p class=\'ui-slider__track-label\'>\n        <span class=\'visually-hidden\'>Available publication years range from</span>\n        <span>' + minRange + '</span>\n        <span class=\'visually-hidden\'>to</span>\n        <span>' + maxRange + '</span>\n      </p>';
    $('.publication-range').append(trackLabel);

    // MAKING SLIDER HANDLES STAY AT THE POSITION OF PREVIOUSLY SUBMITTED QUERY WHEN PANEL IS REOPENED
    // Sync multirange slider with filter data from dropdowns.
    var updateSlider = function updateSlider() {
      // Find what dropdown values are selected
      var $selectMinOption = parseInt($selectMin.find(':selected').text());
      var $selectMaxOption = parseInt($selectMax.find(':selected').text());

      // Sanitize.
      if (isNaN($selectMinOption)) {
        $selectMinOption = minRange;
      };
      if (isNaN($selectMaxOption)) {
        $selectMaxOption = maxRange;
      };

      // Provide selected values to the multirange slider
      $publicationRange.slider('values', 0, $selectMinOption);
      $publicationRange.slider('values', 1, $selectMaxOption);

      // Update floating labels for multirange slider handles
      $('.min-limit').text($selectMinOption);
      $('.max-limit').text($selectMaxOption);
    };

    // Check if Advanced Filter Panel is open.
    $('.search-filter__advanced').one().click(function () {
      if ($dropdownPanel.hasClass('js-search_filter__dropdown-open')) {
        updateSlider();
      }
    });
  });
})(jQuery);
//# sourceMappingURL=ucsf_multirange-slider.js.map
