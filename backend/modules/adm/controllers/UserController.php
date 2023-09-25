<?php

namespace backend\modules\adm\controllers;

use backend\modules\adm\models\ErpCompany;
use backend\modules\adm\models\forms\UpdatePswForm;
use backend\modules\adm\models\search\UserSearch;
use backend\modules\adm\models\User;
use Exception;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
                    'delete-employee-user' => ['POST'],
                    'activate-employee-user' => ['POST'],
                ]
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => ['delete-employee-user', 'activate-employee-user', 'change-user-psw']
            ]
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember('', 'user');
        ErpCompany::setNames();
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->setUserErpCompaniesNames();
        return $this->render('view', [
            'model' => $model
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $newUserDefaultPsw = Yii::$app->params['defaultEmployeePsw'];
        if (!isset($newUserDefaultPsw))
            throw new BadRequestHttpException(Yii::t('adm', 'Bad application configuration. Please contact the development team!'));

        ErpCompany::setNames();
        $model = new User();
        $model->created_at = date('Y-m-d H:i:s');
        $model->setPassword(Yii::$app->params['defaultEmployeePsw']);
        $model->generateAuthKey();
        $model->generateEmailVerificationToken();

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            $email = $model->email;
            $model->username = substr($email, 0, strpos($email, '@'));
            if ($model->save() && $model->setUserErpCompanies()) {
                try {
                    $auth = Yii::$app->authManager;
                    $roleObj = $auth->getRole('BasicUser');
                    $auth->assign($roleObj, $model->id);
                } catch (Exception $exc) {
                    throw new Exception($exc->getMessage(), $exc->getCode());
                }
                Yii::$app->session->setFlash('success', Yii::t('adm', "User successfully added"));
                return $this->redirect(Url::previous('user'));
            }
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('adm', $error[0]));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        ErpCompany::setNames();
        $model = $this->findModel($id);
        $model->updated_at = date('Y-m-d H:i:s');

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $email = $model->email;
            $model->username = substr($email, 0, strpos($email, '@'));
            if ($model->save() && $model->setUserErpCompanies()) {
                Yii::$app->session->setFlash('success', Yii::t('adm', "User successfully modified."));
                return $this->redirect(Url::previous('user'));
            }
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('adm', $error[0]));
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('user'));
        }
        $model->status = User::STATUS_DELETED;
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "User successfully deleted"));
            return $this->redirect(Url::previous('user'));
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('adm', $error[0]));
                return $this->redirect(['index']);
            }
        }
    }

    /**
     * Set status inactive from employee if we press Yes from bootbox from Employee page index if we want
     * to set status inactive to ERP account
     */

    public function actionDeleteEmployeeUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        if (!(bool)$post['result']) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "Employee was deleted with success"));
            return $this->redirect(Url::previous('user'));
        }

        $model = User::findByEmail($post['userEmail']);

        $model->status = User::STATUS_INACTIVE;
        $model->updated_at = date('Y-m-d H:i:s');

        if (!$model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "Employee was deleted with success but the user could not be deleted"));
            return $this->redirect(Url::previous('user'));
        }

        Yii::$app->session->setFlash('success', Yii::t('adm', "Employee and user successfully deleted"));
        return $this->redirect(Url::previous('user'));
    }

    /**
     * Activates an deleted User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('user'));
        }
        $model->status = User::STATUS_ACTIVE;
        $model->updated_at = date('Y-m-d H:i:s');

        if ($model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "User successfully activated"));
            return $this->redirect(Url::previous('user'));
        }
        if ($model->hasErrors()) {
            foreach ($model->errors as $error) {
                Yii::$app->session->setFlash('danger', Yii::t('adm', $error[0]));
                return $this->redirect(['index']);
            }
        }
    }

    /**
     * Set status active from employee if we press Yes from bootbox from Employee page index if we want to
     * activate account ERP
     */

    public function actionActivateEmployeeUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        if (!(bool)$post['result']) {
            Yii::$app->session->setFlash('success', Yii::t('adm', "Employee was activated with success"));
            return $this->redirect(Url::previous('user'));
        }

        $model = User::findByDeletedByEmail($post['userEmail']);
        if (!empty($model)) {
            $model->status = User::STATUS_ACTIVE;
            $model->updated_at = date('Y-m-d H:i:s');

            if (!$model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('adm', "Employee was activated with success but the user could not be activated"));
                return $this->redirect(Url::previous('user'));
            }

            Yii::$app->session->setFlash('success', Yii::t('adm', "Employee and user successfully activated"));
            return $this->redirect(Url::previous('user'));
        }
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdatePassword($id)
    {
        if ((int)$id !== (int)Yii::$app->user->id) {
            return $this->redirect(['/adm/user/update-password', 'id' => Yii::$app->user->id]);
        }

        $updatePswModel = new UpdatePswForm();
        if (
            Yii::$app->user->identity->psw_changed == User::PSW_CHANGED_NO
            && Yii::$app->user->identity->first_time_login == User::FIRST_TIME_LOGIN_YES
        ) {
            $updatePswModel->oldPassword = Yii::$app->params['defaultEmployeePsw'];
        }
        if ($updatePswModel->load(Yii::$app->request->post()) && $updatePswModel->validate()) {
            if ($updatePswModel->changePassword()) {
                Yii::$app->session->setFlash('success', Yii::t('adm', 'You new password was successfully set. Please login again with your new password!'));
                return $this->goHome();
            }
        }

        return $this->render('update_password', [
            'model' => $this->findModel($id),
            'updatePswModel' => $updatePswModel,
        ]);
    }

    /**
     * Allow super admins roles to change other users passwords
     * @param $id
     * @return Response
     */
    public function actionChangeUserPsw($id)
    {
        if (!Yii::$app->user->can('SuperAdmin')) {
            Yii::$app->session->setFlash('danger', Yii::t('adm', 'You are not allowed to perform this action!'));
            Yii::$app->user->logout();
            return $this->goHome();
        }

        $updatePswModel = new UpdatePswForm();
        $updatePswModel->setScenario('changeUserPsw');

        if ($updatePswModel->load(Yii::$app->request->post(), '') && $updatePswModel->validate()) {
            if ($updatePswModel->changePassword()) {
                Yii::$app->session->setFlash('success', Yii::t('adm', 'New password was successfully set.'));
            }
        }

        return $this->redirect(Url::previous('user'));
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('adm', 'The requested page does not exist.'));
    }

    /**
     * check if the employee email address is already created in user table
     * return 0 - no, 1 -yes
     */
    public function actionCheckEmployeeEmailInUserTable()
    {
        //data received from ajax
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        //check if data received from ajax is empty; if yes, setFlash
        if (empty($post)) {
            Yii::$app->session->setFlash('danger', Yii::t('adm', 'No data received.'));
            return $this->redirect(Url::previous('employee'));
        };

        //check if email is empty
        // _platform_details view - ajax - data - email
        if (empty($post['email'])) {
            $emailAlreadyCreated = 0;
        } else {
            //check if email is already created in user table
            $user = User::find()->where('email = :email', [':email' => $post['email']])->one();
            if (empty($user)) {
                $emailAlreadyCreated = 0;
            } else {
                $emailAlreadyCreated = 1;
            }
        }
        return $emailAlreadyCreated;
    }
}
