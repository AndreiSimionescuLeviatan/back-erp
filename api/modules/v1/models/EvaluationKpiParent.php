<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "evaluation_kpi".
 *
 * @property int $id
 * @property int $evaluation_id
 * @property int $kpi_category_id
 * @property int $kpi_id
 * @property float $grade
 * @property int|null $status 0 - new; 1 - answer received
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvaluationKpiParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['evaluation_id', 'kpi_category_id', 'kpi_id', 'deleted', 'added', 'added_by'], 'required'],
            [['evaluation_id', 'kpi_category_id', 'kpi_id', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['grade'], 'number'],
            [['added', 'updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'evaluation_id' => Yii::t('api-hr', 'Evaluation ID'),
            'kpi_category_id' => Yii::t('api-hr', 'Kpi Category ID'),
            'kpi_id' => Yii::t('api-hr', 'Kpi ID'),
            'grade' => Yii::t('api-hr', 'Grade'),
            'status' => Yii::t('api-hr', 'Status'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
