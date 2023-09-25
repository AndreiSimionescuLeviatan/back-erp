<?php

namespace backend\modules\crm\models;

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
 * @property string|null $last_seen
 * @property int $status 0 - inactive, 1 - active, 2 - dead
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property ContractOffer[] $contractOffers
 * @property IbanCompany[] $ibanCompanies
 */
class CompanyParent extends CrmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'cui', 'reg_number', 'added', 'added_by'], 'required'],
            [['country_id', 'state_id', 'city_id', 'tva', 'status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['last_seen', 'added', 'updated'], 'safe'],
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
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('crm', 'Code'),
            'name' => Yii::t('crm', 'Name'),
            'short_name' => Yii::t('crm', 'Short Name'),
            'cui' => Yii::t('crm', 'Cui'),
            'reg_number' => Yii::t('crm', 'Reg Number'),
            'country_id' => Yii::t('crm', 'Country ID'),
            'state_id' => Yii::t('crm', 'State ID'),
            'city_id' => Yii::t('crm', 'City ID'),
            'address' => Yii::t('crm', 'Address'),
            'tva' => Yii::t('crm', 'Tva'),
            'last_seen' => Yii::t('crm', 'Last Seen'),
            'status' => Yii::t('crm', 'Status'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[ContractOffers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContractOffers()
    {
        return $this->hasMany(ContractOffer::className(), ['company_id' => 'id']);
    }

    /**
     * Gets query for [[IbanCompanies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIbanCompanies()
    {
        return $this->hasMany(IbanCompany::className(), ['company_id' => 'id']);
    }
}
