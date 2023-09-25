<?php

namespace console\controllers;

use backend\modules\hr\models\Employee;
use backend\modules\hr\models\HrActiveRecord;
use backend\modules\hr\models\PermissionDay;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

class AutoGeneratePermissionDayController extends Controller
{
    /**
     * @throws \yii\db\Exception
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionIndex($companyID, $year, $month)
    {
        isset($month) ? $currentMonth = $month : $currentMonth = date('n');
        isset($year) ? $currentYear = $year : $currentYear = date('Y');

        //find the corresponding active employees
        $employees = Employee::findAllByAttributes([
            'company_id' => $companyID,
            'status' => 1
        ]);
        //for every employee
        foreach ($employees as $employee) {
            //check if company, employee, day are already saved in permission day table
            //if yes, skip
            $check = PermissionDay::findAllByAttributes([
                'company_id' => $companyID,
                'employee_id' => $employee->id,
                'year' => $currentYear,
                'month' => $currentMonth
            ]);
            if (count($check) != 0) {
                continue;
            }
            //otherwise, save new entries
            $daysInMonth = date('t', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
            //for every day from month
            for ($i = 1; $i <= $daysInMonth; $i++) {
                //save
                try {
                    $dayCreate = date_create($currentYear . '-' . $currentMonth . '-' . $i);
                    $dayFormat = date_format($dayCreate, 'Y-m-d');
                    $dayNumber = date('N', mktime(0, 0, 0, $currentMonth, $i, $currentYear));
                    PermissionDay::createByAttributes([
                        'company_id' => $companyID,
                        'employee_id' => $employee->id,
                        'year' => $currentYear,
                        'month' => $currentMonth,
                        'day' => date($dayFormat),
                        'work' => HrActiveRecord::isWeekend($dayNumber) ? 0 : 1,
                        'co' => 0,
                        'permission' => 0,
                        'added_by' => Yii::$app->params['superAdmin']
                    ]);
                } catch (Exception $exc) {
                    $myMes = Yii::t('cmd-hr', 'The permission day could not be saved') . "\n";
                    $myMes .= $exc->getMessage();
                    throw new Exception($myMes, $exc->getCode());
                }
            }
        }
    }
}