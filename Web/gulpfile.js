var gulp = require('gulp');
var del = require('del');
var typescript = require('gulp-typescript');
var sourcemaps = require('gulp-sourcemaps');
var tslint = require('gulp-tslint');
var less = require('gulp-less');
var minifyCss = require('gulp-minify-css');
var uglify = require('gulp-uglify');
var removeHtmlComments = require('gulp-remove-html-comments');
var html2json = require('gulp-html-to-json');

// Directories

var templateSource = 'templates/app/';
var templateList = 'templates/app/templates.tpl';
var compiledTemplateList = 'templates/app/compiled/templates.tpl';
var templateOut = 'templates/app/compiled/';
var tsSource = 'typescript/**/';
var lessSource = 'less/**/';

var tsOut = 'public/app/';
var libOut = 'public/lib/';
var cssOut = 'public/css/';
var fontsOut = 'public/fonts/';

/* ***************
   Typescript Generation
   *************** */

// Clean the compiled typescript and templates
gulp.task('clean:app', function () {
  return del([tsOut + '**/*']);
});

// TypeScript lint
gulp.task('ts:lint', function() {
  return gulp
    .src(tsSource + '*.ts')
    .pipe(tslint())
    .pipe(tslint.report('verbose'));
});

// TypeScript compile
gulp.task('ts:compile', function() {
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

// App javascript generation
gulp.task('ts', [
  'clean:app',
  'ts:lint',
  'ts:compile',
]);

/* ***************
   CSS Generation
   *************** */

// Clean the compiled and copied stylesheets
gulp.task('clean:css', function () {
  return del([cssOut + '**/*']);
});

// Compile LESS to CSS
gulp.task('less', function() {
  return gulp
    .src(lessSource + '*.less')
    .pipe(less())
    .pipe(minifyCss())
    .pipe(gulp.dest(cssOut));
});

// Copy non-less CSS files
gulp.task('copy:css', function() {
  return gulp
    .src([lessSource + '*', '!' + lessSource + '*.less'],
         { base : './less' })
    .pipe(minifyCss())
    .pipe(gulp.dest(cssOut));
});

// Copy bootstrap css
// TODO: Remove this when we switch to v2 since we compile bootstrap directly.
gulp.task('copy:css:bootstrap', function() {
  return gulp
    .src([
    'node_modules/bootstrap/dist/css/bootstrap.min.css',
    'node_modules/bootstrap/dist/css/bootstrap.min.css.map',
    'node_modules/bootstrap/dist/css/bootstrap-theme.min.css',
    'node_modules/bootstrap/dist/css/bootstrap-theme.min.css.map',
  ])
  .pipe(gulp.dest(cssOut));
});

// Populate css files
gulp.task('css', [
  'clean:css',
  'less',
  'copy:css',
  'copy:css:bootstrap',
]);

/* ***************
   Template Generation
   *************** */

// Clean compiled template file
gulp.task('clean:templates', function() {
  return del([templateOut + '**/*']);
});

// Copy template list file to compiled directory
gulp.task('copy:template:list', function() {
  return gulp
    .src(templateList)
    .pipe(gulp.dest(templateOut));
});

// Remove template comments
gulp.task('decomment:templates', function() {
  return gulp
    .src(templateSource + '*.html',
         { base: templateSource})
    .pipe(removeHtmlComments())
    .pipe(gulp.dest(templateOut));
});

// Compile app templates into a json file
gulp.task('compile:templates',
          [ 'copy:template:list', 'decomment:templates'],
          function() {
  return gulp
    .src(compiledTemplateList)
    .pipe(html2json())
    .pipe(gulp.dest(templateOut));
});

// Generate templates
gulp.task('templates', [
  'clean:templates',
  'compile:templates',
]);

/* ***************
   Font Generation
   *************** */

// Clean the fonts directory
gulp.task('clean:fonts', function () {
  return del([fontsOut + '**/*']);
});

// Copy fonts
gulp.task('copy:fonts', function() {
  return gulp
    .src([
    'node_modules/bootstrap/dist/fonts/*.*',
  ])
  .pipe(gulp.dest(fontsOut));
});

// Generate fonts
gulp.task('fonts', [
  'clean:fonts',
  'copy:fonts',
]);

/* ***************
   Library Generation
   *************** */

// Clean the copied javascript libraries
gulp.task('clean:lib', function () {
  return del([libOut + '**/*']);
});

// Copy library dependencies
gulp.task('copy:libs', function() {
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

    'node_modules/smartcrop/smartcrop.js',
  ])
  .pipe(gulp.dest(libOut));
});

// Generate libraries
gulp.task('libs', [
  'clean:lib',
  'copy:libs',
]);

/* ***************
   External commands
   *************** */

// Build everything
gulp.task('build', [
    'templates',
    'ts',
    'css',
    'fonts',
    'libs',
  ]);

// Clean everything
gulp.task('clean', [
  'clean:app',
  'clean:css',
  'clean:lib',
  'clean:templates',
]);

// Default to bulding everything
gulp.task('default', ['build']);

