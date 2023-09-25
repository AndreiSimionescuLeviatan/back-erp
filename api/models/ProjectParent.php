<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $status
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Product[] $products
 * @property ProjectEmployeePosition[] $projectEmployeePositions
 * @property ProjectSystemDomain[] $projectSystemDomains
 */
class ProjectParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
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
            [['code', 'name', 'added', 'added_by'], 'required'],
            [['status', 'deleted', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['code'], 'string', 'max' => 64],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
            [['name'], 'unique'],
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
            'status' => Yii::t('app', 'Status'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Products]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['project_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectEmployeePositions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectEmployeePositions()
    {
        return $this->hasMany(ProjectEmployeePosition::className(), ['project_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectSystemDomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectSystemDomains()
    {
        return $this->hasMany(ProjectSystemDomain::className(), ['project_id' => 'id']);
    }
}
