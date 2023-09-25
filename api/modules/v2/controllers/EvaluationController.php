<?php

namespace api\modules\v2\controllers;

class EvaluationController extends \api\modules\v1\controllers\EvaluationController
{
    public $modelClass = 'api\modules\v1\models\Evaluation';

//    /**
//     * @return array|mixed
//     * @todo mode this method to user or other place to make her reusable
//     * added the time off approvals request also in here and is not part of the evaluation
//     * in feature this can also be extended with other counters/notifications
//     */
//    public function actionCount()
//    {
//        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
//        if (empty($employeeId)) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'User id not found');
//            return $this->return;
//        }
//
//        $evaluations = Evaluation::countByStatus($employeeId);
//        $evaluations = Evaluation::mergeCountByKeys($evaluations, [0, 1], 0);
//
//        $this->return['evaluations'] = Evaluation::convertStatusIdToName($evaluations);
//        $this->return['approval_requests'] = ApprovalHistory::getApprovalRequests($employeeId);
//
//        $this->return['status'] = 200;
//        Yii::$app->response->statusCode = $this->return['status'];
//        $this->return['message'] = Yii::t('api-hr', 'Successfully sent evaluations count');
//        return $this->return;
//    }
//
//    /**
//     * @return array|mixed
//     */
//    public function actionEmployees()
//    {
//        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
//
//        $rows = Evaluation::find()->select('employee_id, COUNT(employee_id) AS total')
//            ->where(['owner_employee_id' => $employeeId])
//            ->groupBy('employee_id')
//            ->orderBy('total DESC')
//            ->asArray()
//            ->all();
//        $orderedEmployees = [];
//        foreach ($rows as $row) {
//            $orderedEmployees[] = $row['employee_id'];
//        }
//
//        $rows = Evaluation::find()->select('employee_id, status, COUNT(employee_id) AS total')
//            ->where(['owner_employee_id' => $employeeId])
//            ->groupBy('employee_id, status')
//            ->orderBy('status, total DESC')
//            ->asArray()
//            ->all();
//
//        $employees = [];
//        foreach ($rows as $row) {
//            if (!isset($employees[$row['employee_id']])) {
//                $employees[$row['employee_id']] = [
//                    'finished' => 0,
//                    'not_finished' => 0
//                ];
//            }
//            if ($row['status'] == 0 || $row['status'] == 1) {
//                $employees[$row['employee_id']]['not_finished'] += $row['total'];
//            }
//            if ($row['status'] == 2) {
//                $employees[$row['employee_id']]['finished'] += $row['total'];
//            }
//        }
//
//        $this->return['employees_details'] = [];
//
//        foreach ($orderedEmployees as $employeeID) {
//            $this->return['employees_order'][] = $employeeID;
//
//            $employee = Employee::find()->where([
//                'id' => $employeeID,
//                ])->one();
//
//            if(
//                empty($employee->employeeMainCompany)
//                || empty($employee->employeeMainCompany->company)
//                || empty($employee->department)
//                || empty($employee->positionCor)
//            ) {
//                continue;
//            }
//
//            $employeeCompany = $employee->employeeMainCompany->company;
//
//            $this->return['employees_details'][$employeeID] = [
//                'first_name' => $employee->first_name,
//                'last_name' => $employee->last_name,
//                'company_name' => !empty($employee->employeeMainCompany->company_id) ? $employeeCompany->name : null,
//                'department_name' => !empty($employee->department_id) ? $employee->department->name : null,
//                'office_name' => !empty($employee->office_id) ? $employee->office->name : null,
//                'position_name' => !empty($employee->position_cor_id) ? $employee->positionCor->name : null,
//                'photo' => User::getUserImage($employee->user_id),
//                'evaluations' => [
//                    'finished' => $employees[$employeeID]['finished'],
//                    'not_finished' => $employees[$employeeID]['not_finished']
//                ]
//            ];
//        }
//
//        $this->return['status'] = 200;
//        Yii::$app->response->statusCode = $this->return['status'];
//        $this->return['message'] = Yii::t('api-hr', 'Successfully sent employees evaluations');
//        return $this->return;
//    }
//
//    /**
//     * @return array|mixed
//     */
//    public function actionFindAll()
//    {
//        $post = Yii::$app->request->post();
//        if (empty($post['employee'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No employee identifier received');
//            return $this->return;
//        }
//
//        $employee = Employee::find()->where('id = :id', [
//            ':id' => $post['employee']
//        ])->one();
//        if ($employee === null) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
//            return $this->return;
//        }
//
//        if (empty($post['status'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No status received');
//            return $this->return;
//        }
//        if (!in_array($post['status'], ['finished', 'not_finished'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Wrong status received');
//            return $this->return;
//        }
//
//        $employeeId = Employee::getEmployeeId(Yii::$app->user->id);
//
//        $query = Evaluation::find();
//        $query->where('owner_employee_id = ' . $employeeId);
//        $query->andWhere('employee_id = :employee_id', [':employee_id' => $employee->id]);
//        if ($post['status'] == 'finished') {
//            $query->andWhere('status = 2');
//        } elseif ($post['status'] == 'not_finished') {
//            $query->andWhere('status = 0 OR status = 1');
//        }
//        $query->orderBy('added ASC');
//
//        $evaluations = $query->all();
//        if (empty($evaluations)) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluation found');
//            return $this->return;
//        }
//
//        $_evaluationsIDs = $_evaluations = [];
//        foreach ($evaluations as $evaluation) {
//            $_evaluationsIDs[] = $evaluation->id;
//            $_evaluations[$evaluation->id] = [
//                "month" => $evaluation->month,
//                "year" => $evaluation->year,
//                "name" => Yii::t('api-hr', 'Monthly evaluation'),
//                "type" => Yii::t('api-hr', 'Monthly evaluation'),
//                "progress" => EvaluationKpi::getGeneralProgress($evaluation->id),
//                "grade" => !empty($evaluation->grade) ? $evaluation->grade : '0.00',
//                "status" => $post['status']
//            ];
//        }
//
//        $this->return['evaluations'] = $_evaluationsIDs;
//        $this->return['evaluations_details'] = $_evaluations;
//        $this->return['status'] = 200;
//        Yii::$app->response->statusCode = $this->return['status'];
//        $this->return['message'] = Yii::t('api-hr', 'Successfully sent evaluations details');
//        return $this->return;
//    }
//
//    /**
//     * @return array|mixed
//     */
//    public function actionFind()
//    {
//        $post = Yii::$app->request->post();
//
//        if (empty($post['evaluation'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluation identifier received');
//            return $this->return;
//        }
//        $evaluation = Evaluation::find()->where('id = :id', [
//            ':id' => $post['evaluation']
//        ])->one();
//        if ($evaluation === null) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Evaluation not found');
//            return $this->return;
//        }
//        $this->return['evaluation'] = [
//            "month" => $evaluation->month,
//            "year" => $evaluation->year,
//            "name" => Yii::t('api-hr', 'Monthly evaluation'),
//            "type" => Yii::t('api-hr', 'Monthly evaluation'),
//            "progress" => EvaluationKpi::getGeneralProgress($evaluation->id),
//            "grade" => !empty($evaluation->grade) ? $evaluation->grade : '0.00',
//            "status" => ($evaluation->status == 0 || $evaluation->status == 1) ? 'not_finished' : 'finished'
//        ];
//
//        $evaluationKpis = EvaluationKpi::find()->where('evaluation_id = :evaluation_id', [
//            ':evaluation_id' => $evaluation->id
//        ])->orderBy('added ASC')->all();
//        if (empty($evaluationKpis)) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluations found');
//            return $this->return;
//        }
//
//        $categories = EvalKpiCategory::find()->where('deleted = 0')->orderBy('order_by ASC')->all();
//        if (empty($categories)) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No kpis categories found');
//            return $this->return;
//        }
//
//        $this->return['kpis_chapters'] = [];
//        $this->return['kpis'] = [];
//        $this->return['answers'] = [];
//
//        foreach ($categories as $category) {
//            $_category = [
//                'id' => $category->id,
//                'name' => $category->name,
//                'percentage' => $category->percentage,
//                'progress' => EvaluationKpi::getSpecificProgress($evaluation->id, $category->id),
//                'kpis' => []
//            ];
//            foreach ($evaluationKpis as $evaluationKpi) {
//                if ($evaluationKpi->kpi_category_id == $category->id) {
//                    $_category['kpis'][] = $evaluationKpi->id;
//                    $kpiModel = EvalKpi::find()->where('id = :id', [':id' => $evaluationKpi->kpi_id])->one();
//                    $this->return['kpis'][$evaluationKpi->id] = [
//                        'question' => $kpiModel->name,
//                        'typeInputObject' => 'radioRating',
//                        'grade' => !empty($evaluationKpi->grade) ? $evaluationKpi->grade : '0.00',
//                        'observations' => $evaluationKpi->observations,
//                    ];
//                    $this->return['answers'][$evaluationKpi->id] = !empty($evaluationKpi->grade) ? $evaluationKpi->grade : '0.00';
//                }
//            }
//            $this->return['kpis_chapters'][] = $_category;
//        }
//        $this->return['status'] = 200;
//        Yii::$app->response->statusCode = $this->return['status'];
//        $this->return['message'] = Yii::t('api-hr', 'Successfully sent evaluation details');
//        return $this->return;
//    }
//
//    /**
//     * @return array|mixed|void
//     * @throws \Throwable
//     */
//    public function actionSave()
//    {
//        $post = Yii::$app->request->post('evaluation');
//        if (empty($post)) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluation received');
//            return $this->return;
//        }
//        if (empty($post['evaluation'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluation identifier received');
//            return $this->return;
//        }
//
//        $evaluation = Evaluation::find()->where('id = :id', [
//            ':id' => $post['evaluation']
//        ])->one();
//        if ($evaluation === null) {
//            $this->return['status'] = 404;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Evaluation not found');
//            return $this->return;
//        }
//        if (empty($post['answers'])) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'No evaluation answers received');
//            return $this->return;
//        }
//
//        $answers = base64_decode($post['answers']);
//        if (!$answers) {
//            $this->return['status'] = 400;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Wrong evaluation answers received');
//            return $this->return;
//        }
//
//        try {
//            EvaluationKpi::saveGrades($evaluation, $answers);
//            $this->return['status'] = 200;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Successfully saved kpis and evaluation status');
//            return $this->return;
//        } catch (Exception $e) {
//            echo $e->getMessage();
//        }
//    }
//
//    /**
//     * @return array|mixed|void
//     */
//    public function actionGradesByMonths()
//    {
//        $userId = Yii::$app->request->get('user_id');
//        if (empty($userId)) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'User id not received');
//            return $this->return;
//        }
//
//        $employee = Employee::findOne(['user_id' => $userId]);
//        if ($employee === null) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
//            return $this->return;
//        }
//
//        try {
//            $gradesByMonths = Evaluation::getGradesByMonths($employee['id']);
//            $this->return['grades_by_months'] = $gradesByMonths;
//            $this->return['status'] = HttpStatus::OK;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Successfully sent employee grades by months');
//            return $this->return;
//        } catch (Exception $e) {
//            echo $e->getMessage();
//        }
//    }
//
//    /**
//     * @return array|mixed|void
//     */
//    public function actionGradesByCategories()
//    {
//        $userId = Yii::$app->request->get('user_id');
//        if (empty($userId)) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'User id not received');
//            return $this->return;
//        }
//
//        $year = Yii::$app->request->get('year');
//        if (empty($year)) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Year not received');
//            return $this->return;
//        }
//
//        $month = Yii::$app->request->get('month');
//        if (empty($month)) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Month not received');
//            return $this->return;
//        }
//
//        $employee = Employee::findOne(['user_id' => $userId]);
//        if ($employee === null) {
//            $this->return['status'] = HttpStatus::NOT_FOUND;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Employee not found');
//            return $this->return;
//        }
//
//        $data = [
//            'employee_id' => $employee['id'],
//            'year' => $year,
//            'month' => $month
//        ];
//
//        try {
//            $gradesByCategories = EvaluationKpi::getGradesByCategories($data);
//            $this->return['grades_by_categories'] = $gradesByCategories;
//            if (empty($gradesByCategories)) {
//                $this->return['status'] = HttpStatus::NOT_FOUND;
//                Yii::$app->response->statusCode = $this->return['status'];
//                $this->return['message'] = Yii::t('api-hr', 'Employee monthly grades by categories not found');
//                return $this->return;
//            }
//
//            $this->return['status'] = HttpStatus::OK;
//            Yii::$app->response->statusCode = $this->return['status'];
//            $this->return['message'] = Yii::t('api-hr', 'Successfully sent employee monthly grades by categories');
//            return $this->return;
//        } catch (Exception $e) {
//            echo $e->getMessage();
//        }
//    }
}