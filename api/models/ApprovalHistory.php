<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "approval_history".
 *
 * @property Employee $approver
 * @property RequestRecord $requestRecord
 */
class ApprovalHistory extends ApprovalHistoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.approval_history';
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
            [['approver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['approver_id' => 'id']],
            [['request_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => RequestRecord::className(), 'targetAttribute' => ['request_record_id' => 'id']],
        ];
    }

    /**
     * Gets query for [[Approver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApprover()
    {
        return $this->hasOne(Employee::className(), ['id' => 'approver_id']);
    }

    /**
     * Gets query for [[RequestRecord]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequestRecord()
    {
        return $this->hasOne(RequestRecord::className(), ['id' => 'request_record_id']);
    }
}
