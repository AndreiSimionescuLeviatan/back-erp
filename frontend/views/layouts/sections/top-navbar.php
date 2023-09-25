<?php

use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <?php echo Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        'options' => [],
    ]) ?>
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="d-flex nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <span class="d-flex">
                    <?php
                    echo Html::img('@web/images/lte-images/avatar4.png', [
                        'class' => 'align-self-center img-circle elevation-2',
                        'style' => 'width: 34px; position: absolute; left: -24px; top: 2px;',
                        'alt' => Yii::$app->user->identity->username
                    ]);
                    ?>
                    <span class="d-none d-md-inline">
                        <?php echo Yii::t('app', 'Hello') . ' ' . Yii::$app->user->identity->username; ?>
                    </span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <?php
                    echo Html::img('@web/images/lte-images/avatar4.png', [
                        'class' => 'img-circle elevation-2',
                        'alt' => Yii::$app->user->identity->username
                    ])
                    ?>
                    <p style="line-height: 18px;">
                        <?php echo Yii::$app->user->identity->username; ?>
                        <small><?php echo Yii::t('app', 'Member since ') . date('M. Y', Yii::$app->user->identity->created_at) ?></small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <?php
                    echo Html::a(
                        Yii::t('app', 'Profile'),
                        \yii\helpers\Url::to(['user/update-password', 'id' => Yii::$app->user->id]),
                        ['class' => 'btn btn-default btn-flat']
                    );
                    if (!Yii::$app->user->isGuest) {
                        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline-block float-right']);
                        echo Html::submitButton(
                            Yii::t('app', 'Sign out'),
                            ['class' => 'btn btn-default btn-flat']
                        );
                        echo Html::endForm();
                    }
                    ?>
                </li>
            </ul>
        </li>
    </ul>
</nav>
<!-- /.navbar -->