<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Employee;
use api\modules\v2\models\EmployeeCompany;
use api\modules\v1\models\ShiftBreakInterval;
use api\modules\v1\models\Shift;
use api\models\WorkLocation;
use common\components\HttpStatus;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

class ShiftController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Employee';

    public function actionMonthlyScheduleDetails()
    {
        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
        if (empty($employeeId)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $this->return['monthly_schedule_details'] = Shift::getMonthlyScheduleDetails($employeeId);

        $this->return['status'] = HttpStatus::OK;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-hr', 'Successfully sent schedule details');
        return $this->return;
    }

    /**
     * @return array|mixed|void
     */
    public function actionShiftDetails()
    {
        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);
        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $post = Yii::$app->request->post();
        if (empty($post['shift_details'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No shift details received');
            return $this->return;
        }

        $shiftDetails = $post['shift_details'];
        $locationScheduleDetails = null;
        if (!empty($shiftDetails['device_location'])) {
            $locationScheduleDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $shiftDetails['device_location']['latitude'],
                'longitude' => $shiftDetails['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }
        $isInLocation = 0;
        $currentLocationName = null;

        if (!empty($locationScheduleDetails) && !empty($locationScheduleDetails['name'])) {
            $isInLocation = 1;
            $currentLocationName = $locationScheduleDetails['name'];
        }

        if (empty($shiftDetails['id'])) {
            $starShiftDetails = [
                'company_id' => $employee->employeeMainCompany->company_id,
                'employee_id' => $employee->id,
                'start_initial' => $shiftDetails['start'],
                'in_location_at_start_initial' => $isInLocation
            ];
            $transaction = Yii::$app->ecf_hr_db->beginTransaction();
            try {
                $shift = $this->saveShift($starShiftDetails);
                $transaction->commit();

                $this->return['status'] = HttpStatus::OK;
                $this->return['location_name'] = $currentLocationName;
                $this->return['message'] = Yii::t('api-hr', 'Successfully saved shift');
                $this->return['id'] = $shift->id;
                return $this->return;
            } catch (HttpException $exc) {
                $transaction->rollBack();
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        } else {
            $stopShiftDetails = [
                'stop_initial' => $shiftDetails['stop'],
                'in_location_at_stop_initial' => $isInLocation
            ];
            $shiftModel = Shift::find()->where(['id' => $shiftDetails['id']])->one();
            if (empty($shiftModel)) {
                $this->return['status'] = HttpStatus::NOT_FOUND;
                $this->return['message'] = Yii::t('api-hr', 'Shift not found');
                return $this->return;
            }

            try {
                $this->updateShift($shiftModel, $stopShiftDetails);
                $this->return['status'] = HttpStatus::OK;
                $this->return['location_name'] = $currentLocationName;
                $this->return['message'] = Yii::t('api-hr', 'Successfully updated shift');
                return $this->return;
            } catch (HttpException $exc) {
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        }
    }

    /**
     * @return array|mixed|void
     */
    public function actionBreakDetails()
    {
        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);
        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $post = Yii::$app->request->post();
        if (empty($post['break_details'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break details received');
            return $this->return;
        }
        $breakDetails = $post['break_details'];

        $locationBreakDetails = null;
        if (!empty($breakDetails['device_location'])) {
            $locationBreakDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $breakDetails['device_location']['latitude'],
                'longitude' => $breakDetails['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }
        $isInLocation = 0;
        $currentLocationName = null;

        if (!empty($locationBreakDetails) && !empty($locationBreakDetails['name'])) {
            $isInLocation = 1;
            $currentLocationName = $locationBreakDetails['name'];
        }

        if (empty($breakDetails['id'])) {
            $startBreakDetails = [
                'company_id' => $employee->employeeMainCompany->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $breakDetails['shift_id'],
                'start_initial' => $breakDetails['start'],
                'in_location_at_start_initial' => $isInLocation,
            ];

            try {
                $break = $this->saveShiftBreakInterval($startBreakDetails);
                $this->return['status'] = HttpStatus::OK;
                $this->return['location_name'] = $currentLocationName;
                $this->return['message'] = Yii::t('api-hr', 'Successfully saved break');
                $this->return['id'] = $break->id;
                return $this->return;
            } catch (HttpException $exc) {
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        } else {
            $stopBreakDetails = [
                'stop_initial' => $breakDetails['stop'],
                'in_location_at_stop_initial' => $isInLocation
            ];
            $breakModel = ShiftBreakInterval::find()->where(['id' => $breakDetails['id']])->one();
            if (empty($breakModel)) {
                $this->return['status'] = HttpStatus::NOT_FOUND;
                $this->return['message'] = Yii::t('api-hr', 'Break not found');
                return $this->return;
            }

            try {
                $this->updateShiftBreakInterval($breakModel, $stopBreakDetails);
                $this->return['status'] = HttpStatus::OK;
                $this->return['location_name'] = $currentLocationName;
                $this->return['message'] = Yii::t('api-hr', 'Successfully updated break');
                return $this->return;
            } catch (HttpException $exc) {
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function actionShiftsHistory()
    {
        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
        if (empty($employeeId)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        try {
            $shifts = Shift::getShiftsHistory($employeeId);
            $this->return['shifts'] = $shifts;
            $this->return['status'] = HttpStatus::OK;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Successfully sent shifts history');
            return $this->return;
        } catch (NotFoundHttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * @return array|mixed
     */
    public function actionSaveShift()
    {
        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);
        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $post = Yii::$app->request->post();
        if (empty($post['answer'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No answer received');
            return $this->return;
        }

        $answer = json_decode(base64_decode($post['answer']), true);
        $shifts = $answer['shifts'];
        if (empty($shifts)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No shift received');
            return $this->return;
        }

        foreach ($shifts as $shiftId => $shift) {
            $shiftDetails = $shift['shift_details'];
            if (empty($shiftDetails)) {
                $this->return['status'] = HttpStatus::NOT_FOUND;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-hr', 'No shift details received');
                return $this->return;
            }

            $saveShift = [
                'start_modified' => $shiftDetails['start_modified'] === $shiftDetails['start'] ? null : $shiftDetails['start_modified'],
                'stop_modified' => $shiftDetails['stop_modified'] === $shiftDetails['stop'] ? null : $shiftDetails['stop_modified'],
                'observations' => $shiftDetails['observations']
            ];

            $modelShift = Shift::find()->where(['id' => $shiftId])->one();
            if (empty($modelShift)) {
                $this->return['status'] = HttpStatus::NOT_FOUND;
                $this->return['message'] = Yii::t('api-hr', 'Shift not found');
                return $this->return;
            }
            try {
                $this->updateShift($modelShift, $saveShift);
            } catch (HttpException $exc) {
                $this->return['status'] = $exc->statusCode;
                Yii::$app->response->statusCode = $exc->statusCode;
                $this->return['message'] = $exc->getMessage();
                return $this->return;
            }

            $breakInterval = $shift['break_intervals'];
            if (!empty($breakInterval['initial'])) {
                foreach ($breakInterval['initial'] as $breakId => $break) {
                    $saveBreak = [
                        'start_modified' => $break['start_modified'] === $break['start'] ? null : $break['start_modified'],
                        'stop_modified' => $break['stop_modified'] === $break['stop'] ? null : $break['stop_modified'],
                        'observations' => $break['observations'],
                        'deleted' => $break['deleted']
                    ];

                    $breakModel = ShiftBreakInterval::find()->where(['id' => $breakId])->one();
                    if (empty($breakModel)) {
                        $this->return['status'] = HttpStatus::NOT_FOUND;
                        $this->return['message'] = Yii::t('api-hr', 'Break not found');
                        return $this->return;
                    }
                    try {
                        $this->updateShiftBreakInterval($breakModel, $saveBreak);
                    } catch (HttpException $exc) {
                        $this->return['status'] = $exc->statusCode;
                        Yii::$app->response->statusCode = $exc->statusCode;
                        $this->return['message'] = $exc->getMessage();
                        return $this->return;
                    }
                }
            }

            if (!empty($breakInterval['new'])) {
                foreach ($breakInterval['new'] as $newBreak) {
                    $saveNewBreak = [
                        'company_id' => $employee->company_id,
                        'employee_id' => $employee->id,
                        'shift_id' => $shiftId,
                        'start_modified' => $newBreak['start_modified'],
                        'stop_modified' => $newBreak['stop_modified'],
                        'observations' => $newBreak['observations'],
                        'deleted' => $newBreak['deleted']
                    ];
                    try {
                        $this->saveShiftBreakInterval($saveNewBreak);
                    } catch (HttpException $exc) {
                        $this->return['status'] = $exc->statusCode;
                        Yii::$app->response->statusCode = $exc->statusCode;
                        $this->return['message'] = $exc->getMessage();
                        return $this->return;
                    }

                }
            }
        }
        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-hr', 'Successfully saved shift');
        return $this->return;
    }

    /**
     * @return array|mixed
     */
    public function actionDeleteShift()
    {
        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);
        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $post = Yii::$app->request->post();
        if (empty($post['answer'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No answer received');
            return $this->return;
        }

        $answer = json_decode(base64_decode($post['answer']), true);
        $shiftId = $answer['shift_details']['id'];
        if (empty($shiftId)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No shift id received');
            return $this->return;
        }

        $deletedShift = [
            'deleted' => '1'
        ];

        $shiftModel = Shift::find()->where(['id' => $shiftId])->one();
        if (empty($shiftModel)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            $this->return['message'] = Yii::t('api-hr', 'Shift not found');
            return $this->return;
        }
        if ($shiftModel->validated === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already validated');
            return $this->return;
        }
        if ($shiftModel->deleted === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already deleted');
            return $this->return;
        }
        try {
            $this->updateShift($shiftModel, $deletedShift);
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }

        $breakModel = ShiftBreakInterval::find()->where(['shift_id' => $shiftId])->all();
        if (!empty($breakModel)) {
            foreach ($breakModel as $break) {
                try {
                    $this->updateShiftBreakInterval($break, $deletedShift);
                } catch (HttpException $exc) {
                    $this->return['status'] = $exc->statusCode;
                    Yii::$app->response->statusCode = $exc->statusCode;
                    $this->return['message'] = $exc->getMessage();
                    return $this->return;
                }
            }
        }

        $this->return['status'] = HttpStatus::OK;
        $this->return['message'] = Yii::t('api-hr', 'Successfully deleted shift');
        return $this->return;
    }

    public function actionValidateShift()
    {
        $employee = Employee::findOne(['user_id' => Yii::$app->user->id]);
        if ($employee === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        $post = Yii::$app->request->post();
        if (empty($post['answer'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No answer received');
            return $this->return;
        }

        $answer = json_decode(base64_decode($post['answer']), true);
        $shiftId = $answer['shift_details']['id'];
        if (empty($shiftId)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No shift id received');
            return $this->return;
        }

        $validatedShift = [
            'validated' => '1'
        ];

        $shiftModel = Shift::find()->where(['id' => $shiftId])->one();
        if (empty($shiftModel)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            $this->return['message'] = Yii::t('api-hr', 'Shift not found');
            return $this->return;
        }
        if ($shiftModel->validated === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already validated');
            return $this->return;
        }
        if ($shiftModel->deleted === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already deleted');
            return $this->return;
        }
        try {
            $this->updateShift($shiftModel, $validatedShift);
            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully validated shift');
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    public function actionOngoingShift()
    {
        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
        if (empty($employeeId)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }

        try {
            $shifts = Shift::getOngoingShift($employeeId);
            if (empty($shifts)) {
                $this->return['shifts'] = $shifts;
                $this->return['status'] = HttpStatus::OK;
                Yii::$app->response->statusCode = $this->return['status'];
                $this->return['message'] = Yii::t('api-hr', 'Ongoing shift not exist');
                return $this->return;
            }
            $this->return['shifts'] = $shifts;
            $this->return['status'] = HttpStatus::OK;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Successfully sent the ongoing shift');
            return $this->return;
        } catch (NotFoundHttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * @param $starShiftDetails
     * @return Shift
     * @throws HttpException
     */
    public function saveShift($starShiftDetails)
    {
        $model = new Shift();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if ($model->load($starShiftDetails, '') && !$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                }
            }
            throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to save shift. Please contact an administrator!'));
        }
        return $model;
    }

    /**
     * @param $model
     * @param $stopBreakDetails
     * @return void
     * @throws HttpException
     * update shift
     */
    public function updateShift($model, $stopBreakDetails)
    {
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;
        if ($model->load($stopBreakDetails, '') && !$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                }
            }
            throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to update shift. Please contact an administrator!'));
        }
    }

    /**
     * @param $startBreakDetails
     * @return ShiftBreakInterval
     * @throws HttpException
     * create shift break interval
     */
    public function saveShiftBreakInterval($startBreakDetails)
    {
        $model = new ShiftBreakInterval();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if ($model->load($startBreakDetails, '') && !$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                }
            }
            throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to save shift break interval. Please contact an administrator!'));
        }
        return $model;
    }

    /**
     * @param $model
     * @param $stopBreakDetails
     * @return void
     * @throws HttpException
     * create shift break interval
     */
    public function updateShiftBreakInterval($model, $stopBreakDetails)
    {
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;
        if ($model->load($stopBreakDetails, '') && !$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                }
            }
            throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to update shift. Please contact an administrator!'));
        }
    }
}