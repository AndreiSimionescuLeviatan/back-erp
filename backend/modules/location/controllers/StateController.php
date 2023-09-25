<?php

namespace backend\modules\location\controllers;

use backend\modules\adm\models\User;
use backend\modules\location\models\Country;
use common\components\AppHelper;
use Yii;
use backend\modules\location\models\State;
use backend\modules\location\models\search\StateSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * StateController implements the CRUD actions for State model.
 */
class StateController extends Controller
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
                    'get-states' => ['POST']
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'get-states'
                ]
            ]
        ];
    }

    /**
     * Lists all State models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'state');
        User::setUsers(true);
        AppHelper::setNames('country', get_class(new Country()), 'name');
        State::setUserAdded();
        State::setUserUpdated();

        $searchModel = new StateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single State model.
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
     * Deletes an existing State model.
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
            return $this->redirect(Url::previous('state'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('state'));
        }
    }

    /**
     * Activates an existing State model.
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
            return $this->redirect(Url::previous('state'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('state'));
        }
    }

    /**
     * Returns the states base on country code
     *
     * @return array|string[]
     * @author Andrei I.
     * @since 25/05/2022
     */
    public function actionGetStates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post('depdrop_all_params');

        if (!empty($post) && !empty($post['company-country_id'])) {
            $countryId = $post['company-country_id'];
            $out = State::find()->where('deleted = 0 AND country_id = :country_id',
                [':country_id' => $countryId])
                ->orderBy('name')->asArray()->all();
            return ['output' => $out, 'selected' => ''];
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * Finds the State model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return State the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = State::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('location', 'The requested page does not exist.'));
    }
}
