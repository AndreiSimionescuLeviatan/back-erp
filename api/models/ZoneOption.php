<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "zone_option".
 *
 * @property int $id
 * @property int $zone_id
 * @property int|null $value
 * @property string|null $text
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property CarZone[] $carZones
 * @property Zone $zone
 */
class ZoneOption extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'zone_option';
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
            [['zone_id', 'added', 'added_by'], 'required'],
            [['zone_id', 'value', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['text'], 'string', 'max' => 255],
            [['zone_id'], 'exist', 'skipOnError' => true, 'targetClass' => Zone::className(), 'targetAttribute' => ['zone_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'zone_id' => Yii::t('app', 'Zone ID'),
            'value' => Yii::t('app', 'Value'),
            'text' => Yii::t('app', 'Text'),
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
        return $this->hasMany(CarZone::className(), ['zone_option_id' => 'id']);
    }

    /**
     * Gets query for [[Zone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getZone()
    {
        return $this->hasOne(Zone::className(), ['id' => 'zone_id']);
    }

    /**
     * Gets query for [[Zone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public static function getZoneOption($carZoneOptionApi)
    {
        $carZone = CarZone::find()->where(['zone_id' => $carZoneOptionApi->id])->orderBy(['added' => SORT_DESC])->one();

        $carZoneOption = !empty($carZone) && !empty($carZone->zoneOption) ? $carZone->zoneOption->text : '';

        return $carZoneOption;
    }

    /**
     * @param $carZoneOptionApi
     * @param $selectedStatus
     * @return array
     */
    public static function getZoneOptionValues($carZoneOptionApi, $selectedStatus)
    {
        $carZoneOption = ZoneOption::find()->where(['zone_id' => $carZoneOptionApi])->all();
        $carZone = [];
        foreach ($carZoneOption as $zoneOption) {
            $carZone[] =
                [
                    'id' => $zoneOption->id,
                    'value' => $zoneOption->id,
                    'option' => $zoneOption->text,
                    'selected' => ($selectedStatus == $zoneOption->text && !empty($zoneOption->text)) ? 'selected' : ''
                ];
        }
        return $carZone;
    }
}
