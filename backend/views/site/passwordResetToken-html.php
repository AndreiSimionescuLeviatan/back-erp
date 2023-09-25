<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user backend\modules\adm\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<div class="password-reset">
    <p><?php echo Yii::t('app', 'Hello'); ?> <?php echo Html::encode($user->fullName()); ?>,</p>
    <p><?php echo Yii::t('app', 'Follow the link below to reset your password:'); ?></p>
    <p><?php echo Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
