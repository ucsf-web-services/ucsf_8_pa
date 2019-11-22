'use strict';
// implements rotating placeholder text inside homepage Search input

(function () {
  // amount of time before next transition
  var delay = 1500;
  var input = document.querySelector('[data-placeholder]');

  // Removes commas and white spaces before making an array of placeholder text
  var placeholderArr = input.placeholder.replace(/[, ]+/g, " ").trim().split(' ');

  // sets the first word ('search') of an array to a variable
  var stationary = placeholderArr[0];
  // removes first word from array
  placeholderArr.shift();

  var count = 0;
  setInterval(function () {
    input.setAttribute('placeholder', stationary + ' ' + placeholderArr[count]);
    count++;
    if (count >= placeholderArr.length) {
      count = 0;
    }
  }, delay);
})();
//# sourceMappingURL=ucsf_search_placeholder_rotation.js.map
