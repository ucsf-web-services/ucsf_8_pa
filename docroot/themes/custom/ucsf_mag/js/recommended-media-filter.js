(($) => {
  // Wait for the document to be ready.
  $(() => {
    // Apply a class to filter option based on the current query value.
    const theHref = window.location.href;
    const $optionAll = $('[href="?"]');
    const options = ['414451', '414601', '414606'];
    if( theHref.indexOf('category') < 0 ) {
      $optionAll.addClass('active');
    }

    options.forEach(option => {
      if( theHref.indexOf(option) > 0 ) {
        $(`.${option}`).addClass('active');
      }
    });
  });

})(jQuery);
