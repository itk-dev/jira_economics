/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
//require('../css/app.css');
require('../scss/global.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

// create global $ and jQuery variables. Added to avoid $ not defined when calling $ from page specific script
global.$ = global.jQuery = $;

// Add Bootstrap to the mix
require('bootstrap');

// Add fontawesome
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

// Add select2
require('select2');

// Add filer
require('./jqueryFiler/js/jquery.filer.min.js');
require('./jqueryFiler/css/jquery.filer.css');
require('./jqueryFiler/css/themes/jquery.filer-dragdropbox-theme.css');
