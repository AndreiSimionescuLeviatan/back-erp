<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "meeting_room".
 *
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property string $details Details about room equipments and other details
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class MeetingRoomParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_room';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_logistic_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'capacity', 'details', 'added', 'added_by'], 'required'],
            [['capacity', 'added_by', 'updated_by'], 'integer'],
            [['details'], 'string'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-logistic', 'ID'),
            'name' => Yii::t('api-logistic', 'Name'),
            'capacity' => Yii::t('api-logistic', 'Capacity'),
            'details' => Yii::t('api-logistic', 'Details'),
            'added' => Yii::t('api-logistic', 'Added'),
            'added_by' => Yii::t('api-logistic', 'Added By'),
            'updated' => Yii::t('api-logistic', 'Updated'),
            'updated_by' => Yii::t('api-logistic', 'Updated By'),
        ];
    }
}
