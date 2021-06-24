'use strict';

const gulp = require('gulp');
const userConfig = require('./gulp-config');

require('cthreem-core')(gulp, userConfig);


// Available gulp tasks:
// - clean
// - compile
// - compress
// - validate
// - watch
// - default: uses each of the above tasks
