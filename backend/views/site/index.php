<?php

/* @var $this yii\web\View */

$this->title = 'Leviatan Design ERP';
?>

<style>
    .button-download-app {
        width: 100%;
        text-align: left;
    }
</style>

<div class="container-fluid site-index">
    <div class="row">
        <div class="col-12">
            <?php if (!empty(Yii::$app->authManager->getAssignments(Yii::$app->user->id))) { ?>
                <div class="row justify-content-center mt-5">
                    <div class="col-md-6 d-none d-sm-none d-md-block">
                        <div class="jumbotron bg-gradient-blue">
                            <h1 class="display-4">
                                <?php echo Yii::t('app', 'Hello') . ", " . Yii::$app->user->identity->first_name . "!"; ?>
                            </h1>
                            <hr class="my-4">
                            <p class="lead">
                                <?php echo Yii::t('app', 'Please use the menu on the left to get to the desired page.'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="col d-sm-block d-md-none">
                        <div class="jumbotron bg-transparent">
                            <h1>
                                <?php echo Yii::t('app', 'Hello') . ", " . Yii::$app->user->identity->first_name . ","; ?>
                            </h1>
                            <hr class="my-2">
                            <p class="lead font-weight-normal">
                                <span class="d-block">
                                    <?php echo Yii::t('app', 'Echipa Econfaire îți pune la dispoziție următoarele aplicații mobile:'); ?>
                                </span>
                            </p>
                            <div>
                                <a href="/apps/android/ro.erplevtech.auto.10083.apk"
                                   class="button-download-app btn btn-success btn-lg mb-2"
                                   target="_blank">
                                    <i class="fas fa-download mr-1"></i> Auto
                                </a>
                            </div>
                            <div>
                                <a href="/apps/android/ro.erplevtech.hr.10200.apk"
                                   class="button-download-app btn btn-success btn-lg mb-2"
                                   target="_blank">
                                    <i class="fas fa-download mr-1"></i> HR
                                </a>
                            </div>
                            <div>
                                <a href="/apps/android/ro.erplevtech.logistic.10018.apk"
                                   class="button-download-app btn btn-success btn-lg mb-2"
                                   target="_blank">
                                    <i class="fas fa-download mr-1"></i>
                                    <?php echo Yii::t('app', 'Meeting room reservation'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row justify-content-center mt-5">
                    <div class="col-md-6">
                        <div class="jumbotron bg-gradient-warning">
                            <h1 class="display-4">
                                <?php echo Yii::t('app', 'Hello') . " " . Yii::$app->user->identity->first_name; ?>
                            </h1>
                            <hr class="my-4">
                            <p class="lead">
                                <?php echo Yii::t('app', 'At this moment you have no role in the application, if you consider it a mistake please contact your head of department!'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
