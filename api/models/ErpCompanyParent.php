<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "erp_company".
 *
 * @property int $id
 * @property int $company_id
 * @property int $general_manager_id general manager(company administrator) user id taken from user table
 * @property int|null $deputy_general_manager_id company deputy general manager user id taken from user table
 * @property int|null $technical_manager_id company technical manager user id taken from user table
 * @property int|null $executive_manager_id company executive manager user id taken from user table
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $radius
 * @property int $deleted
 */
class ErpCompanyParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'erp_company';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_adm_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'general_manager_id', 'added', 'added_by'], 'required'],
            [['company_id', 'general_manager_id', 'deputy_general_manager_id', 'technical_manager_id', 'executive_manager_id', 'added_by', 'updated_by', 'radius', 'deleted'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['latitude', 'longitude'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-adm', 'ID'),
            'company_id' => Yii::t('api-adm', 'Company ID'),
            'general_manager_id' => Yii::t('api-adm', 'General Manager ID'),
            'deputy_general_manager_id' => Yii::t('api-adm', 'Deputy General Manager ID'),
            'technical_manager_id' => Yii::t('api-adm', 'Technical Manager ID'),
            'executive_manager_id' => Yii::t('api-adm', 'Executive Manager ID'),
            'added' => Yii::t('api-adm', 'Added'),
            'added_by' => Yii::t('api-adm', 'Added By'),
            'updated' => Yii::t('api-adm', 'Updated'),
            'updated_by' => Yii::t('api-adm', 'Updated By'),
            'latitude' => Yii::t('api-adm', 'Latitude'),
            'longitude' => Yii::t('api-adm', 'Longitude'),
            'radius' => Yii::t('api-adm', 'Radius'),
            'deleted' => Yii::t('api-adm', 'Deleted'),
        ];
    }
}
