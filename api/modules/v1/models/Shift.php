<?php

namespace api\modules\v1\models;

use api\models\PermissionDay;
use common\components\HttpStatus;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;

class Shift extends ShiftParent
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.shift';
    }

    /**
     * @param $employeeId
     * @return array
     */
    public static function getMonthlyScheduleDetails($employeeId)
    {
        $monthlyScheduleDetails = [];

        $currentYear = date('Y');
        $currentMonth = date('n');

        $schedule = PermissionDay::find()->where([
            'employee_id' => $employeeId,
            'year' => $currentYear,
            'month' => $currentMonth
        ])->all();
        if (empty($schedule)) {
            return $monthlyScheduleDetails;
        }

        foreach ($schedule as $day) {
            $monthlyScheduleDetails [$day['day']] = [
                'work' => $day->work !== null ? $day->work : null,
                'co' => $day->co !== null ? $day->co : null,
                'permission_details' => [
                    'permission' => $day->permission !== null ? $day->permission : null,
                    'start_hour' => $day->start_hour !== null ? $day->start_hour : null,
                    'stop_hour' => $day->stop_hour !== null ? $day->stop_hour : null,
                ]
            ];
        }

        return $monthlyScheduleDetails;
    }

    /**
     * @param $employeeId
     * @return array
     * @throws NotFoundHttpException
     */
    public static function getShiftsHistory($employeeId)
    {
        $shifts = [];
        $numberOfIntervals = 30;
        $employee = Employee::find()->where(['id' => $employeeId])->one();
        if ($employee === null) {
            throw new NotFoundHttpException(Yii::t('api-hr', 'Employee not found') . PHP_EOL);
        }
        $employeeCompanyId = $employee->employeeMainCompany->company_id;

        $lastTenShiftIntervals = Shift::find()
            ->where('employee_id = :employee_id', [':employee_id' => $employeeId])
            ->andWhere('company_id = :company_id', [':company_id' => $employeeCompanyId])
            ->andWhere('deleted = 0')
            ->orderBy('id DESC')
            ->limit($numberOfIntervals)
            ->all();
        if (empty($lastTenShiftIntervals)) {
            return $shifts;
        }

        foreach ($lastTenShiftIntervals as $shift) {
            $shifts[$shift['id']]['shift_details'] = [
                "start" => $shift['start_initial'],
                "stop" => $shift['stop_initial'],
                "start_modified" => $shift['start_modified'],
                "stop_modified" => $shift['stop_modified'],
                "observations" => $shift['observations'],
                "in_location_at_start_initial" => $shift['in_location_at_start_initial'],
                "in_location_at_stop_initial" => $shift['in_location_at_stop_initial'],
                "validated" => $shift['validated']
            ];

            $initialBreaks = ShiftBreakInterval::find()->where([
                'employee_id' => $employeeId,
                'company_id' => $employeeCompanyId,
                'shift_id' => $shift['id'],
                'deleted' => 0
            ])->all();
            if (empty($initialBreaks)) {
                $shifts[$shift['id']]['break_intervals']['initial'] = [];
            } else {
                foreach ($initialBreaks as $initialBreak) {
                    $shifts[$shift['id']]['break_intervals']['initial'][$initialBreak['id']] = [
                        "start" => $initialBreak['start_initial'],
                        "stop" => $initialBreak['stop_initial'],
                        "start_modified" => $initialBreak['start_modified'],
                        "stop_modified" => $initialBreak['stop_modified'],
                        "deleted" => $initialBreak['deleted'],
                        "observations" => $initialBreak['observations'],
                    ];
                }
            }

            $shifts[$shift['id']]['break_intervals']['new'] = [];
        }

        return $shifts;
    }

    /**
     * @param $employeeId
     * @return array
     * @throws NotFoundHttpException
     */
    public static function getOngoingShift($employeeId)
    {
        $shifts = [];

        $employee = Employee::find()->where(['id' => $employeeId])->one();
        if ($employee === null) {
            throw new NotFoundHttpException( Yii::t('api-hr', 'Employee not found') . PHP_EOL);
        }
        $employeeCompanyId = $employee->company_id;

        $shiftModel = Shift::find()
            ->where('employee_id = :employee_id', [':employee_id' => $employeeId])
            ->andWhere('company_id = :company_id', [':company_id' => $employeeCompanyId])
            ->andWhere('ISNULL(stop_initial)')
            ->andWhere('validated = 0')
            ->andWhere('deleted = 0')
            ->all();
        if (empty($shiftModel)) {
            return $shifts;
        }

        foreach ($shiftModel as $shift) {
            $shifts[$shift['id']]['shift_details'] = [
                "start" => $shift['start_initial'],
                "stop" => $shift['stop_initial'],
                "start_modified" => $shift['start_modified'],
                "stop_modified" => $shift['stop_modified'],
                "observations" => $shift['observations'],
                "in_location_at_start_initial" => $shift['in_location_at_start_initial'],
                "in_location_at_stop_initial" => $shift['in_location_at_stop_initial'],
                "validated" => $shift['validated']
            ];

            $initialBreaks = ShiftBreakInterval::find()->where([
                'employee_id' => $employeeId,
                'company_id' => $employeeCompanyId,
                'shift_id' => $shift['id'],
                'deleted' => 0
            ])->all();
            if (empty($initialBreaks)) {
                $shifts[$shift['id']]['break_intervals']['initial'] = [];
            } else {
                foreach ($initialBreaks as $initialBreak) {
                    $shifts[$shift['id']]['break_intervals']['initial'][$initialBreak['id']] = [
                        "start" => $initialBreak['start_initial'],
                        "stop" => $initialBreak['stop_initial'],
                        "start_modified" => $initialBreak['start_modified'],
                        "stop_modified" => $initialBreak['stop_modified'],
                        "deleted" => $initialBreak['deleted'],
                        "observations" => $initialBreak['observations'],
                    ];
                }
            }

            $shifts[$shift['id']]['break_intervals']['new'] = [];
        }
        return $shifts;
    }
}
