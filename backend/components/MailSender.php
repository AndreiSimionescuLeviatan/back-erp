<?php

namespace backend\components;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User;
use yii\db\Exception;

/**
 * The class will be used to send the mail in outlook platform
 *
 * @author Calin B.
 * @since 02.06.2022
 */
class MailSender
{
    /**
     * The function for sending mail in outlook platform
     * @param $subject
     * @param $content
     * @param $receiver
     * @return bool
     * @throws Exception
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public static function sendMail($subject, $content, $receiver)
    {
        try {
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
        } catch (GuzzleException $exc) {
            throw new Exception("Get an error trying to validate the email sender credentials. Could not send the email");
        }

        try {
            $user = $graph->createRequest("get", "/me")
                ->addHeaders(array("Content-Type" => "application/json"))
                ->setReturnType(User::class)
                ->setTimeout("100")
                ->execute();
        } catch (GuzzleException $exc) {
            throw new Exception("Get an error trying to validate the email sender credentials. Could not send the email");
        }

        $mailBody = array(
            "Message" => array(
                "subject" => $subject,
                "body" => array(
                    "contentType" => "html",
                    "content" => $content
                ),
                "from" => array(
                    "emailAddress" => array(
                        "name" => $user->getDisplayName(),
                        "address" => $user->getMail()
                    )
                ),
                "toRecipients" => array(
                    array(
                        "emailAddress" => array(
                            "name" => $receiver->fullName(),
                            "address" => $receiver->email
                        )
                    )
                )
            )
        );

        try {
            $email = $graph->createRequest("POST", "/me/sendMail")
                ->attachBody($mailBody)
                ->execute();
            if ((int)$email->getStatus() === 202) {
                return true;
            }
            throw new Exception("Received an unexpected code when sending email. Could not send the email");
        } catch (GuzzleException $exc) {
            throw new Exception("Get an error trying to validate the email receivers credentials. Could not send the email");
        }
    }
}
