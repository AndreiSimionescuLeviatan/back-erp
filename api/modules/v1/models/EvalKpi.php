<?php

namespace api\modules\v1\models;

/**
 * This is the model class that extends the "EvalKpiParent" class.
 */
class EvalKpi extends EvalKpiParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.eval_kpi';
    }
}
