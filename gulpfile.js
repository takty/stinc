/**
 * Gulpfile
 *
 * @author Space-Time Inc.
 * @version 2022-03-24
 */

/* eslint-disable no-undef */
'use strict';

const gulp = require('gulp');
const $    = require('gulp-load-plugins')({ pattern: ['gulp-plumber', 'gulp-changed'] });

exports.default = () => {
	return gulp.src(['src/**/*'])
		.pipe($.plumber())
		.pipe($.changed('./dist'))
		.pipe(gulp.dest('./dist'));
};
