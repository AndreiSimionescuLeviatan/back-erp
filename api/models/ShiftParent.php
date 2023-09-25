<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "shift".
 *
 * @property int $id
 * @property int $company_id
 * @property int $employee_id
 * @property string|null $start_initial
 * @property string|null $stop_initial
 * @property string|null $start_modified
 * @property string|null $stop_modified
 * @property string|null $observations
 * @property int|null $in_location_at_start_initial 0: no; 1: yes
 * @property int|null $in_location_at_stop_initial 0: no; 1: yes
 * @property int $validated 0: no; 1: yes
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 * @property int $deleted
 */
class ShiftParent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shift';
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
            [['company_id', 'employee_id', 'added', 'added_by'], 'required'],
            [['company_id', 'employee_id', 'in_location_at_start_initial', 'in_location_at_stop_initial', 'validated', 'added_by', 'updated_by', 'deleted'], 'integer'],
            [['start_initial', 'stop_initial', 'start_modified', 'stop_modified', 'added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'company_id' => Yii::t('api-hr', 'Company ID'),
            'employee_id' => Yii::t('api-hr', 'Employee ID'),
            'start_initial' => Yii::t('api-hr', 'Start Initial'),
            'stop_initial' => Yii::t('api-hr', 'Stop Initial'),
            'start_modified' => Yii::t('api-hr', 'Start Modified'),
            'stop_modified' => Yii::t('api-hr', 'Stop Modified'),
            'observations' => Yii::t('api-hr', 'Observations'),
            'in_location_at_start_initial' => Yii::t('api-hr', 'In Location At Start Initial'),
            'in_location_at_stop_initial' => Yii::t('api-hr', 'In Location At Stop Initial'),
            'validated' => Yii::t('api-hr', 'Validated'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
            'deleted' => Yii::t('api-hr', 'Deleted'),
        ];
    }
}
