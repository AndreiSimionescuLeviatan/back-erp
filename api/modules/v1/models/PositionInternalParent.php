<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "position_internal".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $company_id
 * @property string|null $inherit_positions
 * @property int $order_by
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class PositionInternalParent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'company_id', 'order_by', 'added', 'added_by'], 'required'],
            [['company_id', 'order_by', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code', 'name', 'inherit_positions'], 'string', 'max' => 255],
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
            'id' => Yii::t('hr', 'ID'),
            'code' => Yii::t('hr', 'Code'),
            'name' => Yii::t('hr', 'Name'),
            'description' => Yii::t('hr', 'Description'),
            'company_id' => Yii::t('hr', 'Company ID'),
            'inherit_positions' => Yii::t('hr', 'Inherit Positions'),
            'order_by' => Yii::t('hr', 'Order By'),
            'deleted' => Yii::t('hr', 'Deleted'),
            'added' => Yii::t('hr', 'Added'),
            'added_by' => Yii::t('hr', 'Added By'),
            'updated' => Yii::t('hr', 'Updated'),
            'updated_by' => Yii::t('hr', 'Updated By'),
        ];
    }
}
