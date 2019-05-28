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
          console.log(filter_item);
          $('.search-filter__indicator-list label').removeClass('visually-hidden')
        }
      }

      removeFilter();

    }
  };

  $(function() {
      var waypoint = $('.view-content').waypoint({
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
