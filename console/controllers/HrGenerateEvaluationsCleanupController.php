<?php

namespace console\controllers;

use backend\modules\hr\models\Evaluation;
use backend\modules\hr\models\EvaluationKpi;
use backend\modules\hr\models\EvaluationKpiRaw;
use backend\modules\hr\models\EvaluationRaw;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class HrGenerateEvaluationsCleanupController extends Controller
{
    public function actionIndex($companyId, $evaluationMonth = null, $evaluationYear = null)
    {
        // evaluation_raw:
        // POPULARE OPERATION

        $evaluationMonth = EvaluationRaw::setEvaluationMonth($evaluationMonth);
        $evaluationYear = EvaluationRaw::setEvaluationYear($evaluationYear);
;
        $evaluationsRawNew = EvaluationRaw::find()->where('company_id = :company_id
        AND year = :year
        AND month = :month
        AND operation IS NULL', [
            ':company_id' => $companyId,
            ':year' => $evaluationYear,
            ':month' => $evaluationMonth,
        ])->all();
        $evaluationsByEmployeeByEvaluation = [];
        $evaluationsToMerged = [];
        foreach ($evaluationsRawNew as $evaluationRawNew) {
            // setăm toate evaluările 0 = unice, ulterior valoare se va suprascrie dacă e cazul
            $evaluationRawNew->operation = 0;
            if(!$evaluationRawNew->save()){
                echo $evaluationRawNew->errors;
                echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                return ExitCode::DATAERR;
            }
            if ($evaluationRawNew->owner_employee_id == $evaluationRawNew->employee_id) {
                // setăm autoevaluările = 1 și trecem peste
                $evaluationRawNew->operation = 1;
                if(!$evaluationRawNew->save()){
                    echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                    return ExitCode::DATAERR;
                }
                continue;
            }
            // setăm combinațiile de owner - employee - evaluations pe perechi
            $evaluationsByEmployeeByEvaluation[$evaluationRawNew->owner_employee_id][$evaluationRawNew->employee_id][] = $evaluationRawNew->id;
            foreach ($evaluationsByEmployeeByEvaluation as $owner => $byEmployee) {
                foreach ($byEmployee as $employee => $byEvaluation) {
                    if (count($byEvaluation) > 1) {
                        $evaluationsToMerged[$owner . '_' . $employee] = $byEvaluation;
                    }
                }
            }
        }
        // pentru perechile identificate setăm noile evaluări raw (cele merged_into)
        foreach ($evaluationsToMerged as $pairOwnerEmployee => $byEvaluation) {
            $owner = strtok($pairOwnerEmployee, '_');
            $employee = strtok('_');
            $model = new EvaluationRaw();
            $data = ['company_id' => $companyId,
                'owner_employee_id' => $owner,
                'employee_id' => $employee,
                'year' => $evaluationYear,
                'month' => $evaluationMonth];
            $model->setAttributes($data);
            // setăm evaluarea părinte = 3
            $model->operation = 3;
            $model->grade = 0;
            $model->added = date('Y-m-d H:i:s');
            $model->added_by = Yii::$app->params['superAdmin'];
            if(!$model->save()){
                echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                return ExitCode::DATAERR;
            }
            foreach ($byEvaluation as $key => $evaluationId) {
                $oldEVal = EvaluationRaw::find()->where('id = :id', [':id' => $evaluationId])->one();
                if (!empty($oldEVal)) {
                    // setăm evaluările care urmează a fi merge-uite = 2
                    $oldEVal->operation = 2;
                    $oldEVal->merged_into = $model->id;
                    if(!$oldEVal->save()){
                        echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                        return ExitCode::DATAERR;
                    }
                }
            }
        }

        // EVALUATION
        // din evaluation_raw, luăm înregistrările cu operation = 0 (unice) ȘI operation = 3 (părinte)
        // de păstrat id-urile evaluărilor din raw
        $evaluationsUniqueAndParent = EvaluationRaw::find()->where('company_id = :company_id
        AND year = :year
        AND month = :month
        AND (operation = 0 OR operation = 3)', [
            ':company_id' => $companyId,
            ':year' => $evaluationYear,
            ':month' => $evaluationMonth,
        ])->all();
        foreach ($evaluationsUniqueAndParent as $evaluationUniqueAndParent) {
            $evaluationIdExists = Evaluation::find()->where('id = :id', [
                ':id' => $evaluationUniqueAndParent->id
            ])->one();
            if (empty($evaluationIdExists)){
                $data = $evaluationUniqueAndParent->getAttributes([
                    'company_id',
                    'owner_employee_id',
                    'employee_id',
                    'year',
                    'month'
                ]);
                $model = new Evaluation();
                $model->id = $evaluationUniqueAndParent->id;
                $model->setAttributes($data);
                $model->status = 0;
                $model->grade = 0;
                $model->added = date('Y-m-d H:i:s');
                $model->added_by = Yii::$app->params['superAdmin'];
                if(!$model->save()){
                    echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                    return ExitCode::DATAERR;
                }
            }
        }

        // EVALUATION_KPI
        // evaluation_raw cu operation = 0 => în evaluation_kpi_raw: caut id-ul evaluării și iau toți kpis
        // evaluation_raw cu operation = 1 => ignore
        // evaluation_raw cu operation = 2 => în evaluation_kpi_raw: caut id-ul evaluării și iau toți kpis
        // pe care îi trec cu evaluation_id din merged_into
        // evaluation_raw cu operation = 3 => ignore
        $evaluationsRaw = EvaluationRaw::find()->where('company_id = :company_id
        AND year = :year
        AND month = :month', [
            ':company_id' => $companyId,
            ':year' => $evaluationYear,
            ':month' => $evaluationMonth,
        ])->all();
        $mergedEvaluations = [];
        foreach ($evaluationsRaw as $evaluationRaw) {
            //KPIS operation 0
            if ($evaluationRaw->operation == 0) {
                $kpiForUniqueExists = EvaluationKpi::find()->where('evaluation_id = :evaluation_id', [
                    ':evaluation_id' => $evaluationRaw->id
                ])->all();
                if (empty($kpiForUniqueExists)){
                    $kpisRawForUnique = EvaluationKpiRaw::find()->where('evaluation_id = :evaluation_id', [
                        ':evaluation_id' => $evaluationRaw->id
                    ])->all();
                    foreach ($kpisRawForUnique as $kpiRawForNull) {
                        $data = $kpiRawForNull->getAttributes([
                            'kpi_category_id',
                            'kpi_id',
                            'grade',
                            'status',
                            'deleted'
                        ]);
                        $model = new EvaluationKpi();
                        $model->evaluation_id = $kpiRawForNull->evaluation_id;
                        $model->setAttributes($data);
                        $model->grade = 0;
                        $model->added = date('Y-m-d H:i:s');
                        $model->added_by = Yii::$app->params['superAdmin'];
                        if(!$model->save()){
                            echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                            return ExitCode::DATAERR;
                        }
                    }
                }
            }
            // KPIS TO MERGED operation 2
            if ($evaluationRaw->operation == 2) {
                $kpiForMergedExists = EvaluationKpi::find()->where('evaluation_id = :evaluation_id', [
                    ':evaluation_id' => $evaluationRaw->merged_into
                ])->all();
                if (empty($kpiForMergedExists)){
                    $mergedEvaluations[$evaluationRaw->merged_into][] = $evaluationRaw->id;
                }
            }
        }

        // evaluation_kpi:
        // eliminare duplicate kpi și salvare
        foreach ($mergedEvaluations as $mergedId => $mergedEvaluation) {
            $sql = 'SELECT * FROM `evaluation_kpi_raw` WHERE `evaluation_id` IN'
                . ' (' . implode(',', $mergedEvaluation) . ') '
                . 'GROUP BY `kpi_category_id`, `kpi_id`;';
            $evaluationsKpisRaw = EvaluationKpiRaw::findBySql($sql)->all();
            foreach ($evaluationsKpisRaw as $evaluationKpiRaw) {
                $data = $evaluationKpiRaw->getAttributes([
                    'kpi_category_id',
                    'kpi_id',
                    'grade',
                    'status',
                    'deleted'
                ]);
                $model = new EvaluationKpi();
                $model->evaluation_id = $mergedId;
                $model->setAttributes($data);
                $model->grade = 0;
                $model->added = date('Y-m-d H:i:s');
                $model->added_by = Yii::$app->params['superAdmin'];
                if(!$model->save()){
                    echo Yii::t('cmd-hr', 'The data could not be saved') . "\n";
                    return ExitCode::DATAERR;
                }
            }
        }
    }
}