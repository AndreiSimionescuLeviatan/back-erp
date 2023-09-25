<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "work_location".
 *
 * @property int $id
 * @property string $name
 * @property int $company_id
 * @property string|null $address
 * @property int|null $type 1 - sediu social; 2 - birou principal; 3 - locație de muncă
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $radius
 * @property string|null $start
 * @property string|null $stop
 * @property int|null $perimeter_shape_id 0 - cerc; 1 - poligon
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class WorkLocationParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_location';
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
            [['company_id', 'type', 'radius', 'perimeter_shape_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['start', 'stop', 'added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['address'], 'string', 'max' => 255],
            [['name', 'company_id'], 'unique', 'targetAttribute' => ['name', 'company_id']]
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
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'address' => Yii::t('api-hr', 'Address'),
            'type' => Yii::t('api-hr', 'Type'),
            'latitude' => Yii::t('api-hr', 'Latitude'),
            'longitude' => Yii::t('api-hr', 'Longitude'),
            'radius' => Yii::t('api-hr', 'Radius'),
            'start' => Yii::t('api-hr', 'Start'),
            'stop' => Yii::t('api-hr', 'Stop'),
            'perimeter_shape_id' => Yii::t('api-hr', 'Perimeter Shape ID'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
