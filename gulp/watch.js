var gulp = require('gulp');
var build = require('./build');
var yargs = require('yargs');

// get demo from parameters
var demo = Object.keys(yargs.argv).join(' ').match(/(demo\d+)/ig) || 'demo1';
if (typeof demo === 'object') {
  demo = demo[0];
}

// localhost site
var connect = require('gulp-connect');
gulp.task('localhost', function(cb) {
  connect.server({
    root: '../' + demo + '/dist',
    livereload: true,
  });
  cb();
});
gulp.task('reload', function(cb) {
  connect.reload();
  cb();
});

gulp.task('watch', function() {
  return gulp.watch([build.config.path.src + '/sass/*.scss', build.config.path.src + '/sass/components/*.scss', build.config.path.src + '/sass/components/**/*.scss', build.config.path.src + '/js/*.js', build.config.path.src + '/js/components/*.js', build.config.path.src + '/js/components/**/*.js'], gulp.series('build-bundle'));
});

gulp.task('watch:scss', function() {
  return gulp.watch([build.config.path.src + '/sass/*.scss', build.config.path.src + '/sass/components/*.scss', build.config.path.src + '/sass/components/**/*.scss'], gulp.parallel('build-bundle'));
});

gulp.task('watch:js', function() {
  return gulp.watch([build.config.path.src + '/js/*.js', build.config.path.src + '/js/components/*.js', build.config.path.src + '/js/components/**/*.js'], gulp.parallel('build-bundle'));
});
