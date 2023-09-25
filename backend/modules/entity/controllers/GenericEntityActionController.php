<?php

namespace backend\modules\entity\controllers;

use backend\modules\entity\models\Domain;
use backend\modules\entity\models\Entity;
use backend\modules\entity\models\EntityAction;
use backend\modules\entity\models\EntityActionCategory;
use backend\modules\entity\models\EntityActiveRecord;
use backend\modules\entity\models\GenericEntityAction;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class GenericEntityActionController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'get-entities' => ['POST'],
                    'get-entities-value-replace' => ['POST'],
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'get-entities',
                    'get-entities-value-replace',
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $domainId = $entityId = $categoryIds = $specialityId = null;
        $post = Yii::$app->request->post();

        if (!empty($post['domain_id'])) {
            $domainId = $post['domain_id'];
        }
        if (!empty($post['entity_id'])) {
            $entityId = $post['entity_id'];
        }
        if (!empty($post['speciality_id'])) {
            $specialityId = $post['speciality_id'];
        }
        if (!empty($post['selection'])) {
            $categoryIds = implode(",", $post['selection']);
        }

        if (!empty($post) && EntityAction::validatePostReplace($post)) {
            try {
                $model = new GenericEntityAction();
                $model->setEntityByID($post['entity_id']);
                $model->setNewEntityID($post['new_entity']);
                $model->setOldEntityIDs($post['old_entities']);
                $model->setCategoryIDs($categoryIds);
                $model->executeEntityAction();
                Yii::$app->session->setFlash('success', Yii::t('entity', 'Replaced with success') . '!');
            } catch (\Exception|\Throwable $e) {
                Yii::$app->session->setFlash('danger', Yii::t('entity', $e->getMessage()));
            }
        }

        Domain::setNames();

        return $this->render('index', [
            'dataProvider' => new ArrayDataProvider(),
            'domainId' => $domainId,
            'entityId' => $entityId,
            'categoryIds' => $categoryIds,
            'specialities' => Entity::getValuesByEntityId(Entity::DESIGN_SPECIALITY_ID),
            'specialityId' => $specialityId,
        ]);
    }

    public function actionGetEntities()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $response = [
            'output' => [],
            'selected' => ''
        ];

        if (
            empty($_POST['depdrop_parents'])
            || empty($_POST['depdrop_parents'][0])
        ) {
            return $response;
        }

        $condition = " AND `deleted` = 0 AND IFNULL(`find_replace`, 0) = 1 AND `domain_id` = {$_POST['depdrop_parents'][0]}";
        $response['output'] = Entity::getEntitiesDescription($condition);

        return $response;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function actionGetEntitiesValueReplace()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Entity::setEntities();

        $response = [
            'output' => [],
            'selected' => ''
        ];

        if (
            empty($_POST['depdrop_parents'])
            || empty($_POST['depdrop_parents'][0])
        ) {
            return $response;
        }

        $entity = Entity::getEntityByID($_POST['depdrop_parents'][0]);
        if (!$entity) {
            return $response;
        }

        $column = $entity['display_column'] ?? '`code`';
        $table = "`ecf_{$entity['name_domain']}`.`{$entity['name']}`";
        $condition = "`deleted` = 0";
        if (!empty($_POST['depdrop_all_params'])) {
            $condition .= GenericEntityAction::getAdditionalConditionsByEntityId($entity['id'], $_POST['depdrop_all_params']);
        }

        $sql = "SELECT `id`, {$column} as `name` FROM {$table} WHERE {$condition} ORDER BY {$column}";
        $response['output'] = EntityActiveRecord::queryAll($sql);

        return $response;
    }

    public function actionGetLocationReplace()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        if (empty($post['entityId'])) {
            return false;
        }

        $condition = "WHERE eac.`entity_id`={$post['entityId']}";
        $dataProvider = EntityActionCategory::getDataProvider($condition);

        if (count($dataProvider->allModels) == 0) {
            return false;
        }

        return $this->renderAjax('_grid_entity_description.php', [
            'dataProvider' => $dataProvider,
        ]);
    }
}