<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class FlatpickrAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/flatpickr.css',
    ];
    public $js = [
        'js/flatpickr.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
