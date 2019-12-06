'use strict';
// implements rotating placeholder text inside homepage Search input
(function() {
  // amount of time before next transition
  const delay = 2000;
  const selectors = ['#search', '[data-placeholder]'];

  selectors.forEach((item) => {
    const input = document.querySelector(item)
    if (input != null) {
      // Gets the first placeholder word ('search') to a variable
      const stationary = input.placeholder.split(' ').shift();

      // removes first word from placeholder text
      const trimmedString = input.placeholder.replace(stationary, '').trim();

      // Removes commas and white spaces before making an array of placeholder text
      const placeholderArr = trimmedString.replace(/,\s*/g, ',').split(',');

      let count = 0;
      setInterval(() => {
        input.setAttribute('placeholder', `${stationary} ${placeholderArr[count]}`);

        if (count >= placeholderArr.length) {
          count = 0;
        }
      }, delay);
    }

  })

})();
