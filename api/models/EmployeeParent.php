<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string|null $full_name
 * @property string|null $email
 * @property string|null $phone_number
 * @property string $birth_date
 * @property string $identity_card_series
 * @property string $identity_card_number
 * @property int $gender 0 - unknown, 1 - barbat; 2 - femeie
 * @property int $status 0 - inactiv; 1 - activ
 * @property int|null $type 1 - angajat permanent; 2 - colaborator
 * @property string|null $start_schedule
 * @property string|null $stop_schedule
 * @property int|null $holidays
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class EmployeeParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee';
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
            [['user_id', 'gender', 'status', 'type', 'holidays', 'added_by', 'updated_by'], 'integer'],
            [['first_name', 'last_name', 'birth_date', 'added', 'added_by'], 'required'],
            [['birth_date', 'start_schedule', 'stop_schedule', 'added', 'updated'], 'safe'],
            [['first_name', 'middle_name', 'last_name'], 'string', 'max' => 64],
            [['full_name'], 'string', 'max' => 128],
            [['email'], 'string', 'max' => 255],
            [['phone_number'], 'string', 'max' => 32],
            [['identity_card_series'], 'string', 'max' => 2],
            [['identity_card_number'], 'string', 'max' => 6],
            [['email'], 'unique'],
            [['phone_number'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'user_id' => Yii::t('api-hr', 'User ID'),
            'first_name' => Yii::t('api-hr', 'First Name'),
            'middle_name' => Yii::t('api-hr', 'Middle Name'),
            'last_name' => Yii::t('api-hr', 'Last Name'),
            'full_name' => Yii::t('api-hr', 'Full Name'),
            'email' => Yii::t('api-hr', 'Email'),
            'phone_number' => Yii::t('api-hr', 'Phone Number'),
            'birth_date' => Yii::t('api-hr', 'Birth Date'),
            'identity_card_series' => Yii::t('api-hr', 'Identity Card Series'),
            'identity_card_number' => Yii::t('api-hr', 'Identity Card Number'),
            'gender' => Yii::t('api-hr', 'Gender'),
            'status' => Yii::t('api-hr', 'Status'),
            'type' => Yii::t('api-hr', 'Type'),
            'start_schedule' => Yii::t('api-hr', 'Start Schedule'),
            'stop_schedule' => Yii::t('api-hr', 'Stop Schedule'),
            'holidays' => Yii::t('api-hr', 'Holidays'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
