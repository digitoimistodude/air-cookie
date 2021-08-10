/*

REQUIRED STUFF
==============
*/

var changed     = require('gulp-changed');
var gulp        = require('gulp');
var sass        = require('gulp-sass');
var sourcemaps  = require('gulp-sourcemaps');
var notify      = require('gulp-notify');
var prefix      = require('gulp-autoprefixer');
var cleancss    = require('gulp-clean-css');
var uglify      = require('gulp-uglify');
var cache       = require('gulp-cache');
var concat      = require('gulp-concat');
var util        = require('gulp-util');
var header      = require('gulp-header');
var pixrem      = require('gulp-pixrem');
var uncss       = require('gulp-uncss');
var rename      = require('gulp-rename');
var exec        = require('child_process').exec;

/*

FILE PATHS
==========
*/

var customjs = 'assets/src/js/scripts.js';
var jsSrc = 'assets/src/js/**/*.js';
var devJsDest = 'assets/dev/js';
var prodJsDest = 'assets/prod/js';

/*

ERROR HANDLING
==============
*/

var handleError = function(task) {
  return function(err) {

      notify.onError({
        message: task + ' failed, check the logs..'
      })(err);

    util.log(util.colors.bgRed(task + ' error:'), util.colors.red(err));
  };
};

var currentDate   = util.date(new Date(), 'dd-mm-yyyy HH:ss');
var pkg       = require('./package.json');
var banner      = '/*! <%= pkg.name %> <%= currentDate %> - <%= pkg.author %> */\n';

gulp.task('js', function() {

  // Dev
  gulp.src(
    [
      'assets/src/js/cookieconsent.js',
      'assets/src/js/*.js'
    ])
    .pipe(concat('air-cookie.js'))
    .pipe(header(banner, {pkg: pkg, currentDate: currentDate}))
    .pipe(gulp.dest(devJsDest));

  // Prod
  gulp.src(
    [
      'assets/src/js/cookieconsent.js',
      'assets/src/js/*.js'
    ])
    .pipe(concat('air-cookie.js'))
    .pipe(uglify({
        compress: true,
        mangle: true
      })
      .on('error', function(err) {
        util.log(util.colors.red('[Error]'), err.toString());
        this.emit('end');
      })
    )
    .pipe(header(banner, {pkg: pkg, currentDate: currentDate}))
    .pipe(gulp.dest(prodJsDest));
});
