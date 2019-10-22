const mix = require('laravel-mix');

mix.webpackConfig({
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.common.js'
        },
    },
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'resources/js/dist/').sourceMaps().browserSync();

mix.copyDirectory('node_modules/tinymce/skins/ui/oxide', 'resources/js/tinymce/skin');