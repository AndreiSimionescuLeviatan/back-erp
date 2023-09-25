<?php

namespace api\modules\v2\models;

use Yii;

/**
 * This is the model class for table "approval_history".
 *
 * @property int $id
 * @property int $request_record_id
 * @property int $progress 0: default; 1: approved/disapproved
 * @property int $status 0: waiting; 1: approved; 2: rejected
 * @property int $level 1:level 1 (the person who takes over the duties); 2: level 2 (head office); 3: level 3 (head department); 4: level 4 (CEO); ...
 * @property int $approver_id the employee id who approved/rejected the request
 * @property string|null $observations the observations added by the employee when the request was approved/disapproved
 * @property string $added
 * @property int $added_by
 * @property string|null $updated
 * @property int|null $updated_by
 */
class ApprovalHistoryParent extends \api\modules\v1\models\ApprovalHistory
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'approval_history';
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
            [['request_record_id', 'level', 'approver_id', 'added', 'added_by'], 'required'],
            [['request_record_id', 'progress', 'status', 'level', 'approver_id', 'added_by', 'updated_by'], 'integer'],
            [['added', 'updated'], 'safe'],
            [['observations'], 'string', 'max' => 512],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('api-hr', 'ID'),
            'request_record_id' => Yii::t('api-hr', 'Request Record ID'),
            'progress' => Yii::t('api-hr', 'Progress'),
            'status' => Yii::t('api-hr', 'Status'),
            'level' => Yii::t('api-hr', 'Level'),
            'approver_id' => Yii::t('api-hr', 'Approver ID'),
            'observations' => Yii::t('api-hr', 'Observations'),
            'added' => Yii::t('api-hr', 'Added'),
            'added_by' => Yii::t('api-hr', 'Added By'),
            'updated' => Yii::t('api-hr', 'Updated'),
            'updated_by' => Yii::t('api-hr', 'Updated By'),
        ];
    }
}
