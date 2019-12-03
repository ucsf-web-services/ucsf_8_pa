'use strict';
// implements rotating placeholder text inside homepage Search input

(function () {
  // amount of time before next transition
  var delay = 1500;
  var input = document.querySelector('[data-placeholder]');

  // Rets the first placeholder word ('search') to a variable
  var stationary = input.placeholder.split(' ').shift();

  // removes first word from placeholder text
  var trimmedString = input.placeholder.replace(stationary, '').trim();

  // Removes commas and white spaces before making an array of placeholder text
  var placeholderArr = trimmedString.replace(/,\s*/g, ',').split(',');

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
