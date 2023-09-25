<?php

namespace backend\modules\adm\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\validators\EmailValidator;

class Settings extends SettingsParent
{
    /**
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        return ArrayHelper::merge($rules, [
            [['added_by', 'updated_by'], 'safe'],
        ]);
    }

    /**
     * @return mixed|void|null
     */
    public static function getSslDateExpiration()
    {
        $expirationDate = '';
        $dateExpiration = Settings::find()->where(['name' => 'SSL_EXPIRATION_DATE'])->one();
        if (!empty($dateExpiration)) {
            $expirationDate = $dateExpiration->value;
        }
        return $expirationDate;
    }

    /**
     * @return mixed|string|null
     */
    public static function getSslUntilExpiration()
    {
        $expirationUntil = '';
        $sslExpirationUntil = Settings::find()->where(['name' => 'SSL_EXPIRATION_UNTIL'])->one();
        if (!empty($sslExpirationUntil)) {
            $expirationUntil = $sslExpirationUntil->value;
        }
        return $expirationUntil;
    }


    public static function getSslAdminEmailList()
    {
        $emailsList = explode(',', Yii::$app->params['defaultSslAdminEmailsList']);

        $sslAdminEmailsList = Settings::find()->where(['name' => 'SSL_ADMIN'])->asArray()->one();
        if (!empty($sslAdminEmailsList)) {
            $emailsList = explode(',', $sslAdminEmailsList['value']);
        }

        $returnList = [];
        foreach ($emailsList as $email) {
            $email = trim($email);
            $validator = new EmailValidator();
            if (!$validator->validate($email)) {
                continue;
            }
            $returnList[] = $email;
        }

        return $returnList;
    }

    public static function getValue($name, $defaultValue = null)
    {
        $model = self::find()->where('name = :name', [
            ':name' => $name
        ])->one();
        if ($model === null) {
            return $defaultValue;
        }
        return $model->value;
    }

    public static function getIdentityImages()
    {
        $url = Url::home(true);

        if (strpos($url, 'erp-secunet') || strpos($url, 'erp-ghallard')) {
            return [
                'login_image' => '/images/logo-ghallard.png',
                'left_sidebar_image' => '/images/logo-ghallard.png',
                'icon_tab_image' => '/images/favicon-ghallard.png',
                'title' => 'Ghallard'
            ];
        }
        return [
            'login_image' => '/images/logo-leviatan.png',
            'left_sidebar_image' => '/images/logo-leviatan.png',
            'icon_tab_image' => '/images/favicon-leviatan.png',
            'title' => 'Leviatan Design'
        ];
    }

    public static function findOneByAttributes($attributes, $options = [])
    {
        $className = get_called_class();
        $model = new $className();
        $where = '';
        $bind = [];
        foreach ($attributes as $attribute => $value) {
            if (!$model->hasAttribute($attribute)) {
                continue;
            }
            if (!empty($where)) {
                $where .= ' AND ';
            }
            $where .= "{$attribute} = :{$attribute}";
            $bind[":{$attribute}"] = $value;
        }

        $response = null;
        if (!empty($attributes) && empty($where)) {
            return $response;
        }

        $command = self::find()->where($where, $bind);
        if (!empty($options['order'])) {
            $command->orderBy($options['order']);
        }
        if (!empty($options['as_array'])) {
            $command->asArray();
        }

        return $command->one();
    }
}
