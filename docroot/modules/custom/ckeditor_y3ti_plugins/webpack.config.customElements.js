const path = require('path');

module.exports = {
  entry: './js/custom-elements/importCustomElement.js',
  output: {
    filename: 'customElements.bundle.js',
    path: path.resolve(__dirname, './js/build'),
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
    ],
  },
};