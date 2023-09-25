<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "meeting_room_erp_company".
 *
 * @property int $id
 * @property int $room_id
 * @property int $company_id
 */
class MeetingRoomErpCompanyParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'meeting_room_erp_company';
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
            [['room_id', 'company_id'], 'required'],
            [['room_id', 'company_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-logistic', 'ID'),
            'room_id' => Yii::t('api-logistic', 'Room ID'),
            'company_id' => Yii::t('api-logistic', 'Company ID'),
        ];
    }
}
