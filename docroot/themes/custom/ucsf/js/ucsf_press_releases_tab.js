(($ => {
  // Wait for the document to be ready.
  $(() => {
    const $findMorePr = $('.redirect-to-advanced');
    // Create a query pass it along to /news/filter and conduct search by term
    $findMorePr.one('click', (function (e) {
      e.preventDefault();
      const $searchTerm = $('.search-form__input').val();
      const theHref = $findMorePr.attr("href");
      // Pass search term into url in order to populate search input and conduct a search
      const newHref = theHref.replace('\/news\/filter?', `\/news\/filter?combine=${$searchTerm}&`);
      // Manually redirect to the news filter page
      window.location.href = newHref;
    }));
  });
}))(jQuery);
