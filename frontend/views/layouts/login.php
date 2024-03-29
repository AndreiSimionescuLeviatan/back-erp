<?php

/* @var $this \yii\web\View */

/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?php echo Yii::$app->language; ?>">
    <head>
        <meta charset="<?php echo Yii::$app->charset; ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags(); ?>
        <title><?php echo Html::encode($this->title); ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition login-page">
    <?php $this->beginBody(); ?>
    <div class="login-box">
        <div class="login-logo pr-5 pl-5">
            <?php echo Html::img('@web/images/logo_ecf.png', ['class' => 'img-fluid ', 'alt' => 'Leviatan Design']) ?>
        </div>
        <!-- /.login-logo -->
        <?php echo $content; ?>
    </div>
    <!-- /.login-box -->

    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage(); ?>