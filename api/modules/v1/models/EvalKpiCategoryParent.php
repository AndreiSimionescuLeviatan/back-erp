<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "eval_kpi_category".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int $company_id
 * @property string $name
 * @property string|null $description
 * @property float $percentage
 * @property int $order_by
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvalKpiCategoryParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'company_id', 'order_by', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['company_id', 'name', 'percentage', 'order_by', 'added', 'added_by'], 'required'],
            [['percentage'], 'number'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1024],
            [['name', 'company_id'], 'unique', 'targetAttribute' => ['name', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'parent_id' => Yii::t('api-hr', 'Parent ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'name' => Yii::t('api-hr', 'Name'),
            'description' => Yii::t('api-hr', 'Description'),
            'percentage' => Yii::t('api-hr', 'Percentage'),
            'order_by' => Yii::t('api-hr', 'Order By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
