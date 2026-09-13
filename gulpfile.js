const del = require('del');
const gulp = require('gulp');
const sass = require('gulp-sass');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const cssmin = require('gulp-cssmin');
const copyfiles = require('copyfiles');
const purify = require('gulp-purifycss');
const imagemin = require('gulp-imagemin');
const sourcemaps = require('gulp-sourcemaps');

// Get libs
const libs = require('./libs.js');

// Concatenate & minify libs css
function libsCss() {
    return gulp
        .src(libs.css)
        .pipe(concat('styles.css'))
        .pipe(cssmin())
        .pipe(gulp.dest(libs.dist.css));
}
exports.libsCss = libsCss;

// Concatenate & minify libs
function libsLibs() {
    return gulp
        .src(libs.libs)
        .pipe(concat('libraries.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(libs.dist.js));
}
exports.libsLibs = libsLibs;

// Concatenate & minify libs
function libsPurchase() {
    return gulp
        .src(libs.purchase)
        .pipe(sourcemaps.init())
        .pipe(concat('purchases.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(libs.dist.js));
}
exports.libsPurchase = libsPurchase;

// Concatenate & minify js
function libsJs() {
    return gulp
        .src(libs.js)
        .pipe(sourcemaps.init())
        .pipe(concat('scripts.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(libs.dist.js));
}
exports.libsJs = libsJs;

function libsPosjs() {
    return gulp
        .src(libs.posjs)
        .pipe(sourcemaps.init())
        .pipe(concat('pos.min.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest(libs.dist.js));
}
exports.libsPosjs = libsPosjs;

// Move Libs Fonts
function libsFonts() {
    return gulp.src(libs.fonts).pipe(gulp.dest(libs.dist.fonts));
}
exports.libsFonts = libsFonts;

// Move Libs Images
function libsImg() {
    return gulp.src(libs.img).pipe(gulp.dest(libs.dist.img));
}
exports.libsImg = libsImg;

// Purify CSS
function purifyCSS() {
    return gulp
        .src([libs.dist.css + 'styles.css'])
        .pipe(purify(['./themes/default/views/**/*.php']))
        .pipe(cssmin())
        .pipe(gulp.dest('./themes/default/assets/dist/styles/'));
}
exports.purifyCSS = purifyCSS;

// Watch
function watch() {
    gulp.watch(libs.js, gulp.series('libsJs'));
    gulp.watch(libs.css, gulp.series('libsCss'));
    gulp.watch(libs.posjs, gulp.series('libsPosjs'));
    gulp.watch(libs.purchase, gulp.series('libsPurchase'));
}
exports.watch = watch;

// default
exports.libs = gulp.series(libsLibs, libsPurchase, libsCss, libsJs, libsPosjs, libsFonts, libsImg);
exports.default = watch;

// Get paths
const build_paths = require('./build_paths.js');

// Build Task
exports.buildWeb = gulp.series(copyWeb, buildWebDel, buildWebConfig);
exports.buildApp = gulp.series(copyApp, buildAppDel, buildAppConfig);
exports.build = gulp.series(copyWeb, buildWebDel, buildWebConfig, copyApp, buildAppDel, buildAppConfig);

// function buildApp() {
//     console.log('Start copying files');
//     copyApp().then(() => {
//         console.log('Start deleting files');
//         await buildAppDel();
//         console.log('Start copying config');
//         await buildAppConfig();
//     });
// }

// Web App
async function copyWeb() {
    return await new Promise(function(resolve, reject) {
        copyfiles(build_paths.spos, false, err => {
            if (err) {
                console.error(err);
            }
            resolve(true);
        });
    });
}

async function buildWebDel() {
    await del(build_paths.spos_del, { force: true }).then(del_paths => {
        console.log('File Deleted:\n', del_paths.join('\n'));
    });
}

async function buildWebConfig() {
    await gulp.src(build_paths.spos_config[0]).pipe(gulp.dest(build_paths.spos_config[1]));
}

// Desktop App
async function copyApp() {
    return await new Promise(function(resolve, reject) {
        copyfiles(build_paths.sa, false, err => {
            if (err) {
                console.error(err);
            }
            resolve(true);
        });
    });
}

async function buildAppDel() {
    await del(build_paths.sa_del, { force: true }).then(del_paths => {
        console.log('File Deleted:\n', del_paths.join('\n'));
    });
}

async function buildAppConfig() {
    await gulp.src(build_paths.sa_config[0]).pipe(gulp.dest(build_paths.sa_config[1]));
}
