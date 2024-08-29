// assets/js/app.js

require('../css/bootstrap.min.css');
require('../css/campus.css');
require('./jqueryui/jquery-ui.css');
require('./jqueryui/jquery.multiselect.css');
require('./jtable/themes/lightcolor/green/jtable.css');

// require jQuery normally
const $ = require('jquery');
// create global $ and jQuery variables
global.$ = global.jQuery = $;

//import $ from './jquery-3.2.1.min.js';
import './jqueryui/jquery-ui.js';
import './jqueryui/jquery.multiselect.js';
import greet from './campus';
import './bootstrap.bundle.min.js';
import './jtable/jquery.jtable.min.js';
