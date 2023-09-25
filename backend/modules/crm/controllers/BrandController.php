<?php

namespace backend\modules\crm\controllers;

use backend\modules\adm\models\Domain;
use backend\modules\adm\models\Entity;
use backend\modules\adm\models\Subdomain;
use backend\modules\adm\models\User;
use backend\modules\crm\models\EntityDomain;
use Yii;
use backend\modules\crm\models\Brand;
use backend\modules\crm\models\search\BrandSearch;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends Controller
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
     * Lists all Brand models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'brand');
        User::setUsers(true);
        Brand::setAddedBy();
        Brand::setUpdatedBy();
        $searchModel = new BrandSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Brand model.
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
     * Creates a new Brand model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\db\StaleObjectException
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
        $existingBrand = null;

        $model = new Brand();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;

        $entityDomainModel = new EntityDomain();
        $entityDomainModel->added = date('Y-m-d H:i:s');
        $entityDomainModel->added_by = Yii::$app->user->id;
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {//if model will save, save also the domain entity
            $entityDomainModel->item_id = $model->id;
            if ($entityDomainModel->load($post) && $entityDomainModel->save()) {
                if (!Yii::$app->request->isAjax) {
                    Yii::$app->session->setFlash('success', Yii::t('crm', "Brand successfully added."));
                    return $this->redirect(Url::previous('brand'));
                }
                return '<option value="' . $model->id . '">' . $model->name . '</option>';
            }
            //if we are here it means that the model(Brand) was saved but the entity domain has errors, check for errors
            //and delete the saved model
            if ($entityDomainModel->hasErrors()) {
                $model->delete();
                foreach ($entityDomainModel->errors as $error) {
                    Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                }
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('crm', "The model is invalid but no error have been thrown. Please contact an administrator!"));
            }
            $model->delete();

            return $this->render('create', [
                'model' => $entityDomainModel,
                'entityDomainModel' => $entityDomainModel,
                'existingBrand' => $existingBrand
            ]);
        }

        if ($model->hasErrors()) {
            $postBrand = Yii::$app->request->post('Brand');
            if ($postBrand !== null) {
                $existingBrand = Brand::find()->where('`deleted` != 0 AND name = :name', [
                    ':name' => $postBrand['name']
                ])->one();
            }
            foreach ($model->errors as $error) {
                if ($existingBrand == null) {
                    Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                } elseif ($existingBrand->name == $postBrand['name']) {
                    Yii::$app->session->setFlash('danger', Yii::t('crm', "The brand '{Brand}' belongs to a deleted brand.", [
                        'Brand' => $existingBrand->name
                    ]));
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'entityDomainModel' => $entityDomainModel,
            'existingBrand' => $existingBrand
        ]);
    }

    /**
     * Updates an existing Brand model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $existingBrand = null;
        Domain::setNames();
        Entity::setNames();
        Subdomain::setNames();

        try {
            $model = $this->findModel($id);
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            $entityDomainModel = EntityDomain::findEntityDomainByItem($model->id, true);
        } catch (NotFoundHttpException|BadRequestHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save() &&
            $entityDomainModel->load(Yii::$app->request->post()) && $entityDomainModel->save()) {
            Yii::$app->session->setFlash('success', Yii::t('crm', "Brand successfully updated."));
            return $this->redirect(Url::previous('brand'));
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
            }
        }
        if ($entityDomainModel->hasErrors()) {
            foreach ($entityDomainModel->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'entityDomainModel' => $entityDomainModel,
            'existingBrand' => $existingBrand
        ]);
    }

    /**
     * Deletes an existing Brand model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            $model->trigger(Brand::EVENT_CHAIN_DELETE_BRAND);
            Yii::$app->session->setFlash('success', Yii::t('crm', "Brand successfully deleted."));
            return $this->redirect(Url::previous('brand'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('brand'));
        }
    }


    /**
     * Activates an deleted Brand model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        try {
            $model = $this->findModel($id);
            $model->trigger(Brand::EVENT_CHAIN_ACTIVATE_BRAND);
            Yii::$app->session->setFlash('success', Yii::t('crm', "Brand successfully activated."));
            return $this->redirect(Url::previous('brand'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('brand'));
        }
    }

    /**
     * Finds the Brand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brand the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('crm', 'The requested page does not exist.'));
    }
}
