<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "meeting_room".
 *
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property string|null $details Details about room equipments and other details
 * @property string|null $company_ids This col can't be NULL actually but because we can't overwrite the rule in Yii we set him  as with NULL value by default and make sure we set the value when saving in Yii
 * @property string|null $page_class
 * @property string|null $card_bg_class
 * @property string|null $card_btn_class
 * @property string|null $meeting_details_icon_class
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
            [['name', 'capacity', 'added', 'added_by'], 'required'],
            [['capacity', 'added_by', 'updated_by'], 'integer'],
            [['details'], 'string'],
            [['added', 'updated'], 'safe'],
            [['name', 'company_ids', 'page_class', 'card_bg_class', 'card_btn_class', 'meeting_details_icon_class'], 'string', 'max' => 255],
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
            'company_ids' => Yii::t('api-logistic', 'Company Ids'),
            'page_class' => Yii::t('api-logistic', 'Page Class'),
            'card_bg_class' => Yii::t('api-logistic', 'Card Bg Class'),
            'card_btn_class' => Yii::t('api-logistic', 'Card Btn Class'),
            'meeting_details_icon_class' => Yii::t('api-logistic', 'Meeting Details Icon Class'),
            'added' => Yii::t('api-logistic', 'Added'),
            'added_by' => Yii::t('api-logistic', 'Added By'),
            'updated' => Yii::t('api-logistic', 'Updated'),
            'updated_by' => Yii::t('api-logistic', 'Updated By'),
        ];
    }
}
