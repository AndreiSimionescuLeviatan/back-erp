<?php

namespace api\models;

use backend\modules\pmp\models\Product;
use Yii;

/**
 * @property Session[] $sessions
 */
class Device extends DeviceParent
{
    public $token = '';
    public static $releasesDir = '/var/www/ecf-erp/backend/upload/products-releases/';
    public static $tmpDownloadsDir = '/var/www/ecf-erp/api/web/files/';
    public static $downloadURL = 'files/';

    public static $authMethods = [
        'device_details' => 'The device will send hardware and software details',
        'token' => 'The application will be already authenticated and will send the active token'
    ];

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['owner_id'], 'default', 'value' => null, 'on' => 'create'],
            [['owner_id'], 'required', 'on' => 'update'],
        ]);
    }

    public static function register($input, $retries = 100)
    {
        if ($retries <= 0) {
            return null;
        }

        if (empty($input['device_type']) || empty(ProductType::find()->where(['deleted' => 0, 'id' => $input['device_type']])->one())) {
            return null;
        }

        $product = Product::find()->where("product_type_id = {$input['device_type']}")->one();
        if ($product === null) {
            return null;
        }

        $uuid = self::generateUUID();

        $device = self::find()->where("uuid = '{$uuid}'")->one();
        if ($device !== null) {
            $retries--;
            return self::register($input, $retries);
        }

        $device = new Device();
        $device->product_type_id = $input['device_type'];
        $device->product_version = $product->current_version;
        $device->uuid = $uuid;
        $device->added = date('Y-m-d H:i:s');
        $device->added_by = User::getAPIUserID();
        $device->last_seen = date('Y-m-d H:i:s');
        $device->first_seen = date('Y-m-d H:i:s');
        if (!empty($input['ip_address'])) {
            $device->last_seen_ip_lan = $input['ip_address'];
            $device->first_seen_ip_lan = $input['ip_address'];
        }
        $wanIpAddress = self::getIpAdrress();
        $device->last_seen_ip_wan = $wanIpAddress;
        $device->first_seen_ip_wan = $wanIpAddress;
        $device->status = 1;
        if (!$device->validate()) {
            throw new \Exception(json_encode($device->getErrors()), 400);
        }
        $device->insert();

        if (empty($input['device_details']) || !is_array($input['device_details'])) {
            return $device;
        }

        foreach ($input['device_details'] as $name => $value) {
            if (empty($name)) {
                continue;
            }

            try {
                $deviceDetails = DeviceDetails::create($device, strval($name));
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }

            try {
                DeviceDetailsData::create($device, $deviceDetails, $value);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }
        }
        return $device;
    }

    /**
     * @param $postData
     * @param $method
     * @return Device|array|\yii\db\ActiveRecord
     * @throws \yii\db\StaleObjectException
     */
    public static function auth($postData, $method = null, $deviceUpdate = true)
    {
        try {
            self::validateAuthInput($postData, $method);
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage(), $exc->getCode());
        }

        $device = Device::find()->where('uuid = :uuid', [':uuid' => $postData['uuid']])->one();
        if ($device === null) {
            throw new \Exception(Yii::t('app', 'Device not found'), 404);
        }

        if (!empty($postData['token'])) {
            try {
                self::authByToken($postData['token'], $device->id);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }
            $device->token = $postData['token'];
        } else {
            try {
                DeviceDetails::auth($device, $postData['device_details']);
            } catch (\Exception $exc) {
                throw new \Exception($exc->getMessage(), $exc->getCode());
            }

            $session = Session::create('device_auth', $device->id);
            if ($session === null) {
                throw new \Exception(Yii::t('app', 'Could not open a new session for your device. Please retry later.'), 500);
            }
            $device->token = $session->token;
        }

        if ($deviceUpdate) {
            $device->last_seen = date('Y-m-d H:i:s');
            $device->status = 1;
            $device->last_seen_ip_lan = !empty($postData['ip_address']) ? $postData['ip_address'] : '';
            $device->last_seen_ip_wan = self::getIpAdrress();
            $device->current_version = !empty($postData['product_version']) ? $postData['product_version']
                : ($device->current_version === null ? $device->product_version : $device->current_version);
            if (!empty($postData['user_id']))
                $device->updated_by = $postData['user_id'];
            $device->update();
        }

        return $device;
    }

    public static function validateAuthInput($input, $method = null)
    {
        if (empty($input['uuid'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No uuid.'), 400);
        }

        if ($method === null && empty($input['device_details']) && empty($input['token'])) {
            throw new \Exception(Yii::t('app', 'Wrong request received. No device auth details.'), 400);
        }

        if ($method !== null) {
            if (!isset(self::$authMethods[$method])) {
                throw new \Exception(Yii::t('app', 'Wrong request received. Unknown authentication method.'), 400);
            }
            if (empty($input[$method])) {
                throw new \Exception(Yii::t('app', 'Wrong request received. No device auth details.'), 400);
            }
        }

        return true;
    }

    public static function generateUUID()
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public static function getIpAdrress()
    {
        $ip = Yii::$app->request->getUserIP();
        if (!empty($ip)) {
            return $ip;
        }

        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return '';
    }

    public static function authByToken($token, $deviceID)
    {
        $session = Session::find()->where("token = :token AND device_id = {$deviceID}", [':token' => $token])->one();
        if ($session === null) {
            throw new \Exception(Yii::t('app', 'Could not authenticate your token'), 401);
        }

        if (time() - strtotime($session->last_seen) > Session::sessionTimeoutThreshold()) {
            throw new \Exception(Yii::t('app', 'Your token has expired'), 401);
        }

        $session->last_seen = date('Y-m-d H:i:s');
        $session->updated = date('Y-m-d H:i:s');
        $session->updated_by = User::getAPIUserID();
        $session->update();

        return $session;
    }

    public function getProductDownloadDetails($version, $deviceType)
    {
        try {
            $this->validateProductDownloadDetails($version, $deviceType);
        } catch (\Exception $exc) {
            throw new \Exception($exc->getMessage(), $exc->getCode());
        }

        $tmpDownloadsDir = $this->getDownloadsDirPath();
        $productType = ProductType::find()->where('`deleted` = 0 AND `id` = :id', [':id' => $deviceType])->one();
        if (empty($productType)) {
            throw new \Exception(Yii::t('app', 'Your product type was not found'), 404);
        }
        $productDir = Device::$releasesDir . $productType->name;
        $productReleaseFileFullPath = "{$productDir}/" . $this->getProductReleaseFileName($version, $deviceType);
        $productDownloadFileName = Device::getTemporaryDownlodFileName($productReleaseFileFullPath);
        $productDownloadFileFullPath = "{$tmpDownloadsDir}{$productDownloadFileName}";

        // copiere versiune .zip in web/files/ cu o denumire random creata
        copy($productReleaseFileFullPath, $productDownloadFileFullPath);

        return [
            'url' => Device::getDownloadLink($productDownloadFileName),
            'hash' => Device::getFileHash($productDownloadFileFullPath)
        ];
    }

    public function validateProductDownloadDetails($version, $deviceType)
    {
        if (!file_exists(Device::$releasesDir)) {
            throw new \Exception(Yii::t('app', 'Products releases directory does not exist'), 404);
        }

        $productType = ProductType::find()->where('`deleted` = 0 AND `id` = :id', [':id' => $deviceType])->one();
        if (empty($productType)) {
            throw new \Exception(Yii::t('app', 'Your product type was not found'), 404);
        }

        $productDir = Device::$releasesDir . $productType->name;
        if (!file_exists($productDir)) {
            throw new \Exception(Yii::t('app', 'Products releases directory does not exist'), 404);
        }

        $productReleaseFileFullPath = "{$productDir}/" . $this->getProductReleaseFileName($version, $deviceType);
        if (!file_exists($productReleaseFileFullPath)) {
            throw new \Exception(Yii::t('app', 'Product release file does not exist'), 404);
        }

        return true;
    }

    public function getDownloadsDirPath()
    {
        if (!file_exists(Device::$tmpDownloadsDir)) {
            mkdir(Device::$tmpDownloadsDir, 0777, true);
        }

        return Device::$tmpDownloadsDir;
    }

    public function getProductReleaseFileName($version, $deviceType)
    {
        $productType = ProductType::find()->where('`deleted` = 0 AND `id` = :id', [':id' => $deviceType])->one();
        if (empty($productType)) {
            throw new \Exception(Yii::t('app', 'Your product type was not found'), 404);
        }
        return $productType->name . "-v{$version}.zip";

        // de luat numele fisierului:
        // din tabela product vom lua id-ul produsului care apartine $this->type (vom cauta in coloana device_type din tabela product)
        // din tabela product_history vom lua numele fisierului pe baza product_id si version
    }

    public static function getTemporaryDownlodFileName($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        return str_replace('.', '', uniqid(microtime(true))) . ".{$extension}";
    }

    public static function getDownloadLink($fileName)
    {
        return Device::$downloadURL . $fileName;
    }

    public static function getFileHash($fileFullPath)
    {
        return strtoupper(hash_file('sha256', $fileFullPath));
    }

    /**
     * Gets query for [[Sessions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Session::className(), ['device_id' => 'id']);
    }
}
