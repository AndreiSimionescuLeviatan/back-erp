<?php

namespace api\modules\v1\models;

/**
 * This is the model class that extends the "EvaluationParent" class.
 */
class Evaluation extends EvaluationParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.evaluation';
    }

    /**
     * @var string[]
     */
    public static $statuses = [
        0 => 'not_finished',
        1 => 'in_progress',
        2 => 'finished'
    ];

    /**
     * @param $ownerEmployeeId
     * @return array
     */
    public static function countByStatus($ownerEmployeeId)
    {
        $rows = self::find()
            ->alias('ev')
            ->select('ev.status, COUNT(ev.id) AS total')
            ->rightJoin(Employee::tableName() . ' em', 'ev.employee_id = em.id')
            ->where(['ev.owner_employee_id' => $ownerEmployeeId, 'em.status' => Employee::STATUS_ACTIVE])
            ->groupBy('ev.status')
            ->asArray()
            ->all();

        $evaluations = self::initCountStatuses();

        foreach ($rows as $row) {
            if (!isset($evaluations[$row['status']])) {
                continue;
            }
            $evaluations[$row['status']] = $row['total'];
        }

        return $evaluations;
    }

    /**
     * @return array
     */
    public static function initCountStatuses()
    {
        $evaluations = [];
        foreach (self::$statuses as $key => $value) {
            $evaluations[$key] = 0;
        }
        return $evaluations;
    }

    /**
     * @param $evaluations
     * @param $keysToMerged
     * @param $outKey
     * @return array
     */
    public static function mergeCountByKeys($evaluations, $keysToMerged, $outKey)
    {
        $merged = [];
        foreach ($evaluations as $key => $value) {
            if (!in_array($key, $keysToMerged)) {
                $merged[$key] = $value;
                continue;
            }
            if (!isset($merged[$outKey])) {
                $merged[$outKey] = 0;
            }
            $merged[$outKey] += $value;
        }
        return $merged;
    }

    /**
     * @param $evaluations
     * @return array
     */
    public static function convertStatusIdToName($evaluations)
    {
        $converted = [];
        foreach ($evaluations as $id => $value) {
            if (!isset(self::$statuses[$id])) {
                continue;
            }
            $converted[self::$statuses[$id]] = $value;
        }
        return $converted;
    }

    /**
     * @param $employeeId
     * @return array
     */
    public static function getGradesByMonths($employeeId)
    {
        $gradesByMonths = [];

        $evalMonths = self::find()
            ->select('year, month')
            ->groupBy('year, month')
            ->orderBy('year DESC, month DESC')
            ->all();
        if (empty($evalMonths)) {
            return $gradesByMonths;
        }

        foreach ($evalMonths as $month) {
            $where = [
                'employee_id' => $employeeId,
                'year' => $month['year'],
                'month' => $month['month'],
            ];

            $allEmployeeEvaluations = self::find()->where($where)->count();
            if (empty($allEmployeeEvaluations)) {
                return $gradesByMonths;
            }

            $finishedEmployeeEvaluations = self::find()->where($where)->andWhere(['status' => 2])->count();

            if ($allEmployeeEvaluations === $finishedEmployeeEvaluations) {
                $monthGrade = self::find()
                    ->where($where)
                    ->groupBy('year, month')
                    ->average('grade');

                $gradesByMonths[] = [
                    'year' => $month['year'],
                    'month' => $month['month'],
                    'month_grade' => $monthGrade
                ];
            }
        }

        return array_slice($gradesByMonths, 0, 6);
    }
}
