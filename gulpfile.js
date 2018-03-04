'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var cssnano = require('gulp-cssnano');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('workflow', function () {
    gulp.src('./src/css/**/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass(
            {
                includePaths: ['node_modules/foundation-sites/scss/', 'node_modules/motion-ui/src']
            }
        ).on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['last 2 versions', 'ie >= 9', 'Android >= 2.3', 'ios >= 7'],
            cascade: false
        }))
        // .pipe(cssnano())
        .pipe(sourcemaps.write('./'))

        .pipe(gulp.dest('./src/css/'))
});

gulp.task('default', function () {
    gulp.watch('./src/css/**/*.scss', ['workflow']);
});