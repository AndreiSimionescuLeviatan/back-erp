<?php

namespace api\modules\v1\models;

use Exception;
use Yii;
use yii\db\StaleObjectException;

/**
 * This is the model class that extends the "EvaluationKpiParent" class.
 */
class EvaluationKpi extends EvaluationKpiParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.evaluation_kpi';
    }

    /**
     * @param $evaluationId
     * @return float
     */
    public static function getGeneralProgress($evaluationId)
    {
        $rows = self::find()->select('status, COUNT(`id`) AS total')
            ->where(['evaluation_id' => $evaluationId])
            ->groupBy('status')
            ->asArray()
            ->all();

        $total = 0;
        $answered = 0;
        foreach ($rows as $row) {
            $total += $row['total'];
            if ($row['status'] == 1) {
                $answered = $row['total'];
            }
        }

        return round($answered / $total * 100, 2);
    }

    /**
     * @param $evaluationId
     * @param $categoryId
     * @return float|string
     */
    public static function getSpecificProgress($evaluationId, $categoryId)
    {
        $rows = self::find()->select('kpi_category_id, status, COUNT(`id`) AS total')
            ->where([
                'evaluation_id' => $evaluationId,
                'kpi_category_id' => $categoryId
            ])->groupBy('kpi_category_id, status')
            ->asArray()
            ->all();

        $total = 0;
        $answered = 0;
        foreach ($rows as $row) {
            $total += $row['total'];
            if ($row['status'] == 1) {
                $answered = $row['total'];
            }
        }
        if ($total == 0) {
            return Yii::t('api-hr', 'This category is not included in this evaluation (team member)');
        } else {
            return round($answered / $total * 100, 2);
        }
    }

    /**
     * @param Evaluation | $evaluation
     * @param array | $evaluationDetails
     * @return void
     * @throws Exception
     */
    public static function saveEvaluationGrade($evaluation, $evaluationDetails)
    {
        $evaluation->grade = !empty($evaluationDetails['evaluation']['grade']) ? $evaluationDetails['evaluation']['grade'] : '0.00';

        if (!$evaluation->save()) {
            throw new Exception(Yii::t('api-hr', 'The evaluation grade could not be saved'));
        }
    }

    /**
     * @param array | $evaluationDetails
     * @return void
     * @throws Exception
     */
    public static function saveEvaluationKpisGrades($evaluationDetails)
    {
        $kpis = $evaluationDetails['kpis'];
        foreach ($kpis as $kpiId => $kpiValues) {
            if (!isset($kpiValues['grade'])) {
                throw new Exception(Yii::t('api-hr', 'The kpi grade is not set'));
            }
            if (!array_key_exists('observations', $kpiValues)) {
                throw new Exception(Yii::t('api-hr', 'The kpi observations are not set'));
            }
            $model = self::findOne($kpiId);
            if ($model === null) {
                throw new Exception(Yii::t('api-hr', 'The kpi id could not be found'), 404);
            }
            $model->setAttributes([
                'grade' => $kpiValues['grade'] > 0 ? $kpiValues['grade'] : 0,
                'status' => $kpiValues['grade'] > 0 ? 1 : 0,
                'observations' => $kpiValues['observations'],
                'updated' => date('Y-m-d H:i:s'),
                'updated_by' => Yii::$app->user->id
            ]);

            if (!$model->save()) {
                throw new Exception(Yii::t('api-hr', 'The kpi could not be saved'));
            }
        }
    }

    /**
     * @param Evaluation | $evaluation
     * @param array | $evaluationDetails
     * @return void
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function saveInEvalEmployeeGradeMonthCategory($evaluation, $evaluationDetails)
    {
        $employeeEvaluations = Evaluation::find()->where([
            'employee_id' => $evaluation->employee_id,
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month'],
        ])->all();
        if (empty($employeeEvaluations)) {
            throw new Exception(Yii::t('api-hr', 'The evaluated employee has no saved evaluations'));
        }

        $employeeEvaluationsIds = [];
        foreach ($employeeEvaluations as $employeeEvaluation) {
            $employeeEvaluationsIds[$employeeEvaluation->id] = $employeeEvaluation->id;
        }

        $employeeKpis = self::find()
            ->where(['in', 'evaluation_id', $employeeEvaluationsIds])
            ->orderBy('kpi_category_id, kpi_id')
            ->all();

        if (empty($employeeKpis)) {
            throw new Exception(Yii::t('api-hr', 'The evaluated employee has no saved evaluation kpis'));
        }

        $evaluationCategories = [];
        foreach ($employeeKpis as $employeeKpi) {
            $evaluationCategories[$employeeKpi->kpi_category_id][$employeeKpi->kpi_id][] = $employeeKpi->grade;
        }

        $gradesByCategoryByRepeatedKpis = [];
        foreach ($evaluationCategories as $evaluationCategoryId => $evaluationCategoryValue) {
            foreach ($evaluationCategoryValue as $kpiId => $kpiValue) {
                $sum = 0;
                $count = 0;
                foreach ($kpiValue as $key => $kpiGrade) {
                    if ($kpiGrade > 0) {
                        $sum += $kpiGrade;
                        $count++;
                    }
                }
                if ($sum > 0 && $count > 0) {
                    $gradesByCategoryByRepeatedKpis[$evaluationCategoryId][$kpiId] = round($sum / $count, 2);
                } else {
                    $gradesByCategoryByRepeatedKpis[$evaluationCategoryId][$kpiId] = null;
                }
            }
        }

        $gradesByCategory = [];
        foreach ($gradesByCategoryByRepeatedKpis as $evaluationCategoryId => $evaluationCategoryValue) {
            $sum = 0;
            $count = 0;
            foreach ($evaluationCategoryValue as $key => $kpiGrade) {
                if ($kpiGrade > 0) {
                    $sum += $kpiGrade;
                    $count++;
                }
            }
            if ($sum > 0 && $count > 0) {
                $gradesByCategory[$evaluationCategoryId] = round($sum / $count, 2);
            } else {
                $gradesByCategory[$evaluationCategoryId] = null;
            }
        }

        $checks = EvalEmployeeGradeMonthCategory::find()->where([
            'employee_id' => $evaluation->employee_id,
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month']
        ])->all();
        if (!empty($checks)) {
            foreach ($checks as $check) {
                $check->delete();
            }
        }

        foreach ($gradesByCategory as $categoryId => $categoryGrade) {
            $category = EvalKpiCategory::find()->where([
                'id' => $categoryId
            ])->one();
            if ($category === null) {
                throw new Exception(Yii::t('api-hr', 'The category id was not found'), 404);
            }

            $evalEmployeeGradeMonthCategory = new EvalEmployeeGradeMonthCategory();

            $evalEmployeeGradeMonthCategory->setAttributes([
                'employee_id' => $evaluation->employee_id,
                'eval_kpi_category_id' => $categoryId,
                'eval_kpi_category_percentage' => $category->percentage,
                'grade' => $categoryGrade,
                'year' => $evaluationDetails['evaluation']['year'],
                'month' => $evaluationDetails['evaluation']['month'],
                'added' => date('Y-m-d H:i:s'),
                'added_by' => Yii::$app->user->id
            ]);

            if (!$evalEmployeeGradeMonthCategory->save()) {
                throw new Exception(Yii::t('api-hr', 'The grades by category could not be saved'));
            }
        }
    }


    /**
     * @param Evaluation | $evaluation
     * @param array | $evaluationDetails
     * @return void
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function saveInEvalEmployeeGradeMonth($evaluation, $evaluationDetails)
    {
        $checks = EvalEmployeeGradeMonth::find()->where([
            'employee_id' => $evaluation->employee_id,
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month']
        ])->all();
        if (!empty($checks)) {
            foreach ($checks as $check) {
                $check->delete();
            }
        }

        $evalEmployeeGradeMonth = new EvalEmployeeGradeMonth();

        $evalEmployeeGradeMonth->setAttributes([
            'employee_id' => $evaluation->employee_id,
            'grade' => self::getEvalEmployeeGradeMonthGrade($evaluation, $evaluationDetails),
            'accuracy' => self::getEvalEmployeeGradeMonthAccuracy($evaluation, $evaluationDetails),
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month'],
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id
        ]);

        if (!$evalEmployeeGradeMonth->save()) {
            throw new Exception(Yii::t('api-hr', 'The monthly grades could not be saved'));
        }
    }

    /**
     * @param Evaluation | $evaluation
     * @param array | $evaluationDetails
     * @return float|int|void
     * @throws Exception
     */
    public static function getEvalEmployeeGradeMonthGrade($evaluation, $evaluationDetails)
    {
        $categories = EvalEmployeeGradeMonthCategory::find()->where([
            'employee_id' => $evaluation->employee_id,
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month']
        ])->all();
        if (empty($categories)) {
            throw new Exception(Yii::t('api-hr', 'There are no saved categories'));
        }

        $dynamicPercentagesSum = 0;
        foreach ($categories as $category) {
            if ($category->grade > 0) {
                $dynamicPercentagesSum += $category->eval_kpi_category_percentage;
            }
        }

        $gradeByMonth = 0;
        $count = 0;
        foreach ($categories as $category) {
            if ($category->grade > 0) {
                $gradeByMonth += $category->grade * $category->eval_kpi_category_percentage / $dynamicPercentagesSum;
                $count++;
            }
        }

        if ($gradeByMonth > 0) {
            return round($gradeByMonth, 2);
        }
        return null;
    }

    /**
     * @param Evaluation | $evaluation
     * @param array | $evaluationDetails
     * @return int|mixed|null
     * @throws Exception
     */
    public static function getEvalEmployeeGradeMonthAccuracy($evaluation, $evaluationDetails)
    {
        $categories = EvalEmployeeGradeMonthCategory::find()->where([
            'employee_id' => $evaluation->employee_id,
            'year' => $evaluationDetails['evaluation']['year'],
            'month' => $evaluationDetails['evaluation']['month']
        ])->all();
        if (empty($categories)) {
            throw new Exception(Yii::t('api-hr', 'There are no saved categories'));
        }

        $percentagesSum = 0;
        foreach ($categories as $category) {
            if ($category->grade > 0) {
                $percentagesSum += $category->eval_kpi_category_percentage;
            }
        }

        return $percentagesSum;
    }

    /**
     * @param Evaluation | $evaluation
     * @return void
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function saveInEvalEmployeeGrade($evaluation)
    {
        $checks = EvalEmployeeGrade::find()->where([
            'employee_id' => $evaluation->employee_id
        ])->all();
        if (!empty($checks)) {
            foreach ($checks as $check) {
                $check->delete();
            }
        }

        $monthlyGrades = EvalEmployeeGradeMonth::find()->where([
            'employee_id' => $evaluation->employee_id
        ])->all();
        if (empty($monthlyGrades)) {
            throw new Exception(Yii::t('api-hr', 'There are no saved monthly grades'));
        }

        $sum = 0;
        $count = 0;
        foreach ($monthlyGrades as $monthlyGrade) {
            if ($monthlyGrade->grade > 0) {
                $sum += $monthlyGrade->grade;
                $count++;
            }
        }

        $evalEmployeeGrade = new EvalEmployeeGrade();

        $evalEmployeeGrade->setAttributes([
            'employee_id' => $evaluation->employee_id,
            'grade' => ($sum > 0 && $count > 0) ? round($sum / $count, 2) : null,
            'added' => date('Y-m-d H:i:s'),
            'added_by' => Yii::$app->user->id
        ]);

        if (!$evalEmployeeGrade->save()) {
            throw new Exception(Yii::t('api-hr', 'The general grades could not be saved'));
        }
    }

    /**
     * @param $evaluation
     * @param $answers
     * @return void
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function saveGrades($evaluation, $answers)
    {
        $evaluationDetails = json_decode($answers, true);

        if (!isset($evaluationDetails['evaluation']['grade'])) {
            throw new Exception(Yii::t('api-hr', 'The evaluation grade is not set'));
        }
        if (!isset($evaluationDetails['evaluation']['year'])) {
            throw new Exception(Yii::t('api-hr', 'The evaluation year is not set'));
        }
        if (!isset($evaluationDetails['evaluation']['month'])) {
            throw new Exception(Yii::t('api-hr', 'The evaluation month is not set'));
        }
        if (!isset($evaluationDetails['evaluation']['status'])) {
            throw new Exception(Yii::t('api-hr', 'The evaluation status is not set'));
        }
        if (!isset($evaluationDetails['kpis'])) {
            throw new Exception(Yii::t('api-hr', 'The evaluation kpis are not set'));
        }
        if (!$evaluation->hasAttribute('grade')) {
            throw new Exception(Yii::t('api-hr', 'The evaluation grade attribute does not exist'));
        }
        if (!$evaluation->hasAttribute('employee_id')) {
            throw new Exception(Yii::t('api-hr', 'The evaluation employee_id attribute does not exist'));
        }

        // save evaluation grade
        self::saveEvaluationGrade($evaluation, $evaluationDetails);

        // save evaluation kpis grades
        self::saveEvaluationKpisGrades($evaluationDetails);

        // save in eval_employee_grade_month_category
        self::saveInEvalEmployeeGradeMonthCategory($evaluation, $evaluationDetails);

        // save in eval_employee_grade_month
        self::saveInEvalEmployeeGradeMonth($evaluation, $evaluationDetails);

        // save in eval_employee_grade
        self::saveInEvalEmployeeGrade($evaluation);

        //save evaluation status
        $evaluation->updateAttributes(['status' => $evaluationDetails['evaluation']['status']]);
        if (!$evaluation->save()) {
            throw new Exception(Yii::t('api-hr', 'The evaluation status could not be saved'), 404);
        }
    }

    /**
     * @param array | $data
     * @return array
     */
    public static function getGradesByCategories($data)
    {
        $gradesByCategories = [];

        $tblEvaluation = Evaluation::tableName();
        $tblEvalKpiCategory = EvalKpiCategory::tableName();
        $tblEvalKpi = EvalKpi::tableName();

        $where = "e.employee_id = {$data['employee_id']} 
                  AND e.year = {$data['year']} 
                  AND e.month = {$data['month']}";

        $kpiCategoriesGrades = self::find()->alias('ek')
            ->select('ek.kpi_category_id, ekc.name AS kpi_category_name, AVG(ek.grade) AS kpi_category_grade')
            ->join('INNER JOIN', "{$tblEvaluation} e", 'ek.evaluation_id = e.id')
            ->join('INNER JOIN', "{$tblEvalKpiCategory} ekc", 'ek.kpi_category_id = ekc.id')
            ->where("$where AND ekc.deleted = 0")
            ->groupBy('ek.kpi_category_id')
            ->asArray()
            ->all();
        if (empty($kpiCategoriesGrades)) {
            return $gradesByCategories;
        }

        foreach ($kpiCategoriesGrades as $kpiCategoryGrade) {
            $kpisGrades = EvaluationKpi::find()->alias('ek')
                ->select('ek.kpi_id, ec.name AS kpi_name, AVG(ek.grade) AS kpi_grade')
                ->join('INNER JOIN', "{$tblEvaluation} e", 'ek.evaluation_id = e.id')
                ->join('INNER JOIN', "{$tblEvalKpi} ec", 'ek.kpi_id = ec.id')
                ->where("$where AND ec.deleted = 0 AND ek.kpi_category_id = {$kpiCategoryGrade['kpi_category_id']}")
                ->groupBy('ek.kpi_id')
                ->asArray()
                ->all();
            if (empty($kpisGrades)) {
                return $gradesByCategories;
            }

            $gradesByCategories[] = [
                'kpi_category_id' => $kpiCategoryGrade['kpi_category_id'],
                'kpi_category_name' => $kpiCategoryGrade['kpi_category_name'],
                'kpi_category_grade' => $kpiCategoryGrade['kpi_category_grade'],
                'kpis' => $kpisGrades
            ];
        }

        return $gradesByCategories;
    }
}
