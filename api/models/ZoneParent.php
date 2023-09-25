<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "zone".
 *
 * @property int $id
 * @property string|null $field
 * @property string|null $label
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property CarZone[] $carZones
 * @property CarZoneHistory[] $carZoneHistories
 * @property ZoneOption[] $zoneOptions
 */
class ZoneParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'zone';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_auto_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'added_by'], 'required'],
            [['added', 'updated'], 'safe'],
            [['field', 'label'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'field' => Yii::t('app', 'Field'),
            'label' => Yii::t('app', 'Label'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[CarZones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarZones()
    {
        return $this->hasMany(CarZone::className(), ['zone_id' => 'id']);
    }

    /**
     * Gets query for [[CarZoneHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarZoneHistories()
    {
        return $this->hasMany(CarZoneHistory::className(), ['zone_id' => 'id']);
    }

    /**
     * Gets query for [[ZoneOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getZoneOptions()
    {
        return $this->hasMany(ZoneOption::className(), ['zone_id' => 'id']);
    }
}
