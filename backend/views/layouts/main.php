<?php

/**
 * @var $this \yii\web\View
 * @var $content string
 */

use backend\assets\AppAsset;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php
$this->beginPage();
$identity = Settings::getIdentityImages();
?>
    <!DOCTYPE html>
    <html lang="<?php echo Yii::$app->language; ?>">
    <head>
        <meta charset="<?php echo Yii::$app->charset; ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php $this->registerCsrfMetaTags() ?>
        <title><?php echo Html::encode($identity['title']); ?></title>
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo $identity['icon_tab_image']; ?>"/>
        <?php $this->head(); ?>
    </head>
    <!-- Added 'ondragstart="return false" draggable="false"' so we wont be able to select a text inside an input and drag it on over input -->
    <!-- It will work now for all pages. If we need somewhere to do the drag and drop to review the code below -->
    <body class="layout-fixed sidebar-mini hold-transition" ondragstart="return false" draggable="false">
    <?php $this->beginBody(); ?>
    <div class="wrapper">
        <!-- Preloader -->
        <div class="flex-column justify-content-center align-items-center" id="app-preloader">
            <!-- removed class="animation__shake"-->
            <?php
            if (
                isset($_COOKIE['app-preloader-img-src'])
                && str_ends_with($_COOKIE['app-preloader-img-src'], '.json')
            ): ?>
                <lottie-player src="<?php echo $_COOKIE['app-preloader-img-src']; ?>" background="transparent" speed="1"
                               style="width: 350px; height: 350px; border-radius: 50%" loop autoplay>
                </lottie-player>
            <?php else: ?>
                <img src="<?php
                if (isset($_COOKIE['app-preloader-img-src'])) {
                    echo $_COOKIE['app-preloader-img-src'];
                }
                ?>" alt="Leviatan Design ERP" height="300">
            <?php endif; ?>
        </div>
        <?php
        $userImage = User::getUserImage(Yii::$app->user->id ?? 0);
        // Navbar
        $this->beginContent('@app/views/layouts/sections/top-navbar.php', ['userProfileImage' => $userImage]);
        $this->endContent();
        // Main Sidebar Container
        $sideBar = Yii::$app->user->id == 2 ? 'left-menu' : 'left-sidebar';
        $this->beginContent("@app/views/layouts/sections/{$sideBar}.php", ['userProfileImage' => $userImage]);
        $this->endContent();

        $controller = Yii::$app->controller;
        $default_controller = Yii::$app->defaultRoute;
        $wrapperCssClass = (($controller->id === $default_controller) && ($controller->action->id === $controller->defaultAction)) ? ' home-page-wrapper' : '';
        ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper pl-3 pr-3<?php echo $wrapperCssClass; ?>">
            <!-- Content Header (Page header) -->
            <section class="content-header p-0">
                <div class="container-fluid" style="padding-left: 0;">
                    <div class="row">
                        <div class="col-12">
                            <?php foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                                if (
                                    $key != 'danger-costs-missing'
                                    && $key != 'danger-costs-notfound'
                                    && $key != 'accounts-duplicate'
                                    && $key != 'invoices-duplicate'
                                    && $key != 'invoices-success'
                                ) {
                                    echo '<div class="alert alert-' . $key . ' alert-dismissible fade show mb-0 mt-2">';
                                    echo $message;
                                    echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                                    echo '<span aria-hidden="true">&times;</span>';
                                    echo '</button>';
                                    echo '</div>';
                                }
                            } ?>
                            <?php
                            if (Yii::$app->session->hasFlash('success'))
                                $this->registerJs('$(".content-header").removeClass("p-0");$(".alert-success").animate({opacity: 1.0}, 10000).fadeOut("slow", "swing", function () {$(".content-header").addClass("p-0")});')
                            ?>
                        </div>
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content-header -->
            <!-- Main content -->
            <section role="main" class="content">
                <?php echo $content; ?>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage(); ?>