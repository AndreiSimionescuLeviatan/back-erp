<?php

namespace backend\modules\location\controllers;

use backend\modules\adm\models\User;
use backend\modules\location\models\Country;
use backend\modules\location\models\State;
use common\components\AppHelper;
use Yii;
use backend\modules\location\models\City;
use backend\modules\location\models\search\CitySearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CityController implements the CRUD actions for City model.
 */
class CityController extends Controller
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
                    'activate' => ['POST'],
                    'get-cities' => ['POST']
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'get-cities'
                ]
            ]
        ];
    }

    /**
     * Lists all City models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'city');
        User::setUsers(true);
        AppHelper::setNames('country', get_class(new Country()), 'name');
        AppHelper::setNames('state', get_class(new State()), 'name');
        City::setUserAdded();
        City::setUserUpdated();

        $searchModel = new CitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single City model.
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
     * Deletes an existing City model.
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

            Yii::$app->session->setFlash('success', Yii::t('location', 'Successfully deleted!'));
            return $this->redirect(Url::previous('city'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('city'));
        }
    }

    /**
     * Activates an existing City model.
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

            Yii::$app->session->setFlash('success', Yii::t('location', 'Successfully activated!'));
            return $this->redirect(Url::previous('city'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('city'));
        }
    }

    /**
     * Returns the cities base on state code
     *
     * @return array|string[]
     * added: 03.11.2021
     * added by: Diana Basoc
     * updated: 25.05.2022
     * updated by: Andrei I.
     */
    public function actionGetCities()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post('depdrop_all_params');

        if (!empty($post) && !empty($post['company-country_id']) && !empty($post['company-state_id'])) {
            $countryId = $post['company-country_id'];
            $stateId = $post['company-state_id'];
            $out = City::find()->where('deleted = 0 AND country_id = :country_id AND state_id = :state_id',
                [':country_id' => $countryId, ':state_id' => $stateId])->asArray()->all();
            return ['output' => $out, 'selected' => ''];
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * Finds the City model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return City the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = City::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('location', 'The requested page does not exist.'));
    }
}
