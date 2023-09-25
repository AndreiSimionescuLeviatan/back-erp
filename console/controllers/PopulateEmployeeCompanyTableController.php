<?php

namespace console\controllers;

use backend\modules\hr\models\Employee;
use backend\modules\hr\models\EmployeeCompany;
use Yii;
use yii\console\Controller;

class PopulateEmployeeCompanyTableController extends Controller
{
    public function actionIndex()
    {
        Yii::info("\nPopulate employee_company table is running...", 'populateEmployeeCompanyTable');

        $employees = Employee::getCompanyDetailsForAllEmployees();

        foreach ($employees as $employee) {
            $attributes = [
                'employee_id' => $employee['id'],
                'company_id' => $employee['company_id'],
                'work_location_id' => $employee['work_location_id'],
                'type' => $employee['type'],
                'department_id' => $employee['department_id'],
                'office_id' => $employee['office_id'],
                'position_cor_id' => $employee['position_cor_id'],
                'start_schedule' => $employee['start_schedule'],
                'stop_schedule' => $employee['stop_schedule'],
                'holidays' => $employee['holidays'],
                'health_control' => $employee['health_control'],
                'employment_date' => $employee['employment_date'],
                'added_by' => Yii::$app->params['superAdmin'],
                'deleted' => 0
            ];

            EmployeeCompany::createByAttributes($attributes);
        }
    }
}