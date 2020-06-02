/**
 * Load Plugins.
 *
 * Load gulp plugins and assing them semantic names.
 */
var gulp = require('gulp');

// CSS related plugins.
var sass = require('gulp-sass'); // Gulp pluign for Sass compilation
var autoprefixer = require('gulp-autoprefixer'); // Autoprefixing magic
var minifycss = require('gulp-uglifycss'); // Minifies CSS files

// JS related plugins.
var concat = require('gulp-concat'); // Concatenates JS files
var plumber = require('gulp-plumber');
var jshint = require('gulp-jshint');
var uglify = require('gulp-uglify'); // Minifies CSS files

// Utility related plugins.
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var notify = require('gulp-notify');

/**
 * Configuration.
 *
 * Project Configuration for gulp tasks.
 *
 * Edit the variables as per your project requirements.
 */
var styleSRC = './dist/public/sass/**/*.scss';
var styleDestination = './assets/public/css/';

var adminStyleSRC = './dist/admin/sass/**/*.scss';
var adminStyleDestination = './assets/admin/css/';

var jsSrc = './dist/public/js/public.js';
var jsSrcBrowser = './dist/public/js/join-via-browser.js';
var shortcodeJSSrc = './dist/public/js/shortcode.js';
var jsDestination = './assets/public/js/';
var jsSrcVendor = './dist/public/vendor/**/*.js';

var adminJsSrc = './dist/admin/js/**/*.js';
var adminJsDestination = './assets/admin/js/';

// Copy third party libraries from /node_modules into /vendor
gulp.task('vendor', function () {

    //Select2
    gulp.src([
        './node_modules/select2/dist/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/select2'));

    //dataTable
    gulp.src([
        './node_modules/datatables.net-dt/css/**/*',
        './node_modules/datatables.net/js/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/datatable'));

    //DataTimePicker
    gulp.src([
        './node_modules/jquery-datetimepicker/build/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/dtimepicker'));

    //MomentJS
    gulp.src([
        './node_modules/moment/min/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/moment'));

    //MomentJS Timezone
    gulp.src([
        './node_modules/moment-timezone/builds/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/moment-timezone'));

    //React Production Copy
    gulp.src([
        './node_modules/react/umd/react.production.min.js',
        './node_modules/react-dom/umd/react-dom.production.min.js',
        './node_modules/redux/dist/redux.min.js',
        './node_modules/redux-thunk/dist/redux-thunk.min.js',
        './node_modules/lodash/lodash.min.js',
        './node_modules/@zoomus/websdk/dist/css/bootstrap.css',
        './node_modules/@zoomus/websdk/dist/css/react-select.css',
    ])
        .pipe(gulp.dest('./assets/vendor/zoom'));
});

// Public Styles
gulp.task('styles', function () {
    return gulp.src(styleSRC)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        // .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compact',
            precision: 10
        }))
        // .pipe(sourcemaps.write({includeContent: false}))
        // .pipe(sourcemaps.init({loadMaps: true}))
        .pipe(autoprefixer(
            'last 2 version',
            '> 1%',
            'safari 5',
            'ie 8',
            'ie 9',
            'opera 12.1',
            'ios 6',
            'android 4'))
        // .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(styleDestination))
        .pipe(minifycss({
            "maxLineLen": 80,
            "uglyComments": true
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(styleDestination))
    // .pipe( notify( { message: 'TASK: "styles" Completed! 💯', onLast: true } ) );
});

//Admin Styles
gulp.task('stylesAdmin', function () {
    return gulp.src(adminStyleSRC)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        // .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compact',
            precision: 10
        }))
        /*.pipe(sourcemaps.write({includeContent: false}))
        .pipe(sourcemaps.init({loadMaps: true}))*/
        .pipe(autoprefixer(
            'last 2 version',
            '> 1%',
            'safari 5',
            'ie 8',
            'ie 9',
            'opera 12.1',
            'ios 6',
            'android 4'))
        // .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(adminStyleDestination))
        .pipe(minifycss({
            "maxLineLen": 80,
            "uglyComments": true
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(adminStyleDestination))
    // .pipe( notify( { message: 'TASK: "styles" Completed! 💯', onLast: true } ) );
});

// Public JS
gulp.task('publicJS', function () {
    gulp.src(jsSrc)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest(jsDestination))
        .pipe(rename({
            basename: 'scripts',
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest(jsDestination))
    // .pipe( notify( { message: 'TASK: "customJs" Completed!', onLast: true } ) );
});

// Public JS
gulp.task('browserJS', function () {
    gulp.src(jsSrcBrowser)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(concat('join-browser.js'))
        .pipe(gulp.dest(jsDestination))
        .pipe(rename({
            basename: 'join-browser',
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest(jsDestination))
    // .pipe( notify( { message: 'TASK: "customJs" Completed!', onLast: true } ) );
});



gulp.task('shortcodeJS', function () {
    gulp.src(shortcodeJSSrc)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(concat('shortcode.js'))
        .pipe(gulp.dest(jsDestination))
        .pipe(rename({
            basename: 'shortcode',
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest(jsDestination))
    // .pipe( notify( { message: 'TASK: "customJs" Completed!', onLast: true } ) );
});

// Public JS
gulp.task('vendorJS', function () {
    gulp.src(jsSrcVendor)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(concat('zoom-meeting.js'))
        .pipe(gulp.dest(jsDestination))
        .pipe(rename({
            basename: 'zoom-meeting',
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest(jsDestination))
    // .pipe( notify( { message: 'TASK: "customJs" Completed!', onLast: true } ) );
});

// Admin JS
gulp.task('adminJS', function () {
    gulp.src(adminJsSrc)
        .pipe(plumber({errorHandler: notify.onError("Error: <%= error.message %>")}))
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'))
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest(adminJsDestination))
        .pipe(rename({
            basename: 'scripts',
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest(adminJsDestination))
    // .pipe( notify( { message: 'TASK: "customJs" Completed!', onLast: true } ) );
});

// Default task
gulp.task('default', ['styles', 'stylesAdmin', 'publicJS', 'browserJS', 'shortcodeJS', 'vendorJS', 'adminJS', 'vendor'], function () {
    gulp.watch('./dist/public/sass/*.scss', ['styles']);
    gulp.watch('./dist/admin/sass/*.scss', ['stylesAdmin']);
    gulp.watch('./dist/public/js/*.js', ['publicJS','shortcodeJS']);
    gulp.watch('./dist/public/vendor/*.js', ['vendorJS']);
    gulp.watch('./dist/admin/js/*.js', ['adminJS']);
});
