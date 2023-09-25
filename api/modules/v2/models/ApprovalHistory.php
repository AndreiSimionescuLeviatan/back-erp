<?php

namespace api\modules\v2\models;

use yii\helpers\ArrayHelper;

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
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['approver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['approver_id' => 'id']],
            [['request_record_id'], 'exist', 'skipOnError' => true, 'targetClass' => RequestRecord::className(), 'targetAttribute' => ['request_record_id' => 'id']],
        ]);
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

    public static function getApprovalRequests($employee_id)
    {
        $approvals = [
            'waiting_counter' => 0,
            'waiting' => [],
            'approved' => [],
            'rejected' => [],
        ];

        $approvalsRequests = ApprovalHistory::find()
            ->where([
                'approver_id' => $employee_id,
            ])
            ->asArray()
            ->all();
        foreach ($approvalsRequests as $request) {
            //0: waiting; 1: approved; 2: rejected
            if ((int)$request['status'] === 0) {
                $approvals['waiting'][] = $request;
                $approvals['waiting_counter']++;
            } elseif ((int)$request['status'] === 1) {
                $approvals['approved'][] = $request;
            } elseif ((int)$request['status'] === 2) {
                $approvals['rejected'][] = $request;
            }
        }
        return $approvals;
    }
}
