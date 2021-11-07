const mix = require('laravel-mix');
const webpackConfig = require('./webpack.config.js');

mix
    .setPublicPath('plugin/assets')
    .webpackConfig(webpackConfig)
    .options({
        processCssUrls: false
    })
    /**
     * Admin Assets
     */
    .js('resources/js/admin/plugin.js', 'admin/curtain-call-wp-admin.js')
    .sass('resources/sass/admin/plugin.scss', 'admin/curtain-call-wp-admin.css')
    /**
     * Frontend Assets
     */
    .js('resources/js/frontend/plugin.js', 'frontend/curtain-call-wp-frontend.js')
    .sass('resources/sass/frontend/plugin.scss', 'frontend/curtain-call-wp-frontend.css')
    .sass('resources/sass/fontawesome/fontawesome.scss', 'frontend/fontawesomefree.css')
    .copy('node_modules/@fortawesome/fontawesome-free/webfonts/', 'plugin/assets/fonts/fontawesome/');
