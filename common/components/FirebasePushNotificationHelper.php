<?php

namespace common\components;

use Exception;
use GuzzleHttp\Client;

class FirebasePushNotificationHelper
{

    protected $endpoint;
    protected $serverKey;

    public function __construct()
    {
        $this->endpoint = "https://fcm.googleapis.com/fcm/send";
    }

    public function setEndPoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    public function setServerKey($serverKey)
    {
        $this->serverKey = $serverKey;
    }

    /**
     * @param $clientToken
     * @param $notification
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function sendTo($clientToken, $notification)
    {
        $headers = [
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ];
        $fields = [
            'to' => $clientToken,
            'content-available' => true,
            'priority' => 'high',
            'notification' => $notification,
        ];

        $fields = json_encode($fields);

        $client = new Client();
        try {
            return $client->post($this->endpoint, [
                'headers' => $headers,
                "body" => $fields,
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

    }

}