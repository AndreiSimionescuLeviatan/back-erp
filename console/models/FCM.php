<?php

namespace console\models;

use GuzzleHttp\Client;

class FCM
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

    public function sendTo($clientToken, $notification)
    {
        $headers = [
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type'  => 'application/json',
        ];
        $fields = [
            'to'=>$clientToken,
            'content-available' => true,
            'priority' => 'high',
            'notification' => $notification,
        ];

        $fields = json_encode ($fields);

        $client = new Client();

        try{
            $request = $client->post($this->endpoint,[
                'headers' => $headers,
                "body" => $fields,
            ]);
            $response = $request->getBody();
            return $response;
        }
        catch (Exception $e){
            return $e;
        }

    }

}