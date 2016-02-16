module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            less: {
                files: [
                    'src/Keltanas/PageBundle/Resources/public/less/*.less'
                ],
                tasks: ['less'],
                options: {
                    debounceDelay: 1000
                }
            },
            configFiles: {
                files: [ 'Gruntfile.js' ],
                options: {
                    reload: true
                }
            }
        },
        less: {
            env: {
                options: {
                    compress: false
                },
                files: {
                    "web/css/style.src.css": 'web/bundles/keltanaspage/less/*.less'
                }
            }
        },
        copy: {
            fancybox: {expand: true, flatten: true,
                src: [
                    'web/lib/fancybox/source/*.gif',
                    'web/lib/fancybox/source/*.png'
                ],
                dest: 'web/css'
            },
            fancybox_helpers: {expand: true, flatten: true,
                src: 'web/lib/fancybox/source/helpers/fancybox_buttons.png',
                dest: 'web/css/helpers'
            },
            fonts: {expand: true, flatten: true,
                src: [
                    'web/lib/bootstrap/fonts/*'
                ],
                dest: 'web/fonts'
            },
            img: {expand: true, flatten: true,
                src: [
                    'web/bundles/keltanaspage/img/*'
                ],
                dest: 'web/img'
            }
        },
        cssmin: {
            options: {
                keepSpecialComments: 0,
                processImport: true,
                relativeTo: true,
                rebase: true
            },
            target: {
                files: {
                    'web/css/style.css': [
                        'web/css/style.src.css',
                        'web/bundles/keltanaspage/css/highlightjs/ir_black.css'
                    ]
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
            },
            build: {
                files: {
                    "web/js/script.js": [
                        "web/lib/jquery/dist/jquery.js",
                        "web/lib/bootstrap/dist/js/bootstrap.js",
                        "web/lib/jquery-form/jquery.form.js",
                        "web/bundles/keltanaspage/js/jquery.preview.js",
                        "web/bundles/keltanaspage/js/highlight.pack.js"
                    ]
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-copy');

    // Default task(s).
    grunt.registerTask('default', ['copy', 'less', 'cssmin', 'uglify']);
};
