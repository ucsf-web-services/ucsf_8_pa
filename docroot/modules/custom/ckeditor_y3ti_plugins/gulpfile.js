const gulp = require('gulp'),
	gulpif = require('gulp-if'),
	HtmlSplitter = require('polymer-build').HtmlSplitter,
	sourcesHtmlSplitter = new HtmlSplitter(),
	babelPresetES2015 = require('babel-preset-es2015'),
	babelPresetES2015NoModules = babelPresetES2015.buildPreset({}, {modules: false}),
	babel = require('gulp-babel')

gulp.task('polymerLibraryTranpilation', () => {
	return gulp.src([
                    './bower_components/polymer/**/*.html'
                ])
		.pipe(sourcesHtmlSplitter.split())
		.pipe( gulpif( /\.js$/, babel({ "presets": [ babelPresetES2015NoModules]}) ) )
		.pipe(sourcesHtmlSplitter.rejoin())
		.pipe(gulp.dest('./bower_components/polymer'))
})

gulp.task('webcomponentsTranspilation', () => {
	return gulp.src([
					'./nursing_components/**/*.html',
     				'./bower_components/**/*.html',
			'!' + './bower_components/webcomponentsjs/**/*.html',
			'!' + './bower_components/polymer/**/*.html',
                ])
		.pipe(sourcesHtmlSplitter.split())
		.pipe( gulpif( /\.js$/, babel({ "presets": ['babel-preset-es2015']}) ) )
		.pipe(sourcesHtmlSplitter.rejoin())
		.pipe(gulp.dest('./'))
})
