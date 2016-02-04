var gulp = require('gulp');
var del = require('del');
var typescript = require('gulp-typescript');
var tscConfig = require('./tsconfig.json');
var sourcemaps = require('gulp-sourcemaps');
var tslint = require('gulp-tslint');

var tsSource = 'typescript/**/';
var tsOut = 'public/app/';
var libOut = 'public/lib/';

// Clean the contents of the distribution directory
gulp.task('clean', function () {
  return del([tsOut + '**/*',
              libOut + '**/*']);
});

// TypeScript lint
gulp.task('tslint', function() {
  return gulp.src(tsSource + '*.ts')
    .pipe(tslint())
    .pipe(tslint.report('verbose'));
});

// TypeScript compile
gulp.task('compile', ['clean'], function() {
  return gulp
    .src(tsSource + '*.ts')
    .pipe(sourcemaps.init())
    .pipe(typescript(tscConfig.compilerOptions))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(tsOut));
});

// Copy static assets (non TypeScript compiled files)
gulp.task('copy:assets', ['clean'], function() {
  return gulp.src([tsSource + '*', '!' + tsSource + '*.ts'],
                  { base : './typescript' })
    .pipe(gulp.dest(tsOut));
});

// Copy dependencies
gulp.task('copy:libs', ['clean'], function() {
  return gulp.src([
    'node_modules/es6-shim/es6-shim.js',
    'node_modules/es6-shim/es6-shim.min.js',
    'node_modules/es6-shim/es6-shim.map',

    'node_modules/angular2/bundles/angular2-polyfills.js',
    'node_modules/angular2/bundles/angular2-polyfills.min.js',

    'node_modules/angular2/bundles/angular2.js',
    'node_modules/angular2/bundles/angular2.min.js',
    'node_modules/angular2/bundles/angular2.dev.js',

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
  ])
  .pipe(gulp.dest(libOut));
});

gulp.task('build', ['tslint', 'compile', 'copy:assets', 'copy:libs']);
gulp.task('default', ['build']);

