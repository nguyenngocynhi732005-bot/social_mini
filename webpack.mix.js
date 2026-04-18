const mix = require('laravel-mix');

// Đoạn code sửa lỗi "Invalid options object"
mix.webpackConfig({
    stats: {
        children: true,
    },
});

// Vô hiệu hóa Progress Plugin để tránh lỗi Schema
if (mix.inProduction()) {
    mix.options({
        processCssUrls: false
    });
} else {
    mix.options({
        beforeLoaderCheck: (loader) => {
            // Loại bỏ progress plugin gây lỗi
        }
    });
}

mix.js('resources/js/app.js', 'public/js');