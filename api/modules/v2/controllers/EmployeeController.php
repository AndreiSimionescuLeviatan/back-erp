<?php

namespace api\modules\v2\controllers;

use api\models\PermissionDay;
use api\modules\v2\models\Employee;
use api\modules\v2\models\EmployeeCompany;
use api\modules\v2\models\User;
use common\components\HttpStatus;
use Yii;
use yii\db\ActiveQuery;

/**
 * V2 of User controller
 */
class EmployeeController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\Employee';

    public function actionAssignedCompanies()
    {
        $user = User::findIdentity(Yii::$app->user->id);
        if (!$user) {
            Yii::$app->response->statusCode = 401;
            return [
                'status' => 401,
                'message' => Yii::t('app', 'User not found!')
            ];
        }

        $employee = Employee::find()
            ->where([
                'user_id' => $user->id,
                'status' => 1
            ])
            ->with('employeeCompanies.company')
            ->asArray()
            ->one();
        if (!$employee) {
            Yii::$app->response->statusCode = 401;
            return [
                'status' => 401,
                'message' => Yii::t('app', 'No employee found')
            ];
        }

        $this->return['employee'] = $employee;
//        $this->return['user_companies'] = $employee->employeeCompanies;
//        $this->return['user_companies'] = [];
//        foreach ($user->companies as $key => $company) {
//            unset(
//                $company['added'],
//                $company['added_by'],
//                $company['city_id'],
//                $company['country_id'],
//                $company['cui'],
//                $company['reg_number'],
//                $company['state_id'],
//                $company['tva'],
//                $company['updated'],
//                $company['updated_by']
//            );
//            $this->return['user_companies'][$key] = $company;
//        }
        return $this->return;
    }

    /**
     * Returns a list with users that can take over the current employee duties
     * @param $company_id
     * @return Employee[]|array|\yii\db\ActiveRecord[]
     */
    public function actionEmployeesTakeOverList($company_id)
    {
        //@todo modify query to filter employees that are also on vacation in the requested period */
        $currentEmployee = Employee::find()
            ->select("id, user_id")
            ->where([
                'user_id' => Yii::$app->user->id,
                'status' => 1
            ])
            ->one();
        if (empty($currentEmployee)) {
            Yii::$app->response->statusCode = 401;
            return [
                'status' => 401,
                'message' => Yii::t('app', 'No employee found')
            ];
        }
        if (empty($currentEmployee->employeeMainCompany)) {
            Yii::$app->response->statusCode = 400;
            return [
                'status' => 401,
                'message' => Yii::t('app', 'The employee dont have a main company set. Please contact an administrator!')
            ];
        }

        $this->return['available_take_over_employees_list'] = [];
        $availableTakeOverEmployeesList = EmployeeCompany::find()
            ->select([
                "id", "employee_id",
                "company_id", "department_id", "office_id", "workplace",
                "start_schedule", "stop_schedule", "holidays"
            ])
            ->where([
                /**@todo modify query to filter employees that are also on vacation in the requested period */
                'company_id' => $company_id,
                'type' => 1,
                'department_id' => $currentEmployee->employeeMainCompany->department_id,
                'main_activity' => 1
            ])
            ->andWhere(['not', "employee_id = $currentEmployee->id"])
            ->with(['employee' => function (ActiveQuery $query) {
                $query->select([
                    "id", "user_id", "first_name", "middle_name", "last_name", "full_name"
                ]);
            }])
            ->asArray()
            ->all();
        foreach ($availableTakeOverEmployeesList as $item) {
            $this->return['available_take_over_employees_list'][] = $item['employee'];
        }
        return $this->return;
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function actionTimeOffRequests($id)
    {
        $employeeRequests = Employee::find()
            ->where("id = :id", [":id" => $id])
            ->with([
                'requestRecords.holidayType',
                'requestRecords.approvalHistories.approver',
            ])
            ->asArray()
            ->one();
        if (empty($employeeRequests)) {
            Yii::$app->response->statusCode = 404;
            return [
                'status' => 404,
                'message' => Yii::t('app', 'No employee found')
            ];
        }

        $this->return['requests'] = [
            'waiting' => [],
            'approved' => [],
            'rejected' => [],
        ];

        foreach ($employeeRequests['requestRecords'] as $request) {
            //0: waiting; 1: approved; 2: rejected
            if ((int)$request['status'] === 0 || (int)$request['status'] === 1) {
                $this->return['requests']['waiting'][] = $request;
            } elseif ((int)$request['status'] === 2) {
                $this->return['requests']['approved'][] = $request;
            } elseif ((int)$request['status'] === 3) {
                $this->return['requests']['rejected'][] = $request;
            }
        }

        return $this->return;
    }

    public function actionMonthlyScheduleDetails($employee_id)
    {
        $schedule = PermissionDay::find()
            ->select(['id', 'co', 'day', 'work', 'permission', 'start_hour', 'stop_hour'])
            ->where([
                'employee_id' => $employee_id,
                'company_id' =>
                    !empty(Yii::$app->user->identity->employee) &&
                    !empty(Yii::$app->user->identity->employee->employeeMainCompany) &&
                    !empty(Yii::$app->user->identity->employee->employeeMainCompany->company_id) ?
                        Yii::$app->user->identity->employee->employeeMainCompany->company_id :
                        null,
                'year' => date('Y'),
                'month' => date('n')
            ])
            ->orderBy("day ASC")
            ->asArray()
            ->all();

        $this->return['monthly_schedule_details'] = $schedule;
        $this->return['status'] = HttpStatus::OK;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-hr', 'Successfully sent schedule details');
        return $this->return;
    }

    /**
     * @return array|mixed
     */
    public function actionShifts()
    {
        $employee = Employee::find()
            ->select(['id', 'full_name', 'first_name', 'middle_name', 'last_name'])
            ->where([
                'user_id' => Yii::$app->user->id,
                'status' => 1
            ])
            ->with(
                [
                    'employeeMainCompany' => function (ActiveQuery $query) {
                        $query->select(['id', 'employee_id']);
                    },
                    'shifts' => function (ActiveQuery $query) {
                        $query->select(['id', 'employee_id', 'start_initial', 'start_modified', 'stop_initial', 'stop_modified', 'observations', 'validated']);
                        $query->orderBy('start_initial DESC');
                    },
                    'shifts.shiftBreakIntervals' => function (ActiveQuery $query) {
                        $query->select(['id', 'employee_id', 'shift_id', 'start_initial', 'start_modified', 'stop_initial', 'stop_modified']);
                        $query->orderBy('start_initial DESC');
                    },
                    'openedShifts',
                    'unvalidatedShifts'
                ])
            ->asArray()
            ->one();
        if (empty($employee)) {
            $this->return['status'] = HttpStatus::UNAUTHORIZED;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }
        if (empty($employee['employeeMainCompany'])) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "You don't have a main company set. Please contact an administrator!");
            return $this->return;
        }


        $this->return['unvalidated'] = $employee['unvalidatedShifts'];
        $this->return['opened'] = $employee['openedShifts'];
        $this->return['history'] = $employee['shifts'];

        $this->return['status'] = HttpStatus::OK;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-hr', 'Successfully sent employee shifts');
        return $this->return;
//        if (empty($lastTenShiftIntervals)) {
//            return $shifts;
//        }

//        foreach ($lastTenShiftIntervals as $shift) {
//            $shifts[$shift['id']]['shift_details'] = [
//                "start" => $shift['start_initial'],
//                "stop" => $shift['stop_initial'],
//                "start_modified" => $shift['start_modified'],
//                "stop_modified" => $shift['stop_modified'],
//                "observations" => $shift['observations'],
//                "in_location_at_start_initial" => $shift['in_location_at_start_initial'],
//                "in_location_at_stop_initial" => $shift['in_location_at_stop_initial'],
//                "validated" => $shift['validated']
//            ];
//
//            $initialBreaks = ShiftBreakInterval::find()->where([
//                'employee_id' => $employeeId,
//                'company_id' => $employeeCompanyId,
//                'shift_id' => $shift['id'],
//                'deleted' => 0
//            ])->all();
//            if (empty($initialBreaks)) {
//                $shifts[$shift['id']]['break_intervals']['initial'] = [];
//            } else {
//                foreach ($initialBreaks as $initialBreak) {
//                    $shifts[$shift['id']]['break_intervals']['initial'][$initialBreak['id']] = [
//                        "start" => $initialBreak['start_initial'],
//                        "stop" => $initialBreak['stop_initial'],
//                        "start_modified" => $initialBreak['start_modified'],
//                        "stop_modified" => $initialBreak['stop_modified'],
//                        "deleted" => $initialBreak['deleted'],
//                        "observations" => $initialBreak['observations'],
//                    ];
//                }
//            }
//
//            $shifts[$shift['id']]['break_intervals']['new'] = [];
//        }


//        try {
//            $shifts = Shift::getShiftsHistory($employeeId);
//            $this->return['shifts'] = $shifts;
//            $this->return['status'] = HttpStatus::OK;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Successfully sent shifts history');
//            return $this->return;
//        } catch (NotFoundHttpException $exc) {
//            $this->return['status'] = $exc->statusCode;
//            Yii::$app->response->statusCode = $exc->statusCode;
//            $this->return['message'] = $exc->getMessage();
//            return $this->return;
//        }
    }

    /**
     * @return array|mixed
     */
    public function actionOpenshift()
    {
        $employee = Employee::find()
            ->select(['id'])
            ->where([
                'user_id' => Yii::$app->user->id,
                'status' => 1
            ])
            ->with(
                [
                    'employeeMainCompany' => function (ActiveQuery $query) {
                        $query->select(['id', 'employee_id', 'start_schedule', 'stop_schedule']);
                    },
                    'openshift.openedShiftBreak' => function (ActiveQuery $query) {
                        //$query->select(["SUM(TIMESTAMPDIFF(SECOND, start_initial,stop_initial)) AS total_break_in_seconds"]);
                        $query->select(['id', 'shift_id', 'start_initial', 'start_modified', 'stop_initial', 'stop_modified']);
                    },
                ]
            )
            ->one();
        if (empty($employee)) {
            $this->return['status'] = HttpStatus::UNAUTHORIZED;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
            return $this->return;
        }
        if (empty($employee->employeeMainCompany)) {
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "You don't have a main company set. Please contact an administrator!");
            return $this->return;
        }

        $this->return['opened_shift'] = $employee->openshift;
        $this->return['opened_break'] = empty($employee->openshift) ? null : $employee->openshift->openedShiftBreak;
        $this->return['unvalidated'] = $employee->unvalidatedShifts;
        $this->return['company_shift_details'] = $employee->employeeMainCompany;

        $this->return['status'] = HttpStatus::OK;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-hr', 'Successfully sent employee shifts');
        return $this->return;
    }

    /**
     * @return array|mixed
     * @todo the method is made just for testing, update the method before sending to production
     */
    public function actionPermissions()
    {
        if (empty(Yii::$app->user->identity->employee)) {
            $this->return['status'] = HttpStatus::UNAUTHORIZED;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', 'Wrong/Empty employee details');
            return $this->return;
        }

        $this->return['status'] = HttpStatus::OK;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = Yii::t('api-hr', 'Successfully sent employee permissions');
        $this->return['can_countersign'] = true;
        return $this->return;
    }
}