<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback',
        'css/fontawesome-free/css/all.min.css',
        'css/icheck-bootstrap/icheck-bootstrap.min.css',
        'css/adminlte/adminlte.min.css',
        'css/site.css',
    ];
    public $js = [
        'js/adminlte/adminlte.js',
        'js/bootbox.all.min.js',
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}

