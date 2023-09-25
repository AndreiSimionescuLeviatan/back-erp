<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "car_operation".
 *
 * @property int $id
 * @property int $user_id check-in/check-out user_id
 * @property int $car_id
 * @property int $operation_type_id 1 - check-in; 2- - check-out; 3 - adaugare bon;
 * @property string|null $pdf_name
 * @property string|null $empowering_name
 * @property string $added check-in-time/check-out-time
 * @property int $added_by
 *
 * @property Car $car
 */
class CarOperationParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_operation';
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
            [['user_id', 'car_id', 'added', 'added_by'], 'required'],
            [['user_id', 'car_id', 'operation_type_id', 'added_by'], 'integer'],
            [['added'], 'safe'],
            [['pdf_name', 'empowering_name'], 'string', 'max' => 255],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('auto', 'User ID'),
            'car_id' => Yii::t('auto', 'Car ID'),
            'operation_type_id' => Yii::t('auto', 'Operation Type ID'),
            'pdf_name' => Yii::t('auto', 'Pdf Name'),
            'empowering_name' => Yii::t('auto', 'Empowering Name'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
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
}
