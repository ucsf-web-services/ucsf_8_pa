'use strict';
// implements rotating placeholder text inside homepage Search input
(function() {
  // amount of time before next transition
  const delay = 1500;
  const input = document.querySelector('[data-placeholder]');

  // Removes commas and white spaces before making an array of placeholder text
  const placeholderArr = input.placeholder.replace(/[, ]+/g, " ").trim().split(' ');

  // sets the first word ('search') of an array to a variable
  const stationary = placeholderArr[0];
  // removes first word from array
  placeholderArr.shift();

  let count = 0;
  setInterval(() => {
    input.setAttribute('placeholder', `${stationary} ${placeholderArr[count]}`);
    count++;
    if (count >= placeholderArr.length) {
      count = 0;
    }
  }, delay);
})();
