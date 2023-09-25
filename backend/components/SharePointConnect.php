<?php

namespace backend\components;

use GuzzleHttp\Client;
use Yii;
use yii\base\BaseObject;

class SharePointConnect extends BaseObject
{
    public $domainUrl = 'sites/leviatandesign.sharepoint.com:';
    public $siteId;

    public function __construct($param1, $param2, $config = [])
    {
        // ... initialization before configuration is applied

        parent::__construct($config);

    }

    public function init()
    {
        parent::init();
        \Yii::$app->session->set('accessTokenss', 'accessToken');
        // ... initialization after configuration is applied
//        VarDumper::dump(\Yii::$app->user, 10, 1);
    }

    public function getLabel()
    {
        return $this->siteId;
    }
}

class TokenCache
{
    public function storeTokens($accessToken, $user)
    {
        Yii::$app->session->set('accessToken', $accessToken->getToken());
        Yii::$app->session->set('refreshToken', $accessToken->getRefreshToken());
        Yii::$app->session->set('tokenExpires', $accessToken->getExpires());
        Yii::$app->session->set('userName', $user->getDisplayName());
        Yii::$app->session->set('userEmail', null !== $user->getMail() ? $user->getMail() : $user->getUserPrincipalName());
        Yii::$app->session->set('userTimeZone', $user->getMailboxSettings()->getTimeZone());
    }

    public function clearTokens()
    {
        session()->forget('accessToken');
        session()->forget('refreshToken');
        session()->forget('tokenExpires');
        session()->forget('userName');
        session()->forget('userEmail');
        session()->forget('userTimeZone');
    }

    public function getAccessToken()
    {
        // Check if tokens exist
        if (empty(Yii::$app->session->get('accessToken')) ||
            empty(Yii::$app->session->get('refreshToken')) ||
            empty(Yii::$app->session->get('tokenExpires'))) {
            return '';
        }

        // Check if token is expired
        //Get current time + 5 minutes (to allow for time differences)
        $now = time() + 300;
        if (Yii::$app->session->get('tokenExpires') <= $now) {
            // Token is expired (or very close to it)
            // so let's refresh

            // Initialize the OAuth client

            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
            $oauthClient = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => CLIENT_ID,
                    'client_secret' => CLIENT_SECRET,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'password',
                    'username' => SHARE_POINT_USERNAME,
                    'password' => SHARE_POINT_PASS,
                ],
            ])->getBody()->getContents());
//            $user_accessToken = $user_token->access_token;

//            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
//                'clientId' => config('azure.appId'),
//                'clientSecret' => config('azure.appSecret'),
//                'redirectUri' => config('azure.redirectUri'),
//                'urlAuthorize' => config('azure.authority') . config('azure.authorizeEndpoint'),
//                'urlAccessToken' => config('azure.authority') . config('azure.tokenEndpoint'),
//                'urlResourceOwnerDetails' => '',
//                'scopes' => config('azure.scopes')
//            ]);

            try {
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => session('refreshToken')
                ]);

                // Store the new values
                $this->updateTokens($newToken);

                return $newToken->getToken();
            } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                return '';
            }
        }

        // Token is still valid, just return it
        return session('accessToken');
    }

    public function updateTokens($accessToken, $user)
    {
        Yii::$app->session->set('accessToken', $accessToken->getToken());
        Yii::$app->session->set('refreshToken', $accessToken->getRefreshToken());
        Yii::$app->session->set('tokenExpires', $accessToken->getExpires());
    }
}