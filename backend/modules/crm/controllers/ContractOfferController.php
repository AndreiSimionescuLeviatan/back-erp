<?php

namespace backend\modules\crm\controllers;

use backend\modules\adm\models\User;
use backend\modules\crm\models\Company;
use common\components\AppHelper;
use Yii;
use backend\modules\crm\models\ContractOffer;
use backend\modules\crm\models\search\ContractOfferSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ContractOfferController implements the CRUD actions for ContractOffer model.
 */
class ContractOfferController extends Controller
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
                    'get-contracts-offers-by-company' => ['POST']
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'get-contracts-offers-by-company'
                ]
            ]
        ];
    }

    /**
     * Lists all ContractOffer models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'contract_offer');
        User::setUsers(true);
        Company::setNames();

        $searchModel = new ContractOfferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ContractOffer model.
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
     * Creates a new ContractOffer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        Company::setNames();

        $model = new ContractOffer();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                        return $this->render('create', [
                            'model' => $model,
                        ]);
                    }
                }

                Yii::$app->session->setFlash('danger', Yii::t('crm', 'Failed to create!'));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully created!'));
            return $this->redirect(Url::previous('contract_offer'));
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ContractOffer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Company::setNames();

        $model = $this->findModel($id);
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        Yii::$app->session->setFlash('danger', Yii::t('crm', $error[0]));
                        return $this->render('update', [
                            'model' => $model,
                        ]);
                    }
                }

                Yii::$app->session->setFlash('danger', Yii::t('crm', 'Failed to update!'));
                return $this->render('update', [
                    'model' => $model,
                ]);
            }

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully updated!'));
            return $this->redirect(Url::previous('contract_offer'));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ContractOffer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
            AppHelper::chainDelete($model);

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully deleted!'));
            return $this->redirect(Url::previous('contract_offer'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('contract_offer'));
        }
    }

    /**
     * Activates an existing Contract Offer model.
     * If activation is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        try {
            $model = $this->findModel($id);
            AppHelper::chainActivate($model);

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully activated!'));
            return $this->redirect(Url::previous('contract_offer'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('contract_offer'));
        }
    }

    /**
     * @added_by Calin B.
     * @since 28.06.2022
     */
    public function actionGetContractsOffersByCompany()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = '';
        $postParams = Yii::$app->request->post('depdrop_all_params');

        if (!empty($postParams['company_id'])) {
            $out = ContractOffer::find()->select("id, name AS name")
                ->where('`deleted` = 0 AND `company_id` = :companyID', [':companyID' => $postParams['company_id']])
                ->asArray()->all();
        }

        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Finds the ContractOffer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ContractOffer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ContractOffer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('crm', 'The requested page does not exist.'));
    }
}
