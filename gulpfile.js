/**
 * Load Plugins.
 *
 * Load gulp plugins and assing them semantic names.
 */
const gulp = require('gulp');
const babel = require('gulp-babel');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const cleanCSS = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const sass = require('gulp-sass');
const autoprefixer = require('autoprefixer');
const postcss = require('gulp-postcss');
const del = require('del');

const paths = {
    styles: {
        src: 'src/public/sass/**/*.scss',
        dest: 'assets/public/css/'
    },
    admin_styles: {
        src: 'src/admin/sass/**/*.scss',
        dest: 'assets/admin/css/'
    },
    admin_scripts: {
        src: 'src/admin/js/**/*.js',
        dest: 'assets/admin/js/'
    },
    publicScripts: {
        mainScript: 'src/public/js/public.js',
        browserJoinScript: 'src/public/js/join-via-browser.js',
        shortcodeScript: 'src/public/js/shortcode.js',
        vendorScripts: 'src/public/vendor/**/*.js',
        dest: 'assets/public/js/'
    }
};

/* Not all tasks need to use streams, a gulpfile is just another node program
 * and you can use all packages available on npm, but it must return either a
 * Promise, a Stream or take a callback and call it
 */
function clean() {
    // You can use multiple globbing patterns as you would with `gulp.src`,
    // for example if you are using del 2.0 or above, return its promise
    return del(['assets/admin/js', 'assets/admin/css', 'assets/public/js', 'assets/public/css']);
}

function compileVendorScripts() {
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

    //dataTable Responsive
    gulp.src([
        './node_modules/datatables.net-responsive-dt/js/**/*',
        './node_modules/datatables.net-responsive-dt/css/**/*',
        './node_modules/datatables.net-responsive/js/**/*'
    ])
        .pipe(gulp.dest('./assets/vendor/datatable-responsive'));

    //DataTimePicker
    gulp.src([
        './node_modules/jquery-datetimepicker/build/**/*',
    ])
        .pipe(gulp.dest('./assets/vendor/dtimepicker'));

    //Crypto JS
    gulp.src([
        './node_modules/crypto-js/crypto-js.js',
    ])
        .pipe(gulp.dest('./assets/vendor/crypto-js'));

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
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/react/umd/react.production.min.js',
        './node_modules/react-dom/umd/react-dom.production.min.js',
        './node_modules/redux/dist/redux.min.js',
        './node_modules/redux-thunk/dist/redux-thunk.min.js',
        './node_modules/lodash/lodash.min.js',
        './node_modules/@zoomus/websdk/dist/css/bootstrap.css',
        './node_modules/@zoomus/websdk/dist/css/react-select.css',
    ])
        .pipe(gulp.dest('./assets/vendor/zoom'));
}

/*
 * Define our tasks using plain functions
 */
function styles() {
    return gulp.src(paths.styles.src)
    // .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compact',
            precision: 10
        }))
        .pipe(postcss([autoprefixer()]))
        .pipe(gulp.dest(paths.styles.dest))
        .pipe(cleanCSS({
            level: {
                1: {
                    cleanupCharsets: true,
                    removeEmpty: true,
                    removeWhitespace: true,
                    specialComments: 0
                }
            }
        }))
        // pass in options to the stream
        .pipe(rename({
            basename: 'style',
            suffix: '.min'
        }))
        // .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(paths.styles.dest));
}

/*
 * Define our tasks using plain functions
 */
function admin_styles() {
    return gulp.src(paths.admin_styles.src)
    // .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'compact',
            precision: 10
        }))
        .pipe(postcss([autoprefixer]))
        .pipe(gulp.dest(paths.admin_styles.dest))
        .pipe(cleanCSS({
            level: {
                1: {
                    cleanupCharsets: true,
                    removeEmpty: true,
                    removeWhitespace: true,
                    specialComments: 0
                }
            }
        }))
        // pass in options to the stream
        .pipe(rename({
            basename: 'style',
            suffix: '.min'
        }))
        // .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(paths.admin_styles.dest));
}

/**
 * Admin SCRIPT function
 *
 * @returns
 */
function admin_scripts() {
    return gulp.src(paths.admin_scripts.src, {sourcemaps: false})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(gulp.dest(paths.admin_scripts.dest))
        .pipe(uglify())
        .pipe(concat('script.min.js'))
        .pipe(gulp.dest(paths.admin_scripts.dest));
}

//MAIN SCRIPT FILE
function mainScript() {
    return gulp.src(paths.publicScripts.mainScript, {sourcemaps: false})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(gulp.dest(paths.publicScripts.dest))
        .pipe(uglify())
        .pipe(concat('public.min.js'))
        .pipe(gulp.dest(paths.publicScripts.dest));
}

//JOIN VIA BROWSER SCRIPT FILE
function browserJoinScript() {
    return gulp.src(paths.publicScripts.browserJoinScript, {sourcemaps: false})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(gulp.dest(paths.publicScripts.dest))
        .pipe(uglify())
        .pipe(concat('join-via-browser.min.js'))
        .pipe(gulp.dest(paths.publicScripts.dest));
}

//SHORTCODE SCRIPT FILE
function shortcodeScript() {
    return gulp.src(paths.publicScripts.shortcodeScript, {sourcemaps: false})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(gulp.dest(paths.publicScripts.dest))
        .pipe(uglify())
        .pipe(concat('shortcode.min.js'))
        .pipe(gulp.dest(paths.publicScripts.dest));
}

//VENDOR SCRIPT FILE
function vendorScripts() {
    return gulp.src(paths.publicScripts.vendorScripts, {sourcemaps: false})
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(gulp.dest(paths.publicScripts.dest))
        .pipe(uglify())
        .pipe(concat('zoom-meeting.min.js'))
        .pipe(gulp.dest(paths.publicScripts.dest));
}

/**
 * Watch files include here below
 */
function watchFiles() {
    gulp.watch(paths.admin_scripts.src, admin_scripts);
    gulp.watch(paths.admin_styles.src, admin_styles);
    gulp.watch(paths.styles.src, styles);
    gulp.watch(paths.publicScripts.mainScript, mainScript);
    gulp.watch(paths.publicScripts.browserJoinScript, browserJoinScript);
    gulp.watch(paths.publicScripts.shortcodeScript, shortcodeScript);
    gulp.watch(paths.publicScripts.vendorScripts, vendorScripts);
}

/*
 * Specify if tasks run in series or parallel using `gulp.series` and `gulp.parallel`
 */
const build = gulp.series(clean, gulp.parallel(admin_styles, admin_scripts, styles, mainScript, browserJoinScript, shortcodeScript, vendorScripts));
// const build = gulp.series(modules, gulp.parallel(styles, scripts));
const watch = gulp.series(build, gulp.parallel(watchFiles));

/*
 * You can use CommonJS `exports` module notation to declare tasks
 */
exports.clean = clean;
exports.admin_styles = admin_styles;
exports.admin_scripts = admin_scripts;
exports.vendors = compileVendorScripts;
exports.watch = watch;
exports.build = build;
/*
 * Define default task that can be called by just running `gulp` from cli
 */
exports.default = build;
