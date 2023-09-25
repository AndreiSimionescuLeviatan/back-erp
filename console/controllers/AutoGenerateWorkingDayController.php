<?php

namespace console\controllers;

use api\models\EmployeeCompany;
use backend\modules\hr\models\Employee;
use backend\modules\hr\models\HrActiveRecord;
use backend\modules\hr\models\WorkingDay;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

class AutoGenerateWorkingDayController extends Controller
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

            //check if company, year, month are already saved in working day table
            //if yes, skip
        $employees = EmployeeCompany::find()
            ->leftJoin('ecf_hr.employee as em', 'em.status = 1')
            ->where(['company_id' => $companyID])
            ->all();
        //for every employee
        foreach ($employees as $employee) {
            //check if company, employee, year, month are already saved in working day empl table
            //if yes, skip
            $check = WorkingDay::findAllByAttributes([
                'company_id' => $companyID,
                'employee_id' => $employee->employee_id,
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
                    WorkingDay::createByAttributes([
                        'company_id' => $companyID,
                        'year' => $currentYear,
                        'month' => $currentMonth,
                        'day' => date($dayFormat),
                        'work' => HrActiveRecord::isWeekend($dayNumber) ? 0 : 1,
                        'added_by' => Yii::$app->params['superAdmin']
                    ]);
                } catch (Exception $exc) {
                    $myMes = Yii::t('cmd-hr', 'The working day could not be saved') . "\n";
                    $myMes .= $exc->getMessage();
                    throw new Exception($myMes, $exc->getCode());
                }
            }
        }
    }
}