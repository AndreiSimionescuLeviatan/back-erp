<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Employee;
use backend\modules\hr\models\AppFeedback;
use common\components\HttpStatus;
use Yii;

class GeneralHrComponentsController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Employee';

    public function actionSaveAppFeedback()
    {
        $post = Yii::$app->request->post();

        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);

        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        if (empty($post['feedback'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No feedback was received');
            return $this->return;
        }
        $feedback = json_decode(base64_decode($post['feedback']));

        $appFeedback = [
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'feedback' => $feedback
        ];

        AppFeedback::createByAttributes($appFeedback);

        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-hr', 'Successfully saved feedback');

        return $this->return;
    }
}