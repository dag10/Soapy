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
  return gulp.src(['app/**/*', '!app/**/*.ts'], { base : './' })
    .pipe(gulp.dest('dist'))
});

// Copy dependencies
gulp.task('copy:libs', ['clean'], function() {
  return gulp.src([
    'node_modules/angular2/bundles/angular2-polyfills.js',
    'node_modules/systemjs/dist/system.src.js',
    'node_modules/rxjs/bundles/Rx.js',
    'node_modules/angular2/bundles/angular2.dev.js',
    'node_modules/angular2/bundles/router.dev.js'
  ])
  .pipe(gulp.dest(libOut));
});

gulp.task('build', ['tslint', 'compile', 'copy:libs']);
gulp.task('default', ['build']);

