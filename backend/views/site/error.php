<?php

use common\components\HttpStatus;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */

$this->title = $name;

$statusCode = Yii::$app->errorHandler->exception->statusCode;
if ($statusCode === HttpStatus::FORBIDDEN) {
    $message = Yii::t('app', 'You are not authorized to perform this action.');
}
?>
<div class="h-100 d-flex align-items-center justify-content-center site-error">
    <div class="form-row">
        <div class="col-12 text-center text-warning" style="font-size: 200px">
            <i class="far fa-frown"></i>
        </div>
        <div class="col-12 justify-content-center align-items-center d-inline">
            <h5 class="text-center"><?php echo Html::encode($message); ?></h5>
            <h6 class="text-center"><?php echo Yii::t('app', 'Please contact an administrator!'); ?></h6>
        </div>
        <div class="col-12 text-center">
            <?php echo Html::a(Yii::t('app', 'Home'), Url::home(), [
                'style' => 'text-decoration: underline',
            ]); ?>
        </div>
    </div>
</div>

<?php
$this->registerJs(
    "removeAsideAndNav();",
    View::POS_READY,
    'remove=aside-and-nav'
);
?>

<script>
    function removeAsideAndNav()
    {
        $('aside').remove();
        $('nav').remove();
        $('.wrapper').find('.content-wrapper')
            .addClass('d-flex justify-content-center')
            .css('margin-left', '0')
            .removeClass('pl-3 pr-3');
    }
</script>
