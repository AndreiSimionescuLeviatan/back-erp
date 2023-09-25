<?php

namespace backend\modules\adm\controllers;

use Yii;
use backend\modules\adm\models\Subdomain;
use backend\modules\adm\models\search\SubdomainSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * SubdomainController implements the CRUD actions for Subdomain model.
 */
class SubdomainController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'get-subdomains' => ['POST'],
                    'get-subdomains-operator-casco' => ['POST'],
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => ['get-subdomains','get-subdomains-operator-casco']
            ]
        ];
    }

    /**
     * Lists all Subdomain models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SubdomainSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Subdomain model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Subdomain model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Subdomain();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Subdomain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Subdomain model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Subdomain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Subdomain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Subdomain::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('adm', 'The requested page does not exist.'));
    }

    /**
     * @return array|string[]
     */
    public function actionGetSubdomains()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post('depdrop_all_params');
        if (!empty($post) && !empty($post['entitydomain-domain_id']) && !empty($post['entitydomain-entity_id'])) {
            $domainId = $post['entitydomain-domain_id'];
            $entityId = $post['entitydomain-entity_id'];
            $out = Subdomain::find()->where('deleted = 0 AND domain_id = :domain_id AND entity_id = :entity_id',
                [':domain_id' => $domainId, ':entity_id' => $entityId])->asArray()->all();
            return ['output' => $out, 'selected' => ''];
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * @return array|string[]
     * @added 2022-07-11
     * @added_by Alex G.
     */
    public function actionGetSubdomainsOperatorCasco()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post('depdrop_all_params');
        if (!empty($post) && !empty($post['operator-domain-id']) && !empty($post['operator-entity-id'])) {
            $domainId = $post['operator-domain-id'];
            $entityId = $post['operator-entity-id'];
            $out = Subdomain::find()->where('deleted = 0 AND domain_id = :domain_id AND entity_id = :entity_id',
                [':domain_id' => $domainId, ':entity_id' => $entityId])->asArray()->all();
            return ['output' => $out, 'selected' => ''];
        }
        return ['output' => '', 'selected' => ''];
    }

}
