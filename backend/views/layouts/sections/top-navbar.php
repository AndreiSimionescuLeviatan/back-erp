<?php

use yii\bootstrap4\Breadcrumbs;
use yii\bootstrap4\Html;
use yii\helpers\Url;

/* @var $userProfileImage */

$userName = Yii::$app->user->identity->username ?? '';
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
        <li class="row nav-item dropdown">
            <?php if (Yii::$app->user->can('manageFindReplace')) { ?>
                <a class="nav-link" href="<?php echo Url::to(['/entity/entity-action-log/index']); ?>"
                   title=" <?php echo Yii::t('app', 'Entities replace'); ?>">
                    <i class="fa fa-cogs"></i>
                </a>
            <?php } ?>
            <?php if (Yii::$app->user->can('SuperAdmin')) { ?>
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-tools"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header bg-primary">Settings Menu</span>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/adm/settings/index']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Settings list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/adm/settings/create']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Add setting'); ?>
                    </a>
                    <span class="dropdown-item dropdown-header bg-primary">RBAC Menu</span>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/assignment']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Assignments list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/route']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Route list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/permission']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Permission list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/menu']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Menu list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/role']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'Role list'); ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo Url::to(['/admin/user']); ?>" class="dropdown-item">
                        <?php echo Yii::t('app', 'User list'); ?>
                    </a>
                </div>
            <?php } ?>
        </li>
        <li class="d-flex nav-item dropdown ml-2 user-menu">
            <a href="#" class="nav-link pl-5 dropdown-toggle" data-toggle="dropdown">
                <?php
                echo Html::img($userProfileImage, [
                    'class' => 'align-self-center img-circle elevation-2',
                    'style' => 'width: 34px; position: absolute; left: 7px; top: 2px;',
                    'alt' => $userName
                ]);
                ?>
                <span class="d-flex">
                    <span class="d-none d-md-inline">
                        <?php echo Yii::t('app', 'Hello') . ', ' . $userName; ?>
                    </span>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <?php
                    echo Html::img($userProfileImage, [
                        'class' => 'img-circle elevation-2',
                        'alt' => $userName
                    ]);
                    ?>
                    <?php if (!Yii::$app->user->isGuest) { ?>
                        <p style="line-height: 18px;">
                            <?php echo $userName; ?>
                            <small><?php echo Yii::t('app', 'Member since ') . date('M. Y', Yii::$app->user->identity->created_at); ?></small>
                        </p>
                    <?php } ?>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <?php
                    echo Html::a(
                        Yii::t('app', 'Profile'),
                        Url::to(['/adm/user/update-password', 'id' => Yii::$app->user->id ?? 0]),
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