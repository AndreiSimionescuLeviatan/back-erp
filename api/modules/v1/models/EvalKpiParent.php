<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "eval_kpi".
 *
 * @property int $id
 * @property int $category_id
 * @property int $company_id
 * @property string $name
 * @property string|null $description
 * @property int|null $general 0 - nu; 1 - da
 * @property int|null $order_by
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EvalKpiParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'company_id', 'name', 'added', 'added_by'], 'required'],
            [['category_id', 'company_id', 'general', 'order_by', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 700],
            [['description'], 'string', 'max' => 2048],
            [['name', 'category_id', 'company_id'], 'unique', 'targetAttribute' => ['name', 'category_id', 'company_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'category_id' => Yii::t('api-hr', 'Category ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'name' => Yii::t('api-hr', 'Name'),
            'description' => Yii::t('api-hr', 'Description'),
            'general' => Yii::t('api-hr', 'General'),
            'order_by' => Yii::t('api-hr', 'Order By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
