(($ => {

  // Wait for the document to be ready.
  $(() => {
    // The progress bar which will be updated on scroll.
    const progressBar = document.querySelector('.progress-bar');

    // The article used to detect scroll position.
    const article = document.querySelector('.main-content');

    // Get the Y position for where the article sits on the page.
    const rect = article.getBoundingClientRect();
    const articleTop = rect.top + document.documentElement.scrollTop;

    // Get half the screen height.
    const screenHalfHeight = window.innerHeight / 2;

    window.addEventListener('scroll', () => {
      // Get the current scroll position within the article.
      const scrollY = window.scrollY - articleTop;

      // Get the length of the article minus half of the screen height so that
      // an article will be completed reading once the bottom edge of it is half
      // way up the screen.
      const articleHeight = article.clientHeight - screenHalfHeight;

      // Get the average progress ratio of article scrolled through.
      let progress = scrollY / articleHeight;

      // Ensure that the progress bar never goes lower than zero or higher than 1
      if (progress > 1) {
        progress = 1;
      } else if (progress < 0) {
        progress = 0;
      }

      // Set the progress bar percent.
      progressBar.setAttribute('value', progress);
    })


  });
}))(jQuery);
