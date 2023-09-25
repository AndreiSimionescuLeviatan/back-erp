<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\ApprovalHistory;
use common\components\DateTimeHelper;
use DateInterval;
use DateTime;
use Exception;
use Yii;

/**
 * V2 of Approval History controller
 */
class ApprovalHistoryController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\ApprovalHistory';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['view']);
        return $actions;
    }

    /**
     * @param $id
     * @return array|mixed
     * @throws Exception
     */
    public function actionView($id)
    {
        $model = ApprovalHistory::find()
            ->where([
                'id' => $id,
            ])
            ->with([
                'requestRecord.approvalHistories' => function (\yii\db\ActiveQuery $query) {
                    //if (isset(Yii::$app->user->identity->employee)) {
                    //$query->where(['!=', 'approver_id', Yii::$app->user->identity->employee->id]);
                    //}
                    $query->where(['!=', 'status', 0]);
                    $query->orderBy('level ASC');
                },
                'requestRecord.approvalHistories.approver'
            ])
            ->one();
        if (empty($model)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-logistic', "Time off request could not be found");
            return $this->return;
        }
        $this->return['time_off_request_details'] = null;
        if (
            !empty($model->requestRecord) &&
            !empty($model->requestRecord->employee) &&
            !empty($model->requestRecord->holidayType) &&
            !empty($model->requestRecord->employee->employeeMainCompany) &&
            !empty($model->requestRecord->employee->employeeMainCompany->company)
        ) {
            $_prevApproves = [];
            $timeOffType = $model->requestRecord->holidayType;
            $this->return['time_off_request_details'] = [
                'approval_history_id' => $model->id,
                'level' => $model->level,
                'request_id' => $model->request_record_id,
                'company_id' => $model->requestRecord->hrCompany->id,
                'company_name' => $model->requestRecord->hrCompany->name,
                'employee_id' => $model->requestRecord->employee_id,
                'employee_name' => $model->requestRecord->employee->full_name,
                'time_off_type_id' => $model->requestRecord->holiday_type_id,
                'time_off_name' => $timeOffType->name,
                'time_off_unit' => $timeOffType->unit,
                'time_off_measure_unit' => $timeOffType->measure_unit,
                'time_off_recurrence_type' => $timeOffType->recurrence_type,
                'time_off_abs_value' => $model->requestRecord->counter,
                'time_off_start' => $model->requestRecord->start,
                'time_off_stop' => $model->requestRecord->stop,
                'requester_observations' => $model->requestRecord->observations,
                'approver_observations' => $model->observations
            ];

            foreach ($model->requestRecord->approvalHistories as $request) {
                $_prevApproves[] = [
                    'id' => $request['id'],
                    'status' => $request['status'],
                    'level' => $request['level'],
                    'approver_id' => $request['approver_id'],
                    'approver_observations' => $request['observations'],
                    'approver_full_name' => $request['approver']['full_name'],
                ];
            }
            $this->return['time_off_request_details']['prev_approves'] = $_prevApproves;

            $start = new DateTime($model->requestRecord->start);
            $stop = new DateTime($model->requestRecord->stop);
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
            $this->return['message'] = Yii::t('api-logistic', "Your request could not be processed because of incomplete data. Please contact an administrator");
        }

        return $this->return;
    }
}