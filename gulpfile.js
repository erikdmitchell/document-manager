var gulp = require('gulp'); // gulp
var jshint = require('gulp-jshint'); // JSHint plugin
var stylish = require('jshint-stylish'); // JSHint Stylish plugin

// var sass = require('gulp-sass'); // sass
// var gutil = require('gulp-util'); // ultitly
// var livereload = require('gulp-livereload'); // auto reload
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

// Tasks to run on watch/reload EDIT
gulp.task('watch', ['sass'], function() {		
	livereload.listen();
	gulp.watch('sass/**/*.scss', ['sass']);
	gulp.watch('sass/**/*.sass', ['sass']);
});


		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			fonts: 'assets/fonts',
			images: 'assets/images',
			js: 'assets/js'
		},


gulp.task('lint', function() {
  return gulp.src('./lib/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter(stylish));
});

			all: [
				'Gruntfile.js',
				'<%= dirs.js %>/admin/*.js',
				'!<%= dirs.js %>/admin/*.min.js',
				'<%= dirs.js %>/frontend/*.js',
				'!<%= dirs.js %>/frontend/*.min.js',
				'includes/gateways/simplify-commerce/assets/js/*.js',
				'!includes/gateways/simplify-commerce/assets/js/*.min.js'
			]
/*


		// Sass linting with Stylelint.
		stylelint: {
			options: {
				configFile: '.stylelintrc'
			},
			all: [
				'<%= dirs.css %>/*.scss',
				'!<%= dirs.css %>/select2.scss'
			]
		},

		// Minify .js files.
		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false
				},
				output: {
					comments : /@license|@preserve|^!/
				}
			},
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/admin/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/admin/',
					ext: '.min.js'
				}]
			},
			vendor: {
				files: {
					'<%= dirs.js %>/accounting/accounting.min.js': ['<%= dirs.js %>/accounting/accounting.js'],
					'<%= dirs.js %>/jquery-blockui/jquery.blockUI.min.js': ['<%= dirs.js %>/jquery-blockui/jquery.blockUI.js'],
					'<%= dirs.js %>/jquery-cookie/jquery.cookie.min.js': ['<%= dirs.js %>/jquery-cookie/jquery.cookie.js'],
					'<%= dirs.js %>/js-cookie/js.cookie.min.js': ['<%= dirs.js %>/js-cookie/js.cookie.js'],
					'<%= dirs.js %>/jquery-flot/jquery.flot.min.js': ['<%= dirs.js %>/jquery-flot/jquery.flot.js'],
					'<%= dirs.js %>/jquery-flot/jquery.flot.pie.min.js': ['<%= dirs.js %>/jquery-flot/jquery.flot.pie.js'],
					'<%= dirs.js %>/jquery-flot/jquery.flot.resize.min.js': ['<%= dirs.js %>/jquery-flot/jquery.flot.resize.js'],
					'<%= dirs.js %>/jquery-flot/jquery.flot.stack.min.js': ['<%= dirs.js %>/jquery-flot/jquery.flot.stack.js'],
					'<%= dirs.js %>/jquery-flot/jquery.flot.time.min.js': ['<%= dirs.js %>/jquery-flot/jquery.flot.time.js'],
					'<%= dirs.js %>/jquery-payment/jquery.payment.min.js': ['<%= dirs.js %>/jquery-payment/jquery.payment.js'],
					'<%= dirs.js %>/jquery-qrcode/jquery.qrcode.min.js': ['<%= dirs.js %>/jquery-qrcode/jquery.qrcode.js'],
					'<%= dirs.js %>/jquery-serializejson/jquery.serializejson.min.js': ['<%= dirs.js %>/jquery-serializejson/jquery.serializejson.js'],
					'<%= dirs.js %>/jquery-tiptip/jquery.tipTip.min.js': ['<%= dirs.js %>/jquery-tiptip/jquery.tipTip.js'],
					'<%= dirs.js %>/jquery-ui-touch-punch/jquery-ui-touch-punch.min.js': ['<%= dirs.js %>/jquery-ui-touch-punch/jquery-ui-touch-punch.js'],
					'<%= dirs.js %>/prettyPhoto/jquery.prettyPhoto.init.min.js': ['<%= dirs.js %>/prettyPhoto/jquery.prettyPhoto.init.js'],
					'<%= dirs.js %>/prettyPhoto/jquery.prettyPhoto.min.js': ['<%= dirs.js %>/prettyPhoto/jquery.prettyPhoto.js'],
					'<%= dirs.js %>/flexslider/jquery.flexslider.min.js': ['<%= dirs.js %>/flexslider/jquery.flexslider.js'],
					'<%= dirs.js %>/zoom/jquery.zoom.min.js': ['<%= dirs.js %>/zoom/jquery.zoom.js'],
					'<%= dirs.js %>/photoswipe/photoswipe.min.js': ['<%= dirs.js %>/photoswipe/photoswipe.js'],
					'<%= dirs.js %>/photoswipe/photoswipe-ui-default.min.js': ['<%= dirs.js %>/photoswipe/photoswipe-ui-default.js'],
					'<%= dirs.js %>/round/round.min.js': ['<%= dirs.js %>/round/round.js'],
					'<%= dirs.js %>/stupidtable/stupidtable.min.js': ['<%= dirs.js %>/stupidtable/stupidtable.js'],
					'<%= dirs.js %>/zeroclipboard/jquery.zeroclipboard.min.js': ['<%= dirs.js %>/zeroclipboard/jquery.zeroclipboard.js']
				}
			},
			frontend: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/frontend/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/frontend/',
					ext: '.min.js'
				}]
			},
			simplify_commerce: {
				files: [{
					expand: true,
					cwd: 'includes/gateways/simplify-commerce/assets/js/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: 'includes/gateways/simplify-commerce/assets/js/',
					ext: '.min.js'
				}]
			}
		},

		// Compile all .scss files.
		sass: {
			compile: {
				options: {
					sourceMap: 'none'
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/',
					ext: '.css'
				}]
			}
		},

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

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: ['*.css'],
				dest: '<%= dirs.css %>/',
				ext: '.css'
			}
		},

		// Concatenate select2.css onto the admin.css files.
		concat: {
			admin: {
				files: {
					'<%= dirs.css %>/admin.css' : ['<%= dirs.css %>/select2.css', '<%= dirs.css %>/admin.css'],
					'<%= dirs.css %>/admin-rtl.css' : ['<%= dirs.css %>/select2.css', '<%= dirs.css %>/admin-rtl.css']
				}
			}
		},

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