/**
 * Animation of elements based on location in the viewport.
 */
(function () {
  // element to be animated
  const elementFade = document.querySelectorAll('.element-fade');

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.intersectionRatio > 0) {
          // adds the class responsible for animation on the first viewport appearance
          entry.target.classList.add('element-fade--in-viewport');
          observer.unobserve(entry.target);
        }
      })
    })

    elementFade.forEach(element => {
      observer.observe(element);
    });
  }

})();
