<?php

namespace api\models;

use backend\modules\auto\models\AutoActiveRecord;
use Yii;

/**
 * This is the model class for table "journey".
 *
 * @property int $id
 * @property int $car_id
 * @property int $start_hotspot_id
 * @property int $stop_hotspot_id
 * @property float|null $distance
 * @property float|null $fuel
 * @property float|null $odo
 * @property string $started
 * @property string $stopped
 * @property int|null $time
 * @property int|null $stand_time
 * @property int|null $exploit
 * @property int|null $speed
 * @property float|null $mark
 * @property int $status 0 - nevalidata; 1 - validata
 * @property int $user_id
 * @property int|null $project_id proiectul pe baza caruia s-a efectuat calatoria
 * @property int|null $type 1 - Administrativ / 2 - Serviciu
 * @property int|null $merged_with_id
 * @property string|null $observation
 * @property int|null $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Car $car
 * @property Location $startHotspot
 * @property Location $stopHotspot
 */
class JourneyParent extends AutoActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'journey';
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
            [['car_id', 'start_hotspot_id', 'stop_hotspot_id', 'started', 'stopped', 'user_id', 'added', 'added_by'], 'required'],
            [['car_id', 'start_hotspot_id', 'stop_hotspot_id', 'time', 'stand_time', 'exploit', 'speed', 'status', 'user_id', 'project_id', 'type', 'merged_with_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['distance', 'fuel', 'odo', 'mark'], 'number'],
            [['started', 'stopped', 'added', 'updated'], 'safe'],
            [['observation'], 'string'],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
            [['start_hotspot_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::className(), 'targetAttribute' => ['start_hotspot_id' => 'id']],
            [['stop_hotspot_id'], 'exist', 'skipOnError' => true, 'targetClass' => Location::className(), 'targetAttribute' => ['stop_hotspot_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'start_hotspot_id' => Yii::t('app', 'Start Hotspot ID'),
            'stop_hotspot_id' => Yii::t('app', 'Stop Hotspot ID'),
            'distance' => Yii::t('app', 'Distance'),
            'fuel' => Yii::t('app', 'Fuel'),
            'odo' => Yii::t('app', 'Odo'),
            'started' => Yii::t('app', 'Started'),
            'stopped' => Yii::t('app', 'Stopped'),
            'time' => Yii::t('app', 'Time'),
            'stand_time' => Yii::t('app', 'Stand Time'),
            'exploit' => Yii::t('app', 'Exploit'),
            'speed' => Yii::t('app', 'Speed'),
            'mark' => Yii::t('app', 'Mark'),
            'status' => Yii::t('app', 'Status'),
            'user_id' => Yii::t('app', 'User ID'),
            'project_id' => Yii::t('app', 'Project ID'),
            'type' => Yii::t('app', 'Type'),
            'merged_with_id' => Yii::t('app', 'Merged with id'),
            'observation' => Yii::t('app', 'Observation'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Car]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

    /**
     * Gets query for [[StartHotspot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStartHotspot()
    {
        return $this->hasOne(Location::className(), ['id' => 'start_hotspot_id']);
    }

    /**
     * Gets query for [[StopHotspot]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStopHotspot()
    {
        return $this->hasOne(Location::className(), ['id' => 'stop_hotspot_id']);
    }
}
