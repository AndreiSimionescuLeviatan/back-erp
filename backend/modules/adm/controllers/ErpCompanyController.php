<?php

namespace backend\modules\adm\controllers;

use backend\modules\adm\models\User;
use backend\modules\crm\models\Company;
use backend\modules\hr\models\Employee;
use Yii;
use backend\modules\adm\models\ErpCompany;
use backend\modules\adm\models\search\ErpCompanySearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ErpCompanyController implements the CRUD actions for ErpCompany model.
 */
class ErpCompanyController extends Controller
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
                    'delete' => ['POST']
                ],
            ],
        ];
    }

    /**
     * Lists all ErpCompany models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'erp_company');
        User::setUsers(true);
        Employee::setFullNames(true);
        $searchModel = new ErpCompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ErpCompany model.
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
     * Creates a new ErpCompany model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ErpCompany();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "Erp company successfully added"));
            return $this->redirect(Url::previous('erp_company'));
        }

        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('adm', $error[0]));
                return $this->render('create', [
                    'model' => $model
                ]);
            }
        }

        Company::setNames();
        Employee::setFullNames(true);
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ErpCompany model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(Url::previous('erp_company'));
        }
        Company::setNames();
        Employee::setFullNames(true);
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ErpCompany model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('adm', "Erp company successfully deleted"));
        return $this->redirect(Url::previous('erp_company'));
    }

    /**
     * Finds the ErpCompany model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ErpCompany the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ErpCompany::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('adm', 'The requested page does not exist.'));
    }
}
