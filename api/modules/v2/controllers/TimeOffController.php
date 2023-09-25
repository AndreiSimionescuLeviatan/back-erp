<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\ApprovalHistory;
use api\modules\v2\models\RequestRecord;
use api\modules\v2\models\User;
use common\components\DateTimeHelper;
use DateInterval;
use DateTime;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * V2 of Holiday Type controller
 */
class TimeOffController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\RequestRecord';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['view'], $actions['update']);
        return $actions;
    }

    /**
     * @throws HttpException
     * @throws InvalidConfigException
     * @throws ServerErrorHttpException
     */
    public function actionCreate()
    {

        $transaction = Yii::$app->ecf_hr_db->beginTransaction();
        try {
            $requestRecord = new RequestRecord();
            $requestRecord->load(Yii::$app->getRequest()->getBodyParams(), '');
            $requestRecord->added = date('Y-m-d H:i:s');
            $requestRecord->added_by = Yii::$app->user->id;
            if (!$requestRecord->save()) {
                if ($requestRecord->hasErrors()) {
                    foreach ($requestRecord->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-hr', 'Failed to save your request. Please contact an administrator!'));
            }

            if (!empty($requestRecord->take_over_employee_id) && !empty($requestRecord->takeOverEmployee)) {
                $levelApprovalDetails = [
                    'level' => 1,
                    'approver_id' => [$requestRecord->take_over_employee_id],
                    'approver_user_id' => [$requestRecord->takeOverEmployee->id]
                ];
            } else {
                $levelApprovalDetails = $requestRecord::getNextLevelApproveDetails($requestRecord, 1);
            }

            $notifyErrMsg = "(Level {$levelApprovalDetails['level']})";
            for ($i = 0; $i < count($levelApprovalDetails['approver_id']); $i++) {
                $approvalHistory = new ApprovalHistory();
                $approvalHistory->request_record_id = $requestRecord->id;
                $approvalHistory->level = $levelApprovalDetails['level'];
                $approvalHistory->approver_id = $levelApprovalDetails['approver_id'][$i];
                $approvalHistory->added = date('Y-m-d H:i:s');
                $approvalHistory->added_by = Yii::$app->user->id;
                if (!$approvalHistory->save()) {
                    if ($approvalHistory->hasErrors()) {
                        foreach ($approvalHistory->errors as $error) {
                            throw new HttpException(409, $error[0]);
                        }
                    }
                    throw new HttpException(500, Yii::t('api-hr', 'Failed to save your step 2 request. Please contact an administrator!'));
                }
                try {
                    $requestRecord->notifyApprover($levelApprovalDetails['approver_id'][$i]);
                } catch (Exception $exc) {
                    $notifyErrMsg .= Yii::t('api-hr', ". The request was saved but is not completed because no notification was sent.") . " " . $exc->getMessage();
                }
            }
            $this->return['_message'] = $levelApprovalDetails;
            $this->return['message'] = Yii::t('api-hr', "Successfully saved the request") . $notifyErrMsg;


            $transaction->commit();
            return $this->return;
        } catch (HttpException $exc) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = $exc->statusCode;
            $this->return['status'] = $exc->statusCode;
            $this->return['message'] = $exc->getMessage();
            return $this->return;
        }
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws Exception
     */
    public function actionView($id)
    {
        $requestDetails = RequestRecord::find()
            ->where([
                'id' => $id,
            ])
            ->with([
                'employee.employeeMainCompany.company',
                'approvalHistories' => function ($query) {
                    $query->orderBy('level ASC');
                },
                'approvalHistories.approver',
                'holidayType'
            ])
            ->asArray()
            ->one();
        if (empty($requestDetails)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Time off request could not be found");
            return $this->return;
        }

        if (
            !empty($requestDetails['holidayType']) &&
            !empty($requestDetails['approvalHistories']) &&
            !empty($requestDetails['employee']) &&
            !empty($requestDetails['employee']['employeeMainCompany']) &&
            !empty($requestDetails['employee']['employeeMainCompany']['company'])
        ) {
            $_approvalHistory = [];
            $employeeCompanyName = $requestDetails['employee']['employeeMainCompany']['company']['name'];
            $timeOffType = $requestDetails['holidayType'];
            $this->return['time_off_request_details'] = [
                'request_id' => $requestDetails['id'],
                'request_status' => $requestDetails['status'],
                'company_name' => $employeeCompanyName,
                'employee_name' => $requestDetails['employee']['full_name'],
                'time_off_name' => $timeOffType['name'],
                'time_off_abs_value' => $requestDetails['counter'],
                'time_off_start' => $requestDetails['start'],
                'time_off_stop' => $requestDetails['stop'],
                'time_off_unit' => $timeOffType['unit'],
                'time_off_measure_unit' => $timeOffType['measure_unit'],
                'requester_observations' => $requestDetails['observations']
            ];
            $backendAbsoluteUrl = Yii::$app->params['backendAbsoluteUrl'];
            foreach ($requestDetails['approvalHistories'] as $request) {
                $_approvalHistory[] = [
                    'id' => $request['id'],
                    'status' => $request['status'],
                    'level' => $request['level'],
                    'approver_id' => $request['approver_id'],
                    'approver_observations' => $request['observations'],
                    'approver_full_name' => $request['approver']['full_name'],
                    'photo' => $backendAbsoluteUrl . "/" . User::getUserImage($request['approver']['user_id']),
                ];
            }
            $this->return['time_off_request_details']['approval_history'] = $_approvalHistory;

            $start = new DateTime($requestDetails['start']);
            $stop = new DateTime($requestDetails['stop']);
            if ($timeOffType['measure_unit'] == 1) {
                //add one day to correctly display the diff
                //@todo try to change this implementation to receive the info from app directly
                $timeOffType['measure_unit'] == 1 && $stop->add(new DateInterval('P1D'));
                $time_off_value = DateTimeHelper::countDaysInInterval($start, $stop, true) . " zile";
            } else {
                $abs_diff = $stop->diff($start); //3
                $time_off_value = $abs_diff->format("%H:%I");
            }
            $this->return['time_off_request_details']['time_off_value'] = $time_off_value;
        } else {
            Yii::$app->response->statusCode = 422;
            $this->return['status'] = 422;
            $this->return['time_off_request_details'] = $requestDetails;
            $this->return['message'] = Yii::t('api-logistic', "Something went wrong while getting your request details. Please contact an administrator");
        }

        return $this->return;
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $requestData = Yii::$app->getRequest()->getBodyParams();
        $model = RequestRecord::find()
            ->where("id = :id", [":id" => $id])
            ->one();
        if (empty($model)) {
            Yii::$app->response->statusCode = 404;
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('api-logistic', "No request found with your details. Please contact an administrator");
            return $this->return;
        }
        if (empty($requestData['approver_id'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-logistic', "Wrong request details received");
            return $this->return;
        }
        if (empty($requestData['status'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-logistic', "Wrong request status details received");
            return $this->return;
        }
        if (empty($requestData['level'])) {
            Yii::$app->response->statusCode = 400;
            $this->return['status'] = 400;
            $this->return['message'] = Yii::t('api-logistic', "Wrong request level details received");
            return $this->return;
        }

        $approvalHistory = ApprovalHistory::find()
            ->where("id = :approval_history_id AND request_record_id = :request_record_id AND approver_id = :approver_id", [
                ':approval_history_id' => $requestData['approval_history_id'],
                ':request_record_id' => $id,
                ':approver_id' => $requestData['approver_id']
            ])
            ->one();
        if (empty($approvalHistory)) {
            Yii::$app->response->statusCode = 404;
            $this->return['status'] = 404;
            $this->return['message'] = Yii::t('api-logistic', "Your request has wrong details. Please contact an administrator");
            return $this->return;
        }

        $approvalHistory->progress = 1;
        $approvalHistory->status = $requestData['status'];
        $approvalHistory->observations = !empty($requestData['observations']) ? $requestData['observations'] : null;
        $approvalHistory->updated = date('Y-m-d H:i:s');
        $approvalHistory->updated_by = Yii::$app->user->id;
        if (!$approvalHistory->save()) {
            if ($approvalHistory->hasErrors()) {
                foreach ($approvalHistory->errors as $error) {
                    throw new HttpException(409, $error[0]);
                }
            }
            throw new HttpException(500, Yii::t('api-hr', 'Failed to save your request history. Please contact an administrator!'));
        }

        if ((int)$requestData['status'] === 2) {
            $model->status = 3;
        } elseif ((int)$requestData['level'] === 3) {
            $model->status = (int)$requestData['status'] === 2 ? 3 : 2;
        } else {
            $model->status = 1;
        }
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(409, $error[0]);
                }
            }
            throw new HttpException(500, Yii::t('api-hr', 'Failed to update your request details. Please contact an administrator!'));
        }

        if ((int)$requestData['status'] === 0 || (int)$requestData['level'] === 3 || (int)$requestData['status'] === 2) {
            $notifyErrMsg = "(Level {$requestData['level']})";
            try {
                $model->notifyRequester($model->employee_id, $requestData['status']);
            } catch (Exception $exc) {
                $notifyErrMsg .= Yii::t('api-hr', ". The request was saved but is not completed because no notification was sent.") . " " . $exc->getMessage();
            }
            $this->return['message'] = Yii::t('api-hr', "Successfully saved the request" . $notifyErrMsg);
            return $this->return;
        }

        $levelApprovalDetails = RequestRecord::getNextLevelApproveDetails($model, (int)$requestData['level']);

        $notifyErrMsg = "(Level {$levelApprovalDetails['level']})";
        for ($i = 0; $i < count($levelApprovalDetails['approver_id']); $i++) {
            $approvalHistory = new ApprovalHistory();
            $approvalHistory->request_record_id = $model->id;
            $approvalHistory->level = $levelApprovalDetails['level'];
            $approvalHistory->approver_id = $levelApprovalDetails['approver_id'][$i];
            $approvalHistory->added = date('Y-m-d H:i:s');
            $approvalHistory->added_by = Yii::$app->user->id;
            if (!$approvalHistory->save()) {
                if ($approvalHistory->hasErrors()) {
                    foreach ($approvalHistory->errors as $error) {
                        throw new HttpException(409, $error[0]);
                    }
                }
                throw new HttpException(500, Yii::t('api-hr', 'Failed to save your step 2 request. Please contact an administrator!'));
            }
            try {
                $model->notifyApprover($levelApprovalDetails['approver_id'][$i]);
            } catch (Exception $exc) {
                $notifyErrMsg .= Yii::t('api-hr', ". The request was saved but is not completed because no notification was sent.") . " " . $exc->getMessage();
            }
        }

        $this->return['message'] = Yii::t('api-hr', "Successfully saved the request" . $notifyErrMsg);
        return $this->return;

    }

    /**
     * @param $employee_id
     * @return array|mixed
     */
    public function actionApprovalRequests($employee_id)
    {
        $approvalsRequests = ApprovalHistory::find()
            ->where([
                'approver_id' => $employee_id,
            ])
            ->with([
                'requestRecord.holidayType',
                'requestRecord.employee.employeeMainCompany',
            ])
            ->asArray()
            ->all();
        $this->return['approval_requests'] = [
            'waiting_counter' => 0,
            'waiting' => [],
            'approved' => [],
            'rejected' => [],
        ];
        $backendAbsoluteUrl = Yii::$app->params['backendAbsoluteUrl'];
        foreach ($approvalsRequests as $request) {
            $request['requestRecord']['employee']['photo'] = $backendAbsoluteUrl . "/" . User::getUserImage($request['requestRecord']['employee']['user_id']);
            //0: waiting; 1: approved; 2: rejected
            if ((int)$request['status'] === 0) {
                $this->return['approval_requests']['waiting'][] = $request;
                $this->return['approval_requests']['waiting_counter']++;
            } elseif ((int)$request['status'] === 1) {
                $this->return['approval_requests']['approved'][] = $request;
            } elseif ((int)$request['status'] === 2) {
                $this->return['approval_requests']['rejected'][] = $request;
            }
        }

        return $this->return;
    }
}