<?php

namespace backend\modules\crm\controllers;

use backend\modules\adm\models\User;
use backend\modules\crm\models\Brand;
use backend\modules\crm\models\search\BrandModelSearch;
use Yii;
use backend\modules\crm\models\BrandModel;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * BrandModelController implements the CRUD actions for BrandModel model.
 */
class BrandModelController extends Controller
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
                ],
            ],
        ];
    }

    /**
     * Lists all BrandModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'brand_model');
        User::setUsers(true);
        Brand::setNamesBrandsModel();
        BrandModel::setAddedBy();
        BrandModel::setUpdatedBy();
        $searchModel = new BrandModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BrandModel model.
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
     * Creates a new BrandModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
        $existingModel = null;

        Brand::setNamesBrandsModel();
        $model = new BrandModel();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {
            if (!Yii::$app->request->isAjax) {
                Yii::$app->session->setFlash('success', Yii::t('crm', "Model successfully added."));
                return $this->redirect(Url::previous('brand_model'));
            }
            return '<option value="' . $model->id . '">' . $model->name . '</option>';
        }
        if ($model->hasErrors()) {
            $postModel = Yii::$app->request->post('BrandModel');
            if ($postModel !== null) {
                $existingModel = BrandModel::find()->where('`deleted` != 0 AND name = :name', [
                    ':name' => $postModel['name']
                ])->one();
            }
            foreach ($model->errors as $error) {
                if ($existingModel == null) {
                    Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                } elseif ($existingModel->name == $postModel['name']) {
                    Yii::$app->session->setFlash('danger', Yii::t('crm', "The model '{model}' belongs to a deleted model.", [
                        'model' => $existingModel->name
                    ]));
                }
            }
            return $this->render('create', [
                'model' => $model,
                'existingModel' => $existingModel
            ]);
        }
        return $this->render('create', [
            'model' => $model,
            'existingModel' => $existingModel
        ]);
    }

    /**
     * Updates an existing BrandModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Brand::setNamesBrandsModel();
        $existingModel = null;

        try {
            $model = $this->findModel($id);
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(['index']);
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('crm', "Model successfully updated."));
            return $this->redirect(Url::previous('brand_model'));
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                return $this->render('update', [
                    'model' => $model,
                    'existingModel' => $existingModel
                ]);
            }
        }
        return $this->render('update', [
            'model' => $model,
            'existingModel' => $existingModel
        ]);
    }

    /**
     * Deletes an existing BrandModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            $model->trigger(BrandModel::EVENT_CHAIN_DELETE_MODEL);
            Yii::$app->session->setFlash('success', Yii::t('crm', "Model successfully deleted."));
            return $this->redirect(Url::previous('brand_model'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('brand_model'));
        }
    }

    /**
     * Activates an activate Brand model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        try {
            $model = $this->findModel($id);
            $model->trigger(BrandModel::EVENT_CHAIN_ACTIVATE_MODEL);
            Yii::$app->session->setFlash('success', Yii::t('crm', "Model successfully activated."));
            return $this->redirect(Url::previous('brand_model'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('brand_model'));
        }
    }

    /**
     * Finds the BrandModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BrandModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BrandModel::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('crm', 'The requested page does not exist.'));
    }
}