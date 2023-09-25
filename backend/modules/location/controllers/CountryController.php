<?php

namespace backend\modules\location\controllers;

use backend\modules\adm\models\User;
use common\components\AppHelper;
use Yii;
use backend\modules\location\models\Country;
use backend\modules\location\models\search\CountrySearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class CountryController extends Controller
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
                    'get-countries' => ['POST']
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'get-countries'
                ]
            ]
        ];
    }

    /**
     * Lists all Country models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'country');
        User::setUsers(true);
        Country::setUserAdded();
        Country::setUserUpdated();

        $searchModel = new CountrySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Country model.
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
     * Deletes an existing Country model.
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
            return $this->redirect(Url::previous('country'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('country'));
        }
    }

    /**
     * Activates an existing Country model.
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
            return $this->redirect(Url::previous('country'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('country'));
        }
    }

    /**
     * Returns the countries base on country code
     *
     * @return array|string[]
     * @author Andrei I.
     * @since 25/05/2022
     */
    public function actionGetCountries()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $out = Country::find()->select("id, name AS name")
            ->where('`deleted` = 0')->asArray()->all();

        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Finds the Country model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Country the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Country::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('location', 'The requested page does not exist.'));
    }
}
