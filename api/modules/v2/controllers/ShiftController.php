<?php

namespace api\modules\v2\controllers;

use api\models\WorkLocation;
use api\modules\v2\models\Shift;
use api\modules\v2\models\ShiftBreakInterval;
use common\components\HttpStatus;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;

class ShiftController extends \api\modules\v1\controllers\ShiftController
{
    public $modelClass = 'api\modules\v2\models\Shift';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
        return $actions;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) return false;

        if (empty(Yii::$app->user->identity->employee)) {
            throw new UnauthorizedHttpException(Yii::t('api-hr', 'Wrong/Empty employee details'));
        }
        return true;
    }

    public function actionView($id)
    {
        return Shift::find()
            ->where([
                'id' => $id,
            ])
            ->with([
                'shiftBreakIntervals' => function ($query) {
                    $query->where('deleted = 0');
                    $query->orderBy('added ASC');
                },
            ])
            ->asArray()
            ->one();
    }

    /**
     * @return array|mixed
     */
    public function actionHistory()
    {
        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $lastTenShiftIntervals = Shift::find()
            ->where('employee_id = :employee_id', [':employee_id' => $employee->id])
            ->andWhere('company_id = :company_id', [':company_id' => $employee->employeeMainCompany->company_id])
            ->andWhere('deleted = 0')
            ->with([
                'shiftBreakIntervals' => function ($query) {
                    $query->where('deleted = 0');
                    $query->orderBy('added ASC');
                },
            ])
            ->orderBy('id DESC')
            ->limit(30)
            ->asArray()
            ->all();

        $this->return['shifts'] = $lastTenShiftIntervals;
        return $this->return;
    }

    public function actionStartShift()
    {
        $post = Yii::$app->request->post();
        if (empty($post['shift_data'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break details received');
            return $this->return;
        }
        $shiftData = $post['shift_data'];

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        if ($employee->openshift !== null) {
            $this->return['status'] = HttpStatus::CONFLICT;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'You already have a shift started');
            return $this->return;
        }

        $locationDetails = null;
        if (!empty($shiftData['device_location'])) {//@todo de verificat daca nu este un string gol lat si lng
            $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $shiftData['device_location']['latitude'],
                'longitude' => $shiftData['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }
        $isInLocation = 0;
        $currentLocationName = null;

        if (!empty($locationDetails) && !empty($locationDetails['name'])) {
            $isInLocation = 1;
            $currentLocationName = $locationDetails['name'];
        }

        $newShiftData = [
            'company_id' => $employee->employeeMainCompany->company_id,
            'employee_id' => $employee->id,
            'start_initial' => $shiftData['start'],
            'in_location_at_start_initial' => $isInLocation,
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id,
        ];

        try {
            $model = new Shift();
            if (!$model->load($newShiftData, '') || !$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to save shift. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['location_name'] = $currentLocationName;
            $this->return['message'] = Yii::t('api-hr', 'Successfully started new shift');
            $this->return['opened_shift'] = $model->attributes;
            $this->return['opened_break'] = null;

            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    public function actionStartBreak()
    {
        $post = Yii::$app->request->post();
        if (empty($post['break_details'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break details received');
            return $this->return;
        }
        $breakDetails = $post['break_details'];

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $locationDetails = null;
        if (!empty($breakDetails['device_location'])) {
            $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $breakDetails['device_location']['latitude'],
                'longitude' => $breakDetails['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }
        $isInLocation = 0;
        $currentLocationName = null;

        if (!empty($locationDetails) && !empty($locationDetails['name'])) {
            $isInLocation = 1;
            $currentLocationName = $locationDetails['name'];
        }

        $startBreakDetails = [
            'company_id' => $employee->employeeMainCompany->company_id,
            'employee_id' => $employee->id,
            'shift_id' => $breakDetails['shift_id'],
            'start_initial' => $breakDetails['start'],
            'in_location_at_start_initial' => $isInLocation,
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id,
        ];

        try {
            $model = new ShiftBreakInterval();
            if (!$model->load($startBreakDetails, '') || !$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to save shift break interval. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['location_name'] = $currentLocationName;
            $this->return['message'] = Yii::t('api-hr', 'Successfully saved break');
            $this->return['opened_shift'] = $employee->openshift;
            $this->return['opened_break'] = $model->attributes;
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    public function actionStopShift()
    {
        $post = Yii::$app->request->post();
        if (empty($post['shift_data'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break details received');
            return $this->return;
        }
        $shiftData = $post['shift_data'];

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = Shift::findOne([
            'id' => $shiftData['id'],
            'employee_id' => $employee->id,
            'deleted' => 0
        ]);
        if ($model === null) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "You don't have any opened shift");
            return $this->return;
        }

        if ((int)$model->id !== (int)$shiftData['id']) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "You don't have any opened shift");
            return $this->return;
        }

        $locationDetails = null;
        if (!empty($shiftData['device_location'])) {
            $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $shiftData['device_location']['latitude'],
                'longitude' => $shiftData['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }

        $isInLocation = 0;
        if (!empty($locationDetails) && !empty($locationDetails['name'])) {
            $isInLocation = 1;
        }

        $newShiftData = [
            'stop_initial' => $shiftData['stop'],
            'in_location_at_stop_initial' => $isInLocation,
            'updated' => date('Y-m-d H:i:s'),
            'updated_by' => Yii::$app->user->id,
        ];

        try {
            if (!$model->load($newShiftData, '') || !$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to stop shift. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully stopped the shift');
            $this->return['id'] = $model->id;
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    public function actionStopBreak()
    {
        $post = Yii::$app->request->post();
        if (empty($post['break_details'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break details received');
            return $this->return;
        }
        $breakDetails = $post['break_details'];

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = ShiftBreakInterval::find()
            ->where([
                'id' => $breakDetails['id'],
                'shift_id' => $breakDetails['shift_id'],
                'employee_id' => $employee->id
            ])->one();
        if (empty($model)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break found matching your request');
            return $this->return;
        }

        $locationDetails = null;
        if (!empty($breakDetails['device_location'])) {
            $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $breakDetails['device_location']['latitude'],
                'longitude' => $breakDetails['device_location']['longitude'],
                'employee_id' => $employee['id']
            ]);
        }
        $isInLocation = 0;

        if (!empty($locationDetails) && !empty($locationDetails['name'])) {
            $isInLocation = 1;
        }

        $stopBreakDetails = [
            'stop_initial' => $breakDetails['stop'],
            'in_location_at_stop_initial' => $isInLocation,
            'updated' => date('Y-m-d H:i:s'),
            'updated_by' => Yii::$app->user->id,
        ];

        try {
            if (!$model->load($stopBreakDetails, '') || !$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to save shift break interval. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully updated break');
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * Allow employee to manually update existing shift
     * @return array|mixed
     */
    public function actionManualUpdateShift()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Invalid shift details received');
            return $this->return;
        }

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = Shift::findOne([
            'id' => $post['id'],
            'employee_id' => $employee->id
        ]);
        if (empty($model)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No shift found matching your request');
            return $this->return;
        }

        try {
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            if (!$model->load($post, '') || $model->save() === false) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to update shift interval. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully updated shift');
            $this->return['shift_details'] = Shift::find()
                ->where(['id' => $model->id])
                ->with([
                    'shiftBreakIntervals' => function ($query) {
                        $query->where('deleted = 0');
                        $query->orderBy('added ASC');
                    },
                ])
                ->asArray()
                ->one();
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * Allow employee to manually update existing breaks
     * @return array|mixed
     */
    public function actionManualUpdateBreak()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id']) || empty($post['shift_id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Invalid break details received');
            return $this->return;
        }

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = ShiftBreakInterval::findOne([
            'id' => $post['id'],
            'shift_id' => $post['shift_id'],
            'employee_id' => $employee->id
        ]);
        if (empty($model)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break found matching your request');
            return $this->return;
        }

        try {
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            if (!$model->load($post, '') || $model->save() === false) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to update shift break interval. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully updated break');
            $this->return['shift_details'] = Shift::find()
                ->where(['id' => $model->shift_id])
                ->with([
                    'shiftBreakIntervals' => function ($query) {
                        $query->where('deleted = 0');
                        $query->orderBy('added ASC');
                    },
                ])
                ->asArray()
                ->one();
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * Allow employee to manually add shift breaks
     * @return array|mixed
     */
    public function actionManualAddBreak()
    {
        $post = Yii::$app->request->post();
        if (empty($post['shift_id']) || empty($post['start_initial']) || empty($post['stop_initial'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Invalid new break details received');
            return $this->return;
        }

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = new ShiftBreakInterval();
        $locationDetails = null;
        if (!empty($post['latitude']) && !empty($post['longitude'])) {
            $locationDetails = WorkLocation::getWorkLocationByCoordinatesForEmployeeId([
                'latitude' => $post['latitude'],
                'longitude' => $post['longitude'],
                'employee_id' => $employee['id']
            ]);
        }

        $startBreakDetails = [
            'company_id' => $employee->employeeMainCompany->company_id,
            'employee_id' => $employee->id,
            'shift_id' => $post['shift_id'],
            'start_initial' => $post['start_initial'],
            'stop_initial' => $post['stop_initial'],
            'observations' => $post['observations'],
            'in_location_at_start_initial' => !empty($locationDetails) && !empty($locationDetails['name']) ? 1 : 0,
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id,
        ];

        try {
            if (!$model->load($startBreakDetails, '') || $model->save() === false) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to create shift break interval. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully add new break');
            $this->return['shift_details'] = Shift::find()
                ->where(['id' => $model->shift_id])
                ->with([
                    'shiftBreakIntervals' => function ($query) {
                        $query->where('deleted = 0');
                        $query->orderBy('added ASC');
                    },
                ])
                ->asArray()
                ->one();
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * Allow employee to manually delete existing breaks
     * @return array|mixed
     */
    public function actionDeleteBreak()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id']) || empty($post['shift_id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Invalid break details received');
            return $this->return;
        }

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = ShiftBreakInterval::findOne([
            'id' => $post['id'],
            'shift_id' => $post['shift_id'],
            'employee_id' => $employee->id
        ]);
        if (empty($model)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'No break found matching your request');
            return $this->return;
        }

        try {
            $model->deleted = 1;
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            if ($model->save() === false) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to delete shift break. Please contact an administrator!'));
            }

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully deleted break');
            $this->return['shift_details'] = Shift::find()
                ->where(['id' => $model->shift_id])
                ->with([
                    'shiftBreakIntervals' => function ($query) {
                        $query->where('deleted = 0');
                        $query->orderBy('added ASC');
                    },
                ])
                ->asArray()
                ->one();
            return $this->return;
        } catch (HttpException $exc) {
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * Allow employee to manually delete existing shift and related breaks
     * @return array|mixed
     */
    public function actionDeleteShift()
    {
        $post = Yii::$app->request->post();
        if (empty($post['id'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Invalid shift details received');
            return $this->return;
        }

        //we don't verify because the verification is made before each action
        // using the beforeAction method
        $employee = Yii::$app->user->identity->employee;

        $model = Shift::findOne([
            'id' => $post['id'],
            'employee_id' => $employee->id
        ]);
        if (empty($model)) {
            $this->return['status'] = HttpStatus::NOT_FOUND;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Shift not found');
            return $this->return;
        }
        if ($model->validated === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already validated');
            return $this->return;
        }
        if ($model->deleted === 1) {
            $this->return['status'] = HttpStatus::CONFLICT;
            $this->return['message'] = Yii::t('api-hr', 'Shift already deleted');
            return $this->return;
        }

        $transaction = Shift::getDb()->beginTransaction();
        try {
            $model->deleted = 1;
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;

            if ($model->save() === false) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                    }
                }
                throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to delete shift. Please contact an administrator!'));
            }
            foreach ($model->shiftBreakIntervals as $breakInterval) {
                $breakInterval->deleted = 1;
                $breakInterval->updated = date('Y-m-d H:i:s');
                $breakInterval->updated_by = Yii::$app->user->id;
                if ($breakInterval->save() === false) {
                    if ($breakInterval->hasErrors()) {
                        foreach ($breakInterval->errors as $error) {
                            throw new HttpException(HttpStatus::CONFLICT, $error[0]);
                        }
                    }
                    throw new HttpException(HttpStatus::INTERNAL_SERVER_ERROR, Yii::t('api-hr', 'Failed to delete one or more breaks from shift. Please contact an administrator!'));
                }
            }
            $transaction->commit();

            $this->return['status'] = HttpStatus::OK;
            $this->return['message'] = Yii::t('api-hr', 'Successfully deleted shift');
            return $this->return;
        } catch (HttpException $exc) {
            $transaction->rollBack();
            $this->return['status'] = $exc->statusCode;
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        } catch (Exception $exc) {
            $transaction->rollBack();
            $this->return['status'] = HttpStatus::INTERNAL_SERVER_ERROR;
            Yii::$app->response->statusCode = HttpStatus::INTERNAL_SERVER_ERROR;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }
}