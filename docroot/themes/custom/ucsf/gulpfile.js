'use strict';

const gulp = require('gulp');
const _ = require('lodash');
let userConfig = require('./gulp-config');

// Load in custom config.
try {
  const customConfig = require('./gulp-config.local.js');
  userConfig = _.merge(userConfig, customConfig);
}
catch (e) {
  console.log('Add a gulp-config.local.js file for any custom local configuration.');
}

require('cthreem-core')(gulp, userConfig);


// Available gulp tasks:
// - clean
// - compile
// - compress
// - validate
// - watch
// - default: uses each of the above tasks
