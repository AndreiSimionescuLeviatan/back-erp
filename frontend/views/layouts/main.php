<?php

/* @var $this \yii\web\View */

/* @var $content string */

use frontend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
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
        <link rel="shortcut icon" type="image/x-icon" ref="/favicon.ico"/>
        <?php $this->head(); ?>
    </head>
    <body class="layout-fixed sidebar-mini h;old-transition">
    <?php $this->beginBody(); ?>
    <div class="wrapper">
        <?php
        // Navbar
        $this->beginContent('@app/views/layouts/sections/top-navbar.php');
        $this->endContent();
        // Main Sidebar Container
        $this->beginContent('@app/views/layouts/sections/left-sidebar.php');
        $this->endContent();
        ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper pl-3 pr-3">
            <!-- Content Header (Page header) -->
            <section class="content-header p-0">
                <div class="container-fluid" style="padding-left: 0;">
                    <div class="row">
                        <div class="col-12">
                            <?php foreach (Yii::$app->session->getAllFlashes() as $key => $message) {
                                echo '<div class="alert alert-' . $key . ' alert-dismissible fade show mb-0">';
                                echo $message;
                                echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                                echo '<span aria-hidden="true">&times;</span>';
                                echo '</button>';
                                echo '</div>';
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
        <!-- Main Footer -->
        <footer class="main-footer">
            <strong>
                Copyright &copy; <?php echo date('Y') ?> <a href="<?php echo Yii::$app->homeUrl; ?>">Leviatan Design</a>.
            </strong>
        </footer>
    </div>
    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage(); ?>