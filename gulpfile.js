var gulp = require('gulp'); // gulp
var jshint = require('gulp-jshint'); // JSHint plugin
var stylish = require('jshint-stylish'); // JSHint Stylish plugin
var stylelint = require('gulp-stylelint'); // stylelint plugin
var uglify = require('gulp-uglify'); // uglify js plugin
var pump = require('pump'); // gulp pump
var cssnano = require('gulp-cssnano'); // minify css
var sourcemaps = require('gulp-sourcemaps'); // use sourcemaps for css
var sass = require('gulp-sass'); // sass
var gutil = require('gulp-util'); // ultitly
var livereload = require('gulp-livereload'); // auto reload
var autoprefixer = require('autoprefixer'); // adds browser prefixes
var cssdeclsort = require('css-declaration-sorter'); // orders our css within the class/id
var plumber = require('gulp-plumber'); // Prevent pipe breaking caused by errors from gulp plugins
var postcss = require('gulp-postcss'); // PostCSS is a tool for transforming styles with JS plugins
var rename = require('gulp-rename'); // rename files
var phpcs = require('gulp-phpcs'); // Gulp plugin for running PHP Code Sniffer.
	
// Custom error function.
var onError = function(err) {
	// eslint-disable-next-line no-console
	console.log('An error ocurred: ', gutil.colors.magenta(err.message));
	gutil.beep();
	this.emit('end');
}

// Notifies our live reload when a file has changed
function notifyLiveReload(event) {
	var fileName = require('path').relative(__dirname, event.path);
	livereload.changed(fileName);
}

// build out dirs
var dirs = {
    css: 'assets/css',
    images: 'assetts/images',
    js: 'assets/js',
    admin: 'admin'  
};

// JavaScript linting with JSHint.
gulp.task('lintjs', function() {
  return gulp.src(dirs.admin + '/js/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter(stylish));
});

// Sass linting with Stylelint.
gulp.task('lintcss', function lintCssTask() {
  return gulp.src(dirs.admin + '/css/*.css')
    .pipe(stylelint({
      reporters: [
        {formatter: 'string', console: true}
      ]
    }));
});			

// Minify .js files.
gulp.task('minjs', function (cb) {
  pump([
        gulp.src(dirs.admin + '/js/*.js'),
        uglify(),
        gulp.dest(dirs.admin + '/js/')
    ],
    cb
  );
});

// Gulps our style file EDIT
/*
gulp.task('sass', function() {
	var processors = [
		autoprefixer({browsers: ['last 2 versions']}),
		cssdeclsort({order: 'alphabetically'}),
	];
	return gulp.src('./sass/style.scss')
		.pipe(plumber({errorHandler: onError}))
		.pipe(sass({ outputStyle: 'nested' }))
		.pipe(postcss(processors))
		.pipe(rename("style.css"))
		.pipe(gulp.dest('./'))
		.pipe(livereload())
});
*/

/*
		// Generate RTL .css files
		rtlcss: {
			woocommerce: {
				expand: true,
				cwd: '<%= dirs.css %>',
				src: [
					'*.css',
					'!select2.css',
					'!*-rtl.css'
				],
				dest: '<%= dirs.css %>/',
				ext: '-rtl.css'
			}
		},
*/    

// Minify all .css files.
gulp.task('mincss', function () {
    return gulp.src(dirs.admin + '/css/*.css')
        .pipe(sourcemaps.init())
        .pipe(cssnano())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dirs.admin + '/css'));
});
			
// Generate POT files.
// Check textdomain errors.
	
// PHP Code Sniffer.
var phpcsSrc = [
    '**/*.php', // Include all files    
    '!node_modules/**', // Exclude node_modules/
	'!vendor/**' // Exclude vendor/    
];

gulp.task('phpcs', function () {
    return gulp.src(phpcsSrc)
        // Validate files using PHP Code Sniffer
        .pipe(phpcs({
            bin: 'vendor/bin/phpcs',
            standard: './phpcs.ruleset.xml',
            warningSeverity: 0
        }))
        .pipe(phpcs.reporter('log')); // Log all problems that was found
});

// Tasks to run on watch/reload EDIT
gulp.task('watch', ['sass'], function() {		
	livereload.listen();
	gulp.watch('sass/**/*.scss', ['sass']);
	gulp.watch('sass/**/*.sass', ['sass']);
});

// Default gulp task
gulp.task('default', function() {
  console.log('Good Day!');
});

/*
'lintjs'
'lintcss'
'minjs'
'sass'
'mincss'
'phpcs'
*/