<?php

namespace console\controllers;

use backend\modules\hr\models\EvaluationKpiRaw;
use backend\modules\hr\models\EvaluationRaw;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class HrGenerateEvaluationsController extends Controller
{
    /**
     * @return int|void
     * @throws Exception
     */
    public function actionIndex($companyId, $evaluationMonth = null, $evaluationYear = null)
    {
        $evaluationMonth = EvaluationRaw::setEvaluationMonth($evaluationMonth);
        $evaluationYear = EvaluationRaw::setEvaluationYear($evaluationYear);
        $sql = "SELECT DISTINCT employee_id_evaluated, employee_id_evaluator FROM `eval_kpi_relation` WHERE `deleted` = 0";
        $relations = Yii::$app->get('ecf_hr_db')->createCommand($sql)->queryAll();
        if ($relations === null) {
            echo Yii::t('cmd-hr', 'No relations found') . "\n";
            return ExitCode::DATAERR;
        }

        foreach ($relations as $key => $value) {
            $attributes = [
                'company_id' => $companyId,
                'owner_employee_id' => $value['employee_id_evaluator'],
                'owner_position_internal_id' => null,
                'employee_id' => $value['employee_id_evaluated'],
                'position_internal_id' => null,
                'year' => $evaluationYear,
                'month' => $evaluationMonth
            ];

            $evaluation = EvaluationRaw::getByAttributes($attributes);
            EvaluationKpiRaw::setFinalKpis($evaluation->id, $value['employee_id_evaluated'], $value['employee_id_evaluator']);
        }
    }
}