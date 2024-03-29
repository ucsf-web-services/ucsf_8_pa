// /**
//  * Header Search.
//  */
 (($) => {
  // Wait for the document to be ready.
  $(() => {
    const $searchInput = $('#header-search');
    const $button = $('.mag-mobile-search-reveal');
    const $logo = $(".header--logo");

    $button.on('click', function (e) {
      e.preventDefault();

      if ($(this).hasClass("active")) {
        $searchInput.focus();

        // if magazine menu is open minimize logo when search form is open
        if ($(".mag-nav--active").length > 0) {
          $logo.addClass("header--logo-minimized");
        }

        // change checked option default
        const $radio = $("#allucsf");
        if ($radio.is(":checked")) {
          $radio.attr("checked", false);
          $("#newscenter").attr("checked", true);
        }
      } else {
        // if magazine menu is open close the form, return logo to regular size
        $logo.removeClass("header--logo-minimized");
      }
    });

  });

})(jQuery);
