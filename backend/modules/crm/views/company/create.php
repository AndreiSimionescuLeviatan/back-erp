<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $companyModel backend\modules\crm\models\Company */
/* @var $entityDomainModel \backend\modules\crm\models\EntityDomain */
/* @var $ibanCompanyModel \backend\modules\crm\models\IbanCompany */
/* @var $tva */
/* @var $isNewRecord */

$this->title = Yii::t('crm', 'Create Company');
$this->params['breadcrumbs'][] = ['label' => Yii::t('crm', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_form', [
        'companyModel' => $companyModel,
        'entityDomainModel' => $entityDomainModel,
        'ibanCompanyModel' => $ibanCompanyModel,
        'tva' => $tva,
        'isNewRecord' => true
    ]) ?>

</div>
