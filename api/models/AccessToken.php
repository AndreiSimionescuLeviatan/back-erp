<?php

namespace api\models;


use Yii;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * AccessToken Class for access_token table.
 * This is class to manage access_token than will be used in UserIdentity Class
 * UserIdentity class will find any token that active at current date and give Authorization based on access_token status
 * Inspired from @url https://github.com/hoaaah/yii2-rest-api-template
 */
class AccessToken extends AccessTokenParent
{
    public $tokenExpiration = 60 * 24 * 365; // in seconds

    /**
     * @return string
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_INITIAL . '.access_token';
    }

    /**
     * Generate new access_token that will be used at Authorization
     *
     * @param User $user
     * @return void
     * @throws ServerErrorHttpException
     * @throws HttpException
     */
    public static function generateAuthKey($user)
    {
        $accessToken = new AccessToken();
        $accessToken->user_id = $user->id;
        $accessToken->token = $user->auth_key;
        $accessToken->last_used_at = date('Y-m-d H:i:s');
        $accessToken->expire_at = $accessToken->tokenExpiration + strtotime($accessToken->last_used_at);
        $accessToken->added = date('Y-m-d H:i:s');

        if (!$accessToken->save()) {
            if ($accessToken->hasErrors()) {
                foreach ($accessToken->errors as $error) {
                    throw new ServerErrorHttpException($error[0]);
                }
            }
            throw  new ServerErrorHttpException(Yii::t('app', 'Could not generate new token. Please contact an administrator!'));
        }
    }

    /**
     * Make all user token based on any user_id expired
     *
     * @param $userId
     * @return void
     */
    public static function makeAllUserTokenExpiredByUserId($userId)
    {
        AccessToken::updateAll(['expire_at' => strtotime("now")], ['user_id' => $userId]);
    }

    /**
     * Expire any access_token
     * @return bool
     */
    public function expireThisToken()
    {
        $this->expire_at = strtotime("now");
        return $this->save();
    }


    /**
     * Make all user token based on any user_id expired
     *
     * @param $userId
     * @return void
     */
    public function extendUserTokenExpireTime($userId)
    {

        /**
         * $accessToken->expire_at = $accessToken->tokenExpiration + strtotime($accessToken->last_used_at);
         * $accessToken->added = date('Y-m-d H:i:s');
         */
        AccessToken::updateAll([
            'last_used_at' => date('Y-m-d H:i:s'),
            'expire_at' => $this->tokenExpiration + strtotime("now"),
            'updated' => date('Y-m-d H:i:s')
        ], ['user_id' => $this->user_id]);
    }
}