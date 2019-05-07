const mix = require("laravel-mix");

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

mix.js("resources/js/app.js", "public/js").sass(
	"resources/sass/app.scss",
	"public/css"
);

/*
 |--------------------------------------------------------------------------
 | CSS Styles
 |--------------------------------------------------------------------------
 |
 | css for each pages
 */

/* css for signin page */
mix.styles(["resources/css/signin.css"], "public/css/signin.css").version();

/* css for all pages */
mix.styles([
	"resources/css/dashboard.css", 
	"resources/css/adminManagerList.css"], 
	"public/css/all.css"
	).version();

/*
 |--------------------------------------------------------------------------
 | JS Scripts
 |--------------------------------------------------------------------------
 |
 | JS for each pages
 */

mix.scripts([
	"resources/js/dashboard.js", 
	"resources/js/adminManagerList.js"],
	"public/js/all.js"
	).version();
