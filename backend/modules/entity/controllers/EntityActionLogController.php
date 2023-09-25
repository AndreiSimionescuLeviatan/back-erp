<?php

namespace backend\modules\entity\controllers;

use backend\modules\entity\models\EntityAction;
use backend\modules\entity\models\search\EntityActionLogSearch;
use Yii;
use yii\web\Controller;

class EntityActionLogController extends Controller
{
    public function actionIndex()
    {
        EntityAction::setFilterOptions('added_by');
        $searchModel = new EntityActionLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $conditions = [['deleted' => 0]];
        if (!empty($searchModel->domain_id)) {
            $conditions[] = ['domain_id' => $searchModel->domain_id];
        }

        EntityAction::setDomainNames();
        EntityAction::setEntityNames($conditions);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
}