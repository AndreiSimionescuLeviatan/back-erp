<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "work_location_polygon".
 *
 * @property int $id
 * @property int $work_location_id
 * @property float $latitude
 * @property float $longitude
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 */
class WorkLocationPolygonParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'work_location_polygon';
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
            [['work_location_id', 'latitude', 'longitude', 'added', 'added_by'], 'required'],
            [['work_location_id', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['added', 'updated'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'work_location_id' => Yii::t('api-hr', 'Work Location ID'),
            'latitude' => Yii::t('api-hr', 'Latitude'),
            'longitude' => Yii::t('api-hr', 'Longitude'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
        ];
    }
}
