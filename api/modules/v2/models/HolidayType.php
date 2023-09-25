<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "holiday_type".
 *
 * @property HolidayTypeParent[] $vacationTypeSubcats
 * @property HolidayTypeParent $parent
 */
class HolidayType extends HolidayTypeParent
{

    public $measureUnitName;
    public $recurrenceTypeName;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.holiday_type';
    }

    // filter out some fields, best used when you want to inherit the parent implementation
    // and exclude some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();
        // remove fields that contain sensitive information
        unset($fields['added'], $fields['added_by'], $fields['updated'], $fields['updated_by']);
        return $fields;
    }

    public function extraFields()
    {
        return ['recurrenceTypeName', 'parent'];
    }

    /**
     * Gets query for [[VacationTypeSubcats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVacationTypeSubcats()
    {
        return $this->hasMany(HolidayTypeParent::className(), ['parent_id' => 'id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(HolidayTypeParent::className(), ['id' => 'parent_id']);
    }

    public function getMeasureUnitName($id)
    {
        $measureUnits = [
            1 => Yii::t('api-hr', 'Days'),
            2 => Yii::t('api-hr', 'Hours')
        ];
        $this->measureUnitName = array_key_exists($this->measure_unit, $measureUnits) ? $measureUnits[$this->measure_unit] : null;
    }

    public function getRecurrenceTypeName()
    {
        $recurrenceType = [
            1 => Yii::t('api-hr', 'Year'),
            2 => Yii::t('api-hr', 'Once')
        ];
        $this->recurrenceTypeName = array_key_exists($this->recurrence_type, $recurrenceType) ? $recurrenceType[$this->recurrence_type] : null;
    }
}
