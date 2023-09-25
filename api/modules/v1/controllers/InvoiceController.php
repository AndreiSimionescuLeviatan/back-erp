<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\InvoiceInitialImage;

use Yii;
use yii\helpers\FileHelper;


class InvoiceController extends RestV1Controller
{
    public $modelClass = 'api\modules\v1\models\Invoice';

    public function actionUploadImages()
    {
        ini_set("memory_limit", "1024M");
        set_time_limit(0);

        self::$threadName = 'InvoiceController_actionUploadImages';

        self::debug('New upload archive request received');

        if (!Yii::$app->request->isPost) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = 'Wrong HTTP method';
            self::error($this->return['message']);
            return $this->return;
        }

        if (empty($_FILES) || empty($_FILES["upload"])) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = 'No file uploaded received';
            $this->return['files'] = $_FILES;
            self::error($this->return['message']);
            self::error('Files: ' . json_encode($_FILES));
            return $this->return;
        }

        if ($_FILES["upload"]["size"] > 1000000000) { // 1GB
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = 'File too large';
            self::error($this->return['message']);
            return $this->return;
        }

        $fileName = $_FILES["upload"]["name"];
        $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($imageFileType != "zip") {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = 'Sorry, only zip files are allowed';
            self::error($this->return['message']);
            return $this->return;
        }

        $imagesDir = Yii::getAlias('@backend/' . Yii::$app->params['financeImagesToBeClassifiedUploadDir']) . '/' . date('Y_m_d');
        if (!is_dir($imagesDir)) {
            FileHelper::createDirectory($imagesDir);
        }

        $uniquePrefix = uniqid(date('YmdHis') . '_');
        $fullFilePath = "{$imagesDir}/{$uniquePrefix}_{$fileName}";
        if (!move_uploaded_file($_FILES["upload"]["tmp_name"], $fullFilePath)) {
            $message = "Error uploading file {$fileName}";
            $this->return['status'] = 500;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = $message;

            self::error("{$message} to {$fullFilePath}");
            return $this->return;
        }

        try {
            $response = InvoiceInitialImage::processUploadedZipFile($fullFilePath);
            self::debug($response);
            $this->return['process_zip_response'] = $response;
        } catch (\Exception $exc) {
            $this->return['status'] = $exc->getCode();
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = $exc->getMessage();

            self::debug($this->return['message']);
            return $this->return;
        }

        $message = "File {$fileName} has been uploaded";
        $this->return['status'] = 200;
        Yii::$app->response->statusCode = $this->return['status'];
        $this->return['message'] = $message;

        self::debug("{$message} to {$fullFilePath}");
        return $this->return;
    }
}