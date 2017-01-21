module.exports = function(grunt) {

	var config = {
		assets: 'admin/assets'
	};

    grunt.initConfig({

	    config: config,

        pkg: grunt.file.readJSON('package.json'),

		sass: {
			dist: {
				options: {
					sourceMap: true
				},
				files: {
					'<%= config.assets %>/css/aw-main.css': '<%= config.assets %>/css/aw-main.scss',
					'<%= config.assets %>/css/editor.css': '<%= config.assets %>/css/editor.scss',
					'<%= config.assets %>/css/email-preview.css': '<%= config.assets %>/css/email-preview.scss'
				}
			}
		},

        uglify: {
            main: {
                options: {
                    mangle: false
                },
                files: {
                    '<%= config.assets %>/js/min/automatewoo.min.js': [
	                    '<%= config.assets %>/js/automatewoo.js'
                    ],
					'<%= config.assets %>/js/min/automatewoo-sms-test.min.js': [
						'<%= config.assets %>/js/automatewoo-sms-test.js'
					],
					'<%= config.assets %>/js/min/automatewoo-workflows.min.js': [
						'<%= config.assets %>/js/automatewoo-workflows.js'
					],
					'<%= config.assets %>/js/min/automatewoo-rules.min.js': [
						'<%= config.assets %>/js/automatewoo-rules.js'
					],
					'<%= config.assets %>/js/min/automatewoo-email-preview.min.js': [
						'<%= config.assets %>/js/automatewoo-email-preview.js'
					],
					'<%= config.assets %>/js/min/automatewoo-tools.min.js': [
						'<%= config.assets %>/js/automatewoo-tools.js'
					],
					'<%= config.assets %>/js/min/automatewoo-modal.min.js': [
						'<%= config.assets %>/js/automatewoo-modal.js'
					],
					'<%= config.assets %>/js/min/automatewoo-variables.min.js': [
						'<%= config.assets %>/js/automatewoo-variables.js'
					],
					'<%= config.assets %>/js/min/validate.min.js': [
						'<%= config.assets %>/js/validate.js'
					],
					'<%= config.assets %>/js/min/dashboard.min.js': [
						'<%= config.assets %>/js/dashboard.js'
					]
                }
            }
        },

	    autoprefixer: {
		    options: {
			    browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
		    },
		    files: {
			    expand: true,
			    src: '<%= config.assets %>/css/*.css'
		    }
	    },

        watch: {
            css: {
                files: '<%= config.assets %>/css/*.scss',
                tasks: ['sass', 'autoprefixer']
            },
            js: {
                files: '<%= config.assets %>/js/*.js',
                tasks: ['uglify:main']
            }
        },


		notify_hooks: {
			options: {
				enabled: true,
				success: true,
				duration: 1
			}
		}

    });


	grunt.loadNpmTasks('grunt-notify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-autoprefixer');

	grunt.task.run('notify_hooks');

	grunt.registerTask('build', [
		'sass',
		'uglify',
		'autoprefixer',
	]);

    // Default task(s).
    grunt.registerTask('default', ['watch']);

};