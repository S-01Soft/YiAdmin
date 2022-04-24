require.config({
    urlArgs: 'v=' + (Config.statics && Config.statics.open && Config.statics.version || (Config.debug ? Date.now() : '1.0.0')), 
    baseUrl: '/static/',
    paths: {
        'jquery': Yi.getStatic('jquery') || 'library/jquery/dist/jquery.min',
        'lodash': Yi.getStatic('lodash') || 'library/lodash/dist/lodash.min',
        layer: Yi.getStatic('layer') || 'vendor/layer/layer',
        'moment': Yi.getStatic('moment') || 'library/moment/min/moment.min',
        'hook': 'js/hook',
        'formatter': 'js/formatter',
        'bootstrap': Yi.getStatic('bootstrap') || 'library/bootstrap/dist/js/bootstrap.bundle.min',
        'toastr': Yi.getStatic('toastr') || 'library/toastr/toastr.min',
        lang: '../_lang?callback=define&module=' + Config.module + '&c=' + Config.controller + '&r=' + Config.langVersion
    },
    shim: {
        bootstrap: ['jquery'],
        layer: {
            deps: ['jquery'],
            exports: 'layer'
        }
    },
    map: {
        '*': {
            css: 'library/require-css/css.min'
        }
    },
    waitSeconds: 60,
});
window.$lang = Yi.lang.parse;
for (var i = 0; i < Yi._config.length; i ++) {
    require.config(Yi._config[i]);
}
Yi.require('jquery', 'layer', 'moment', 'bootstrap', 'toastr', 'css', 'lodash', 'lang', 'formatter');
require(Yi.modules(), function () {
    Yi.lang.load(require('lang'));
    window.jquery = window.$ = require('jquery');
    window.Layer = require('layer');
    window.Moment = require('moment');
    window.Toastr = require('toastr');
    window._ = window.__ = require('lodash');
    if (!Config.index || !Config.index.requires) Yi.init();
    else Yi.load(Config.index.requires, function() {
        Yi.init();
    });
});