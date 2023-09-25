<?php

namespace backend\modules\adm\models;

use Yii;

/**
 * This is the model class for table "domain".
 *
 * @property int $id
 * @property string $name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Entity[] $entities
 * @property Subdomain[] $subdomains
 */
class DomainParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain';
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
            [['name', 'added', 'added_by'], 'required'],
            [['deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adm', 'ID'),
            'name' => Yii::t('adm', 'Name'),
            'deleted' => Yii::t('adm', 'Deleted'),
            'added' => Yii::t('adm', 'Added'),
            'added_by' => Yii::t('adm', 'Added By'),
            'updated' => Yii::t('adm', 'Updated'),
            'updated_by' => Yii::t('adm', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Entities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEntities()
    {
        return $this->hasMany(Entity::className(), ['domain_id' => 'id']);
    }

    /**
     * Gets query for [[Subdomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubdomains()
    {
        return $this->hasMany(Subdomain::className(), ['domain_id' => 'id']);
    }
}
