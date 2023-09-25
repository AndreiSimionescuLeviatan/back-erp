<?php

namespace common\components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Yii;
use yii\web\BadRequestHttpException;

class MicrosoftGraphMailHelper
{
    /**
     * @var string
     */
    public $subject;
    public $content;
    public $toRecipients;
    public $ccRecipients = [];
    public $sender = [];
    public $attachments = [];

    /**
     * Function to send email
     * @return string
     * @throws BadRequestHttpException
     */

    public function sendEmail()
    {
        try {
            $this->validateEmailFields();
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
            $user_token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => CLIENT_ID,
                    'client_secret' => CLIENT_SECRET,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'password',
                    'username' => SHARE_POINT_USERNAME,
                    'password' => SHARE_POINT_PASS,
                ],
            ])->getBody()->getContents());
            $user_accessToken = $user_token->access_token;
            $graph = new Graph();
            $graph->setAccessToken($user_accessToken);

            $user = $graph->createRequest("get", "/me")
                ->addHeaders(array("Content-Type" => "application/json"))
                ->setReturnType(Model\User::class)
                ->setTimeout("100")
                ->execute();
            $mailBody = array(
                "Message" => array(
                    "subject" => $this->subject,
                    "body" => $this->content,
                    "from" => array(
                        "emailAddress" => array(
                            "name" => $user->getDisplayName(),
                            "address" => $user->getMail()
                        )
                    ),
                    "toRecipients" => $this->toRecipients,
                    "ccRecipients" => $this->ccRecipients,
                    "attachments" => $this->attachments
                )
            );
            $graph->createRequest("POST", "/me/sendMail")
                ->attachBody($mailBody)
                ->execute();
        } catch (BadRequestHttpException | GraphException | GuzzleException $exc) {
            throw new BadRequestHttpException($exc->getMessage());
        }
        return Yii::t('app', "Email sent successfully");
    }

    /**
     * Function to validate email fields
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateEmailFields()
    {
        try {
            $this->validateSubject();
            $this->validateContent();
            $this->validateToRecipients();
            $this->validateCcRecipients();
            $this->validateAttachments();
        } catch (BadRequestHttpException $exc) {
            throw new BadRequestHttpException($exc->getMessage());
        }
    }

    /**
     * Function to validate subject
     * @return void
     * @throws BadRequestHttpException
     * @author Daniel L.
     */
    private function validateSubject()
    {
        if (empty($this->subject)) {
            throw new BadRequestHttpException(Yii::t('app', "No data for subject"));
        }
    }

    /**
     * Function to validate content
     * @return void
     * @throws BadRequestHttpException
     * @author Daniel L.
     */
    private function validateContent()
    {
        if (empty($this->content['contentType']) || empty($this->content['content'])) {
            throw new BadRequestHttpException(Yii::t('app', "No data for content"));
        }
    }

    /**
     * Function to validate toRecipients
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateToRecipients()
    {
        if (empty($this->toRecipients)) {
            throw new BadRequestHttpException(Yii::t('app', "You need to include toRecipients"));
        }
        for ($i = 0; $i < count($this->toRecipients); $i++) {
            if (empty($this->toRecipients[$i]['emailAddress']) || empty($this->toRecipients[$i]['emailAddress']['address'])) {
                throw new BadRequestHttpException(Yii::t('app', "No data for to recipients"));
            }
        }
    }

    /**
     * Function to validate ccRecipients
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateCcRecipients()
    {
        for ($i = 0; $i < count($this->ccRecipients); $i++) {
            if (empty($this->ccRecipients[$i]['emailAddress']) || empty($this->ccRecipients[$i]['emailAddress']['address'])) {
                throw new BadRequestHttpException(Yii::t('app', "No data for carbon copy recipients"));
            }
        }
    }

    /**
     * Function to validate attachments
     * @return void
     * @throws BadRequestHttpException
     */
    private function validateAttachments()
    {
        for ($i = 0; $i < count($this->attachments); $i++) {
            if (empty($this->attachments[$i]['name']) || empty($this->attachments[$i]['contentType']) || empty($this->attachments[$i]['contentBytes'] || $this->attachments[$i]['contentBytes'] == false)) {
                $this->attachments[]['@odata.type'] = "#microsoft.graph.fileAttachment";
                throw new BadRequestHttpException(Yii::t('app', "No valid response received from server. Please contact an administrator!"));
            }
        }
    }

}