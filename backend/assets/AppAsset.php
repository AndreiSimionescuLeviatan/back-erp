<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // removed because it increases the page load on SSV
        //'//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback',
        'css/font.css',
        'css/fontawesome-free/css/all.min.css',
        'css/icheck-bootstrap/icheck-bootstrap.min.css',
        'css/adminlte/adminlte.min.css',
        'css/site.css',
        'css/toastr.css',
    ];
    public $js = [
        'js/adminlte/adminlte.js',
        'js/bootbox.all.min.js',
        'js/app.js',
        'js/pdf.js',
        'js/pdf.worker.js',
        'js/konva.min.js',
        'js/toastr.js',
        'js/moment-with-locales.min.js',
        'js/lottie-player.js',
        'js/full.calendar.index.global.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
