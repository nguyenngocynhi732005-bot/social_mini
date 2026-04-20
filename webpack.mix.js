const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js');

mix.options({
    processCssUrls: false,
});

mix.override((webpackConfig) => {
    // Disable WebpackBar to avoid ProgressPlugin schema errors on newer webpack versions.
    webpackConfig.plugins = (webpackConfig.plugins || []).filter(
        (plugin) => plugin && plugin.constructor && plugin.constructor.name !== 'WebpackBar'
    );
});