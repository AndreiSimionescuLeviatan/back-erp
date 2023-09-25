<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "position".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $deleted
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 *
 * @property Employee[] $employees
 * @property EmployeePosition[] $employeePositions
 * @property ProjectEmployeePosition[] $projectEmployeePositions
 */
class PositionParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'position';
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
            [['deleted', 'added_by', 'updated_by'], 'integer'],
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
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[Employees]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->hasMany(Employee::className(), ['position_id' => 'id']);
    }

    /**
     * Gets query for [[EmployeePositions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeePositions()
    {
        return $this->hasMany(EmployeePosition::className(), ['position_id' => 'id']);
    }

    /**
     * Gets query for [[ProjectEmployeePositions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectEmployeePositions()
    {
        return $this->hasMany(ProjectEmployeePosition::className(), ['position_id' => 'id']);
    }
}
