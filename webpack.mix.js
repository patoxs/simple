let mix = require('laravel-mix');

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

mix.js('resources/assets/js/app.js', 'public/js')
    .js('resources/assets/js/backend.js', 'public/js')
    .js('resources/assets/js/manager.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css')
    .sass('resources/assets/sass/backend.scss', 'public/css')
    .sass('resources/assets/sass/manager.scss', 'public/css')
    .copy('resources/assets/sass/helpers', 'public/css', true)
    .copy('resources/assets/themes', 'public/themes', true)
    .copy('resources/assets/logos', 'public/logos', true)
    .copy('resources/assets/js/helpers', 'public/js/helpers', true)
    .copy('resources/assets/img', 'public/img', true)
    .copy('resources/assets/ayuda', 'public/ayuda', true)
    .copy('resources/assets/uploads', 'public/uploads', true)
    .copy('resources/assets/calendar', 'public/calendar', true)
    .copy('resources/assets/fonts/bootstrap', 'public/fonts', true)
    .disableNotifications()
    .browserSync({
        notify: false,
        open: true,
        proxy: '127.0.0.1:8000'
    })
    .webpackConfig({
        resolve: {
            alias: {
                'jquery-ui': 'jquery-ui-dist/jquery-ui.js'
            }
        }
    });