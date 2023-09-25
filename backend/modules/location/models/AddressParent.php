<?php

namespace backend\modules\location\models;

use Yii;

/**
 * This is the model class for table "address".
 *
 * @property int $id
 * @property int $street_id
 * @property string|null $number
 * @property string|null $block
 * @property string|null $scale
 * @property string|null $floor
 * @property string|null $apartment
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class AddressParent extends LocationActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'address';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_location_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['street_id', 'added', 'added_by'], 'required'],
            [['street_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['number', 'block', 'scale', 'floor', 'apartment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'street_id' => Yii::t('app', 'Street ID'),
            'number' => Yii::t('app', 'Number'),
            'block' => Yii::t('app', 'Block'),
            'scale' => Yii::t('app', 'Scale'),
            'floor' => Yii::t('app', 'Floor'),
            'apartment' => Yii::t('app', 'Apartment'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }
}
