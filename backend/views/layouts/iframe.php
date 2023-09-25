<?php

use backend\assets\ChartJsAsset;
use backend\assets\Select2AnalyticsAsset;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use yii\helpers\Html;
use yii\web\View;

Select2AnalyticsAsset::register($this);
ChartJsAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language ?>">
<head>
    <meta charset="<?php echo Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?php echo Html::encode($this->title) ?></title>
    <?php $this->head(); ?>
</head>
<body class="layout-fixed sidebar-mini hold-transition">
<?php $this->beginBody(); ?>
<div class="wrapper">
    <div class="content-wrapper pl-3 pr-3" style="margin-left: 0;">
        <section class="content-header p-0">
            <div class="container-fluid" style="padding-left: 0;">
                <div class="row">
                    <div class="col-12">
                        <?php foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                            echo '<div class="alert alert-' . $key . ' alert-dismissible fade show mb-0 mt-2">';
                            echo $message;
                            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                            echo '<span aria-hidden="true">&times;</span>';
                            echo '</button>';
                            echo '</div>';
                        } ?>
                        <?php
                        if (Yii::$app->session->hasFlash('success')) {
                            $this->registerJs('$(".content-header").removeClass("p-0");$(".alert-success").animate({opacity: 1.0}, 10000).fadeOut("slow", "swing", function () {$(".content-header").addClass("p-0")});');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
        <section role="main" class="content" style="padding: 10px;">
            <?php echo $content; ?>
        </section>
    </div>
</div>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
