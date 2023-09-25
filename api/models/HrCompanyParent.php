<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $short_name
 * @property string $cui
 * @property string $reg_number
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $address
 * @property int|null $tva
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class HrCompanyParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
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
            [['code', 'name', 'cui', 'reg_number', 'added', 'added_by'], 'required'],
            [['country_id', 'state_id', 'city_id', 'tva', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code', 'name', 'short_name', 'cui', 'reg_number', 'address'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'code' => Yii::t('api-hr', 'Code'),
            'name' => Yii::t('api-hr', 'Name'),
            'short_name' => Yii::t('api-hr', 'Short Name'),
            'cui' => Yii::t('api-hr', 'Cui'),
            'reg_number' => Yii::t('api-hr', 'Reg Number'),
            'country_id' => Yii::t('api-hr', 'Country ID'),
            'state_id' => Yii::t('api-hr', 'State ID'),
            'city_id' => Yii::t('api-hr', 'City ID'),
            'address' => Yii::t('api-hr', 'Address'),
            'tva' => Yii::t('api-hr', 'Tva'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
