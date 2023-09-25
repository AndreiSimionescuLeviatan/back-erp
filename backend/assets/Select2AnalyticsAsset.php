<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Select2 asset bundle.
 * Used in pages where `kartik-v/yii2-widget-select2` is not included
 */
class Select2AnalyticsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/select2/select2/dist';
    public $baseUrl = '@web';
    public $css = [
        'css/select2.min.css'
    ];
    public $js = [
        'js/select2.full.js'
    ];
    public $depends = [
        /**
         * we won't need these dependencies because we have them already in backend\assets\AppAsset
         */
        // 'yii\bootstrap4\BootstrapAsset',
        // 'yii\web\YiiAsset',
        'backend\assets\AppAsset'
    ];
}
