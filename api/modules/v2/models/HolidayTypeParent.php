<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "holiday_type".
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_id null - without subcategory of holiday, holiday_type id - category of subcategory
 * @property int|null $unit
 * @property int|null $measure_unit 1 - days, 2 - hours
 * @property int|null $recurrence_type 1 - once a year, 2 - once per employee, 3 - unlimited
 * @property int $checked 0 - main category; 1 - secondary category
 * @property int $deleted 0 - no; 1 - yes
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class HolidayTypeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'holiday_type';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_hr_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'added', 'added_by'], 'required'],
            [['parent_id', 'unit', 'measure_unit', 'recurrence_type', 'checked', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'name' => Yii::t('api-hr', 'Name'),
            'parent_id' => Yii::t('api-hr', 'Parent ID'),
            'unit' => Yii::t('api-hr', 'Unit'),
            'measure_unit' => Yii::t('api-hr', 'Measure Unit'),
            'recurrence_type' => Yii::t('api-hr', 'Recurrence Type'),
            'checked' => Yii::t('api-hr', 'Checked'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
