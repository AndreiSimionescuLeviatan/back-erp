<?php

namespace api\models;

use backend\modules\auto\models\AutoActiveRecord;
use Yii;

/**
 * This is the model class for table "accessory".
 *
 * @property int $id
 * @property string $name
 * @property int $default_qty
 * @property string $measure_unit_name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class AccessoryParent extends AutoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accessory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'default_qty', 'measure_unit_name', 'added', 'added_by'], 'required'],
            [['default_qty', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name', 'measure_unit_name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('auto', 'ID'),
            'name' => Yii::t('auto', 'Name'),
            'default_qty' => Yii::t('auto', 'Default Qty'),
            'measure_unit_name' => Yii::t('auto', 'Measure Unit Name'),
            'deleted' => Yii::t('auto', 'Deleted'),
            'added' => Yii::t('auto', 'Added'),
            'added_by' => Yii::t('auto', 'Added By'),
            'updated' => Yii::t('auto', 'Updated'),
            'updated_by' => Yii::t('auto', 'Updated By'),
        ];
    }
}
