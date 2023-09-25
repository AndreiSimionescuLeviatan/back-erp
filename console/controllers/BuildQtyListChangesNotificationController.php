<?php

namespace console\controllers;

use backend\components\MailSender;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\build\models\QuantityList;
use backend\modules\build\models\QuantityListChanges;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Url;

class BuildQtyListChangesNotificationController extends Controller
{
    /**
     * @var int
     * The delay that the notifications should be taken from DB.
     * The default value is 3600 seconds(1hour) witch means that the query that retrieves the changes
     * will include only changes from last hour
     */
    public $delay = 3600;//default delay is 1 hour in seconds

    /**
     * @var string
     * The speciality code to be used in query ex: 'A' for architecture.
     * Will not use the id because it could change over time
     */
    public $specialityCode;
    protected $changesListData = [];
    protected $successNotification = [];
    protected $failedNotification = [];

    /**
     * @param $actionID
     * @return string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'delay',
            'specialityCode'
        ]);
    }

    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->setViewPath('@app/mail');
        Yii::info("\nQuantity list changes notification cron service is running...", 'buildQtyChangesNotifications');
        $now = time();
        $changesStartTime = date('Y-m-d H:i:s', ($now - $this->delay));
        $changesStopTime = date('Y-m-d H:i:s', $now);

        $receiversList = ["florin.fotciuc@leviatan.ro", "laura.popovici@leviatan.ro"];

        //verificare daca suntem in timpul programului, daca nu suntem nu trimitem nici o notificare
        /**
         * start the url creation here because we only need to overwrite the
         * quantityListId & QuantityListChangesSearch['added']
         */
        $urlRoute[2] = $urlRoute[3] = [
            'build/quantity-list-changes/view',
            'quantityListId' => null,
            'QuantityListChangesSearch' => [
                'added' => null
            ]
        ];

        $qtyListChanges = QuantityListChanges::find()
            ->alias('changes')
            ->select([
                "changes.*",
                "proj.id AS project_id",
                "proj.name AS project_name",
                "spec.id AS speciality_id",
                "spec.name AS speciality_name",
                "obj.id AS object_id",
                "obj.name AS object_name",
            ])
            ->where("(changes.status = 2 OR changes.status = 3) AND changes.notification_sent = 0 AND changes.added >= '{$changesStartTime}'")
            ->orderBy('changes.status ASC')
            ->joinWith(['quantityList', 'quantityList.project proj', 'quantityList.object obj', 'quantityList.speciality spec']);
        if (!empty($this->specialityCode)) {//check if we have an exact speciality that we want the changes for
            $qtyListChanges->andWhere(['spec.code' => $this->specialityCode]);
        }
        $changes = $qtyListChanges->asArray()->all();
        if (empty($changes)) {
            Yii::debug("No qty list changes made from {$changesStartTime} to {$changesStopTime}", 'buildQtyChangesNotifications');
            return ExitCode::OK;
        }

        $this->changesListData = [];
        $this->changesListData[2] = [];
        $this->changesListData[3] = [];
        foreach ($changes as $change) {
            $qtyListId[$change['status']] = $change['quantity_list_id'];
            $projectId[$change['status']] = $change['project_id'];
            $projectName[$change['status']] = $change['project_name'];
            $specialityId[$change['status']] = $change['speciality_id'];
            $specialityName[$change['status']] = $change['speciality_name'];
            $objectId[$change['status']] = $change['object_id'];
            $objectName[$change['status']] = $change['object_name'];

            if (array_key_exists($projectId[$change['status']] . $specialityId[$change['status']] . $objectId[$change['status']], $this->changesListData[$change['status']])) {
                continue;
            }

            $urlRoute[$change['status']]['quantityListId'] = $qtyListId[$change['status']];
            $urlRoute[$change['status']]['QuantityListChangesSearch']['added'] = "{$changesStartTime} - {$changesStopTime}";
            $urlRoute[$change['status']]['QuantityListChangesSearch']['status'] = $change['status'];

            $this->changesListData[$change['status']][$projectId[$change['status']] . $specialityId[$change['status']] . $objectId[$change['status']]] = [
                'href' => Url::toRoute($urlRoute[$change['status']]),
                'text' => "{$projectName[$change['status']]} {$objectName[$change['status']]} {$specialityName[$change['status']]}",
            ];
        }

        if (!empty($this->changesListData)) {
            foreach ($this->changesListData as $key => $changesListData) {
                if (empty($changesListData)) {
                    continue;
                }
                try {
                    QuantityList::setQtyStatusNames();
                    $receiversList = QuantityList::getReceiversEmailsByStatus($key, true);
                    $notificationToSend = count($receiversList);

                    if (!empty($receiversList)) {
                        foreach ($receiversList as $receiverMail) {
                            $receiver = User::findByEmail($receiverMail);
                            if ($receiver === null) {
                                continue;
                            }
                            $name = QuantityList::$qtyStatusNames[$key];
                            $subject = Yii::t('app', 'List of quantities with status "{name}" modified in the last hour', ['name' => $name]);

                            $mailBody = $this->renderPartial('build-qty-list-changes-notification-html', [
                                'receiver' => $receiver,
                                'changesList' => $changesListData,
                            ]);

                            Yii::info("Sending notification to {$receiver->email}...", 'buildQtyChangesNotifications');

                            $sendNotification = MailSender::sendMail($subject, $mailBody, $receiver);

                            if ($sendNotification) {
                                //update quantity_list_changes.notification_sent to 1
                                $notificationToSend--;
                                Yii::debug("Sent to {$receiver->email}.", 'buildQtyChangesNotifications');
                            } else {
                                //update quantity_list_changes.notification_sent to 2
                                Yii::error("ERROR WHEN SENDING NOTIFICATION", 'buildQtyChangesNotifications');
                            }
                        }
                    }
                } catch (\Exception $exc) {
                    Yii::error("Authenticating user error: {$exc->getMessage()}", 'buildQtyChangesNotifications');
                    Yii::error("Error Code: {$exc->getCode()}", 'buildQtyChangesNotifications');
                }
            }
        }

        $notificationToSend === 0 ?
            Yii::debug("All notifications sent", 'buildQtyChangesNotifications') :
            Yii::warning("A number of {$notificationToSend} could not be sent! ERROR: Change list data or receives list is wrong!", 'buildQtyChangesNotifications');
        return $notificationToSend === 0 ? ExitCode::OK : ExitCode::PROTOCOL;
    }



    // Commented until Cornel E. will see it and confirm that we can remove this code
    // Commented by Calin B. on 21.07.2022 as I created a class MailSender in backend/components that replaces bellow code
    // The class was created to access it multiple times in multiple controllers
//    /**
//     * @param $content
//     * @param User $receiver
//     * @return bool
//     * @throws GraphException
//     */
//    protected function sendGraphMail($content, $receiver)
//    {
//        try {
//            $guzzle = new Client();
//            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
//            $user_token = json_decode($guzzle->post($url, [
//                'form_params' => [
//                    'client_id' => CLIENT_ID,
//                    'client_secret' => CLIENT_SECRET,
//                    'resource' => 'https://graph.microsoft.com/',
//                    'grant_type' => 'password',
//                    'username' => SHARE_POINT_USERNAME,
//                    'password' => SHARE_POINT_PASS,
//                ],
//            ])->getBody()->getContents());
//            $user_accessToken = $user_token->access_token;
//            $graph = new Graph();
//            $graph->setAccessToken($user_accessToken);
//            Yii::info("Received token from GRAPH.", 'qtyChangesNotifications');
//        } catch (GuzzleException $exc) {
//            Yii::error("Authenticating user error: {$exc->getMessage()}.", 'qtyChangesNotifications');
//            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
//            return false;
//        }
//
//        try {
//            $user = $graph->createRequest("get", "/me")
//                ->addHeaders(array("Content-Type" => "application/json"))
//                ->setReturnType(\Microsoft\Graph\Model\User::class)
//                ->setTimeout("100")
//                ->execute();
//            Yii::debug("AUTOMATE is now authenticated on GRAPH.", 'qtyChangesNotifications');
//        } catch (GuzzleException $exc) {
//            Yii::error("Authenticating AUTOMATE error: {$exc->getMessage()}.", 'qtyChangesNotifications');
//            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
//            return false;
//        }
//
//        $mailBody = array(
//            "Message" => array(
//                "subject" => "Liste cantități modificate în ultima ora",
//                "body" => array(
//                    "contentType" => "html",
//                    "content" => $content
//                ),
//                "from" => array(
//                    "emailAddress" => array(
//                        "name" => $user->getDisplayName(),
//                        "address" => $user->getMail()
//                    )
//                ),
//                "toRecipients" => array(
//                    array(
//                        "emailAddress" => array(
//                            "name" => $receiver->fullName(),
//                            "address" => $receiver->email
//
//                        )
//                    )
//                )
//            )
//        );
//
//        try {
//            $email = $graph->createRequest("POST", "/me/sendMail")
//                ->attachBody($mailBody)
//                ->execute();
//            if ((int)$email->getStatus() === 202) {
//                Yii::debug("Notified the user '{$receiver->email}", 'qtyChangesNotifications');
//                return true;
//            }
//            Yii::error("Received an unexpected code '{$email->getStatus()}' when sending email", 'qtyChangesNotifications');
//            return false;
//        } catch (GuzzleException $exc) {
//            Yii::error("Authenticating user error: {$exc->getMessage()}.", 'qtyChangesNotifications');
//            Yii::error("Error Code: {$exc->getCode()}", 'qtyChangesNotifications');
//            return false;
//        }
//    }
}