const mix = require('laravel-mix');
const webpackConfig = require('./webpack.config.js');

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

/**
 * Override webpack.config.js, without editing the file directly.
**/
mix.webpackConfig(webpackConfig);

/**
 *  mix.options({
 *    extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
 *    globalVueStyles: file, // Variables file to be imported in every component.
 *    processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
 *    purifyCss: false, // Remove unused CSS selectors.
 *    uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
 *    postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
 *  });
**/
mix.options({
    processCssUrls: false
});

/**
 * Mix the Admin Resources
**/
mix.js('resources/js/admin/plugin.js', 'admin/curtain-call-wp-admin.js');
mix.sass('resources/sass/admin/plugin.scss', 'admin/curtain-call-wp-admin.css');

/**
 * Mix the Frontend Resources
**/
mix.js('resources/js/frontend/plugin.js', 'frontend/curtain-call-wp-frontend.js');
mix.sass('resources/sass/frontend/plugin.scss', 'frontend/curtain-call-wp-frontend.css');
mix.sass('resources/sass/fontawesome/fontawesome.scss', 'frontend/fontawesomefree.css');

mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/', 'assets/fonts/fontawesome/');

mix.setPublicPath('assets');