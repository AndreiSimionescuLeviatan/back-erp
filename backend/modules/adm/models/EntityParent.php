<?php

namespace backend\modules\adm\models;

use Yii;

/**
 * This is the model class for table "entity".
 *
 * @property int $id
 * @property int $domain_id
 * @property string $name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Domain $domain
 * @property Subdomain[] $subdomains
 */
class EntityParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'entity';
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
            [['domain_id', 'name', 'added', 'added_by'], 'required'],
            [['domain_id', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm', 'ID'),
            'domain_id' => Yii::t('adm', 'Domain ID'),
            'name' => Yii::t('adm', 'Name'),
            'deleted' => Yii::t('adm', 'Deleted'),
            'added' => Yii::t('adm', 'Added'),
            'added_by' => Yii::t('adm', 'Added By'),
            'updated' => Yii::t('adm', 'Updated'),
            'updated_by' => Yii::t('adm', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Domain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
    }

    /**
     * Gets query for [[Subdomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubdomains()
    {
        return $this->hasMany(Subdomain::className(), ['entity_id' => 'id']);
    }
}
