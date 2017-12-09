var gulp = require('gulp'); // gulp
var jshint = require('gulp-jshint'); // JSHint plugin
var stylish = require('jshint-stylish'); // JSHint Stylish plugin
var stylelint = require('gulp-stylelint'); // stylelint plugin
var uglify = require('gulp-uglify'); // uglify js plugin
var pump = require('pump'); // gulp pump
var cssnano = require('gulp-cssnano'); // minify css
var sourcemaps = require('gulp-sourcemaps'); // use sourcemaps for css

// var sass = require('gulp-sass'); // sass
var gutil = require('gulp-util'); // ultitly
var livereload = require('gulp-livereload'); // auto reload
// var autoprefixer = require('autoprefixer'); // adds browser prefixes
// var cssdeclsort = require('css-declaration-sorter'); // orders our css within the class/id
// var plumber = require('gulp-plumber'); // Prevent pipe breaking caused by errors from gulp plugins
// var postcss = require('gulp-postcss'); // PostCSS is a tool for transforming styles with JS plugins
// var rename = require('gulp-rename'); // rename files
	
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

// Default gulp task
gulp.task('default', function() {
  console.log('Good Day!');
});

// Tasks to run on watch/reload EDIT
gulp.task('watch', ['sass'], function() {		
	livereload.listen();
	gulp.watch('sass/**/*.scss', ['sass']);
	gulp.watch('sass/**/*.sass', ['sass']);
});


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
    .pipe(gulpStylelint({
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
			
/*

		







		// Watch changes for assets.
		watch: {
			css: {
				files: ['<%= dirs.css %>/*.scss'],
				tasks: ['sass', 'rtlcss', 'cssmin', 'concat']
			},
			js: {
				files: [
					'<%= dirs.js %>/admin/*js',
					'<%= dirs.js %>/frontend/*js',
					'!<%= dirs.js %>/admin/*.min.js',
					'!<%= dirs.js %>/frontend/*.min.js'
				],
				tasks: ['jshint', 'uglify']
			}
		},

		// Generate POT files.
		// Check textdomain errors.
		// Exec shell commands.
		// Clean the directory.
		/*
		clean: {
			apigen: {
				src: [ 'wc-apidocs' ]
			}
		},
        */
		// PHP Code Sniffer.
		phpcs: {
			options: {
				bin: 'vendor/bin/phpcs'
			},
			dist: {
				options: {
					standard: './phpcs.ruleset.xml'
				},
				src:  [
					'**/*.php',                                                  // Include all files
					'!apigen/**',                                                // Exclude apigen/
					'!includes/api/legacy/**',                                   // Exclude legacy REST API
					'!includes/gateways/simplify-commerce/includes/Simplify/**', // Exclude simplify commerce SDK
					'!includes/libraries/**',                                    // Exclude libraries/
					'!node_modules/**',                                          // Exclude node_modules/
					'!tests/cli/**',                                             // Exclude tests/cli/
					'!tmp/**',                                                   // Exclude tmp/
					'!vendor/**'                                                 // Exclude vendor/
				]
			}
		},

		// Autoprefixer.
		postcss: {
			options: {
				processors: [
					require( 'autoprefixer' )({
						browsers: [
							'> 0.1%',
							'ie 8',
							'ie 9'
						]
					})
				]
			},
			dist: {
				src: [
					'<%= dirs.css %>/*.css'
				]
			}
		}
	});




	// Load NPM tasks to be used here
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-shell' );
	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-stylelint' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-concat' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );

	// Register tasks
	grunt.registerTask( 'default', [
		'js',
		'css',
		'i18n'
	]);

	grunt.registerTask( 'js', [
		'jshint',
		'uglify:admin',
		'uglify:frontend'
	]);

	grunt.registerTask( 'css', [
		'sass',
		'rtlcss',
		'postcss',
		'cssmin',
		'concat'
	]);

	grunt.registerTask( 'docs', [
		'clean:apigen',
		'shell:apigen'
	]);

	// Only an alias to 'default' task.
	grunt.registerTask( 'dev', [
		'default'
	]);

	grunt.registerTask( 'i18n', [
		'checktextdomain',
		'makepot'
	]);

	grunt.registerTask( 'e2e-tests', [
		'shell:e2e_tests'
	]);

	grunt.registerTask( 'e2e-tests-grep', [
		'shell:e2e_tests_grep'
	]);

	grunt.registerTask( 'e2e-test', [
		'shell:e2e_test'
	]);
};    
*/