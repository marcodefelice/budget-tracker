const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .vue() // <- Add this
    .postCss('resources/css/app.css', 'public/css', [
        //
    ]);
