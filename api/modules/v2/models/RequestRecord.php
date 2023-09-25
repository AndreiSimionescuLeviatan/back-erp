<?php

namespace api\modules\v2\models;

/**
 * @todo generate api models
 */

use backend\modules\adm\models\Settings;
use backend\modules\pmp\models\DeviceToken;
use common\components\FirebasePushNotificationHelper;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "request_record".
 */
class RequestRecord extends RequestRecordParent
{

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['added'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'added_by',
                'updatedByAttribute' => 'updated_by',
            ]
        ];
    }

    /**
     * @param int|null $employeeId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function notifyApprover($employeeId)
    {
        $employee = Employee::find()
            ->where(["id" => $employeeId, "status" => 1])
            ->one();

        if (empty($employee) || empty($employee->user_id)) {
            throw new Exception(Yii::t("api-hr", "The employee that should approve your request don't have a valid ERP account. Please contact an administrator!"));
        }

        $erpFcmNotificationTokenSetting = Settings::findOneByAttributes(['name' => "FIREBASE_TOKEN_HR_APP"]);
        if (empty($erpFcmNotificationTokenSetting) || empty($erpFcmNotificationTokenSetting['value'])) {
            throw new Exception(Yii::t("api-hr", "Notifications settings are wrong. Please contact an administrator!"));
        }
        $fcmToken = $erpFcmNotificationTokenSetting['value'];

        $fcmHelper = new FirebasePushNotificationHelper();
        $fcmHelper->setServerKey($fcmToken);

        $deviceTokenToNotify = DeviceToken::getTokenForNotify($employee->user_id);
        if ($deviceTokenToNotify === null) {
            throw new Exception(Yii::t("api-hr", "The employee that should approve your request don't have a valid device registered in ERP. Please contact an administrator!"));
        }

        $notification = [
            "title" => Yii::t('api-hr', "Human Resources"),
            "body" => Yii::t('api-hr', 'You have new approvals to review')
        ];
        try {
            return $fcmHelper->sendTo($deviceTokenToNotify, $notification);
        } catch (GuzzleException $exc) {
            throw new Exception($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * @param $employeeId
     * @param $status
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function notifyRequester($employeeId, $status)
    {
        $employee = Employee::find()
            ->where(["id" => $employeeId, "status" => 1])
            ->one();

        if (empty($employee) || empty($employee->user_id)) {
            throw new Exception(Yii::t("api-hr", "The employee to whom this approval is addressed does not have an ERP account. Please contact an administrator!"));
        }

        $erpFcmNotificationTokenSetting = Settings::findOneByAttributes(['name' => "FIREBASE_TOKEN_HR_APP"]);
        if (empty($erpFcmNotificationTokenSetting) || empty($erpFcmNotificationTokenSetting['value'])) {
            throw new Exception(Yii::t("api-hr", "Notifications settings are wrong. Please contact an administrator!"));
        }
        $fcmToken = $erpFcmNotificationTokenSetting['value'];

        $fcmHelper = new FirebasePushNotificationHelper();
        $fcmHelper->setServerKey($fcmToken);

        $deviceTokenToNotify = DeviceToken::getTokenForNotify($employee->user_id);
        if ($deviceTokenToNotify === null) {
            throw new Exception(Yii::t("api-hr", "The employee to whom this approval is addressed does not have an valid device registered in ERP. Please contact an administrator!"));
        }

        $notification = [
            "title" => Yii::t('api-hr', "Human Resources"),
            "body" => Yii::t('api-hr', (int)$status === 1 ? 'Your request was approved' : 'Your request was rejected')
        ];
        try {
            return $fcmHelper->sendTo($deviceTokenToNotify, $notification);
        } catch (GuzzleException $exc) {
            throw new Exception($exc->getMessage(), $exc->getCode());
        }
    }

    /**
     * @param RequestRecord $requestRecord
     * @param int $prevLevel
     * @return array
     */
    public static function getNextLevelApproveDetails($requestRecord, $prevLevel)
    {
//        $requester = Employee::findOne($requestRecord->employee_id);
//        $employeeMainCompany = $requester->employeeMainCompany;
//        $headOfficeId = $employeeMainCompany->office['head_of_office'];
//        $headDepartmentId = $employeeMainCompany->department['head_of_department'];
//
//        if (!empty($headOfficeId) && (int)$requester->id === (int)$headOfficeId) {
//            $level = 3;
//            $approver_id = self::getEmployeeMainCompanyTopManagementIds($requester);
//        } elseif (!empty($headDepartmentId) && (int)$requester->id === (int)$headDepartmentId) {
//            $level = 3;
//            $approver_id = self::getEmployeeMainCompanyTopManagementIds($requester);
//        } elseif (!empty($headOfficeId) && $prevLevel === 1) {//if we have a head of office, send to him the request
//            $level = 2;
//            $approver_id = [$headOfficeId];
//        } elseif (!empty($headDepartmentId) && $prevLevel === 1) {//if we have a head of department, send to him the request
//            $level = 2;
//            $approver_id = [$headDepartmentId];
//        } elseif ($employeeMainCompany && $employeeMainCompany->erpCompany && $prevLevel === 2) {
//            $level = 3;
//            $approver_id = self::getEmployeeMainCompanyTopManagementIds($requester);
//        } else {
//            $level = -1;
//            $approver_id = [110];
//        }
//
//        return [
//            'headOfficeId' => $headOfficeId,
//            'headDepartmentId' => $headDepartmentId,
//            'level' => $level,
//            'approver_id' => $approver_id
//        ];

        /**
         * ChatGPT "improved" code V1
         */
//        $requester = Employee::findOne($requestRecord->employee_id);
//        $employeeMainCompany = $requester->employeeMainCompany;
//
//        // In case we don't have a valid requester or employeeMainCompany
//        if (!$requester || !$employeeMainCompany) {
//            throw new \Exception('Invalid requester or employeeMainCompany.');
//        }
//
//        $headOfficeId = $employeeMainCompany->office['head_of_office'];
//        $headDepartmentId = $employeeMainCompany->department['head_of_department'];
//
//        $isHeadOfOffice = !empty($headOfficeId) && (int)$requester->id === (int)$headOfficeId;
//        $isHeadOfDepartment = !empty($headDepartmentId) && (int)$requester->id === (int)$headDepartmentId;
//
//        if ($isHeadOfOffice || $isHeadOfDepartment) {
//            // If the requester is the head of the office or the department, they have a level of 3
//            $level = 3;
//            $approverIds = self::getEmployeeMainCompanyTopManagementIds($requester);
//        } elseif ($prevLevel === 1 && ($isHeadOfOffice || $isHeadOfDepartment)) {
//            // If the previous level was 1 and we have a head of office or department, move the request to them
//            $level = 2;
//            $approverIds = $isHeadOfOffice ? [$headOfficeId] : [$headDepartmentId];
//        } elseif ($employeeMainCompany->erpCompany && $prevLevel === 2) {
//            // If the previous level was 2 and we have an ERP company, move to top management
//            $level = 3;
//            $approverIds = self::getEmployeeMainCompanyTopManagementIds($requester);
//        } else {
//            // If none of the above conditions are met, assign level -1 and approver_id 110
//            $level = -1;
//            $approverIds = [110];
//        }
//
//        return [
//            'headOfficeId' => $headOfficeId,
//            'headDepartmentId' => $headDepartmentId,
//            'level' => $level,
//            'approver_id' => $approverIds
//        ];

        /**
         * ChatGPT improved code V2
         */
        $requester = Employee::findOne($requestRecord->employee_id);
        $employeeMainCompany = $requester->employeeMainCompany;
        $headOfficeId = !empty($employeeMainCompany->office) ? $employeeMainCompany->office['head_of_office'] : null;
        $headDepartmentId = !empty($employeeMainCompany->department) ? $employeeMainCompany->department['head_of_department'] : null;

        $isHeadOffice = !empty($headOfficeId) && (int)$requester->id === (int)$headOfficeId;
        $isHeadDepartment = !empty($headDepartmentId) && (int)$requester->id === (int)$headDepartmentId;
        $isPrevLevelOne = $prevLevel === 1;
        $isPrevLevelTwo = $prevLevel === 2 && $employeeMainCompany && $employeeMainCompany->hrCompany;

        $level = -1;
        $approver_id = [110];//@todo this should be a user from HR department taken from setting or somewhere else

        if ($isHeadOffice || $isHeadDepartment || $isPrevLevelTwo) {
            $level = 3;
            $approver_id = self::getEmployeeMainCompanyTopManagementIds($requester);
        } else if (($isPrevLevelOne && !empty($headOfficeId)) || ($isPrevLevelOne && !empty($headDepartmentId))) {
            $level = 2;
            $approver_id = !empty($headOfficeId) ? [$headOfficeId] : [$headDepartmentId];
        }

        return [
            'headOfficeId' => $headOfficeId,
            'headDepartmentId' => $headDepartmentId,
            'level' => $level,
            'approver_id' => $approver_id
        ];
    }

    /**
     * Private method to be used internal to retrieve top management employee ids as array
     * @param Employee $employee
     * @return array
     */
    public static function getEmployeeMainCompanyTopManagementIds($employee)
    {
        $approver_id = [];
        $topManagementAttributes = ['general_manager_id', 'deputy_general_manager_id', 'technical_manager_id', 'executive_manager_id'];
        foreach ($employee->employeeMainCompany->hrCompany->attributes as $attribute => $value) {
            if (!in_array($attribute, $topManagementAttributes) || empty($value)) {
                continue;
            }
            $approver_id[] = $value;
        }
        return $approver_id;
    }
}
