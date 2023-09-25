<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "environment".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $domain_id
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Device[] $devices
 * @property ProductHistory[] $productHistories
 */
class EnvironmentParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'environment';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_pmp_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'domain_id', 'added', 'added_by'], 'required'],
            [['domain_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'name' => Yii::t('app', 'Name'),
            'domain_id' => Yii::t('app', 'Domain ID'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Devices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDevices()
    {
        return $this->hasMany(Device::className(), ['environment_id' => 'id']);
    }

    /**
     * Gets query for [[ProductHistories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductHistories()
    {
        return $this->hasMany(ProductHistory::className(), ['environment_id' => 'id']);
    }
}
