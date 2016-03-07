var gulp = require('gulp');
var del = require('del');
var typescript = require('gulp-typescript');
var sourcemaps = require('gulp-sourcemaps');
var tslint = require('gulp-tslint');
var less = require('gulp-less');
var minifyCss = require('gulp-minify-css');
var uglify = require('gulp-uglify');
var html2json = require('gulp-html-to-json');

// Directories

var templateSource = 'templates/app/templates.tpl';
var templateOut = 'templates/app/compiled/';
var tsSource = 'typescript/**/';
var lessSource = 'less/**/';

var tsOut = 'public/app/';
var libOut = 'public/lib/';
var cssOut = 'public/css/';
var fontsOut = 'public/fonts/';

// Clean the compiled typescript and templates
gulp.task('clean:app', function () {
  return del([tsOut + '**/*']);
});

// Clean the copied javascript libraries
gulp.task('clean:lib', function () {
  return del([libOut + '**/*']);
});

// Clean the compiled and copied stylesheets
gulp.task('clean:css', function () {
  return del([cssOut + '**/*']);
});

// Clean the fonts directory
gulp.task('clean:fonts', function () {
  return del([fontsOut + '**/*']);
});

// Clean the compiled and copied javascript and style resources
gulp.task('clean', ['clean:app', 'clean:css', 'clean:lib']);

// TypeScript lint
gulp.task('tslint', function() {
  return gulp
    .src(tsSource + '*.ts')
    .pipe(tslint())
    .pipe(tslint.report('verbose'));
});

// Compile LESS to CSS
gulp.task('less', ['clean:css'], function() {
  return gulp
    .src(lessSource + '*.less')
    .pipe(less())
    .pipe(minifyCss())
    .pipe(gulp.dest(cssOut));
});

// Copy non-less CSS files
gulp.task('copy:css', ['clean:css'], function() {
  return gulp
    .src([lessSource + '*', '!' + lessSource + '*.less'],
         { base : './less' })
    .pipe(minifyCss())
    .pipe(gulp.dest(cssOut));
});

// TypeScript compile
gulp.task('compile', ['clean:app'], function() {
  return gulp
    .src(tsSource + '*.ts')
    .pipe(sourcemaps.init())
    .pipe(typescript({
      typescript: require('typescript'), // our desired version
      target: 'ES5',
      module: 'system',
      sourceMap: true,
      moduleResolution: 'node',
      experimentalDecorators: true,
      emitDecoratorMetadata: true,
      outFile: 'soapy.js',
      removeComments: false,
      noImplicitAny: false,
    }))
    .pipe(uglify({
      mangle: false,
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(tsOut));
});

// Copy app templates
gulp.task('copy:templates', ['clean:app'], function() {
  return gulp
    .src(templateSource)
    .pipe(html2json())
    .pipe(gulp.dest(templateOut));
});

// Copy bootstrap css
gulp.task('copy:css:bootstrap', ['clean:css'], function() {
  return gulp
    .src([
    'node_modules/bootstrap/dist/css/bootstrap.min.css',
    'node_modules/bootstrap/dist/css/bootstrap.min.css.map',
    'node_modules/bootstrap/dist/css/bootstrap-theme.min.css',
    'node_modules/bootstrap/dist/css/bootstrap-theme.min.css.map',
  ])
  .pipe(gulp.dest(cssOut));
});

// Copy fonts
gulp.task('copy:fonts', ['clean:fonts'], function() {
  return gulp
    .src([
    'node_modules/bootstrap/dist/fonts/*.*',
  ])
  .pipe(gulp.dest(fontsOut));
});

// Copy library dependencies
gulp.task('copy:libs', ['clean:lib'], function() {
  return gulp
    .src([
    'node_modules/es6-shim/es6-shim.js',
    'node_modules/es6-shim/es6-shim.min.js',
    'node_modules/es6-shim/es6-shim.map',

    'node_modules/angular2/bundles/angular2-polyfills.js',
    'node_modules/angular2/bundles/angular2-polyfills.min.js',

    'node_modules/angular2/bundles/angular2.js',
    'node_modules/angular2/bundles/angular2.min.js',
    'node_modules/angular2/bundles/angular2.dev.js',

    'node_modules/angular2/bundles/http.js',
    'node_modules/angular2/bundles/http.min.js',
    'node_modules/angular2/bundles/http.dev.js',

    'node_modules/systemjs/dist/system.js',
    'node_modules/systemjs/dist/system.js.map',
    'node_modules/systemjs/dist/system.src.js',

    'node_modules/systemjs/dist/system-polyfills.js',
    'node_modules/systemjs/dist/system-polyfills.js.map',
    'node_modules/systemjs/dist/system-polyfills.src.js',

    'node_modules/angular2/bundles/router.js',
    'node_modules/angular2/bundles/router.min.js',
    'node_modules/angular2/bundles/router.dev.js',

    'node_modules/rxjs/bundles/Rx.js',
    'node_modules/rxjs/bundles/Rx.min.js',
    'node_modules/rxjs/bundles/Rx.min.js.map',

    'node_modules/bootstrap/dist/js/bootstrap.js',
    'node_modules/bootstrap/dist/js/bootstrap.min.js',
  ])
  .pipe(gulp.dest(libOut));
});

gulp.task('build', [
    'tslint',
    'less',
    'copy:css',
    'copy:css:bootstrap',
    'compile',
    'copy:templates',
    'copy:libs',
    'copy:fonts',
  ]);

gulp.task('css', ['less', 'copy:css', 'copy:css:bootstrap']);

gulp.task('default', ['build']);

