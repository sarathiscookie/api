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
mix.styles(["resources/css/pages/signin.css"], "public/css/signin.css").version();

/* CSS for all plugins */
mix.styles(
	[
	  "resources/css/plugins/datatables.min.css",
	  "resources/css/plugins/select2.css",
	  "resources/css/plugins/select2-bootstrap4.css",
	  "resources/css/plugins/daterangepicker.css",
	],
	"public/css/plugins.css"
).version();

/* CSS for all pages */
mix.styles(
	[ 
	  "resources/css/pages/dashboard.css", 
	  "resources/css/pages/adminManagerList.css",
	  "resources/css/pages/adminOrderList.css",
	],
	"public/css/all.css"
).version();

/*
 |--------------------------------------------------------------------------
 | JS Scripts
 |--------------------------------------------------------------------------
 |
 | JS for each pages
 */

/* js for all plugins */
mix.scripts(
	[ 
	  "resources/js/plugins/datatables.min.js",
	  "resources/js/plugins/select2.js",
	  "resources/js/plugins/moment.min.js",
	  "resources/js/plugins/daterangepicker.js",
	],
	"public/js/plugins.js"
).version();

/* js for all pages */
mix.scripts(
	[
		"resources/js/pages/dashboard.js",
		"resources/js/pages/adminManagerList.js",
		"resources/js/pages/adminCompanyList.js",
		"resources/js/pages/adminShopList.js",
		"resources/js/pages/adminUserList.js",
		"resources/js/pages/adminKeyList.js",
		"resources/js/pages/adminSupplierList.js",
		"resources/js/pages/adminProductList.js",
		"resources/js/pages/adminModuleList.js",
		"resources/js/pages/adminOrderList.js",
	],
	"public/js/all.js"
).version();
