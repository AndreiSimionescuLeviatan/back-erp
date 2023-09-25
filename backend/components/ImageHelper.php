<?php

namespace backend\components;

use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;

class ImageHelper
{

    /**
     * @param $filePath
     * @return string
     */
    public static function convertImageFileToBase64($filePath)
    {
        $filePath = Yii::getAlias("@backend/web/car-zone-photo/$filePath");
        if (!file_exists($filePath)) {
            return null;
        }
        $type = pathinfo($filePath, PATHINFO_EXTENSION);
        $data = file_get_contents($filePath);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }


    public static $allowedImageTypes = ['jpg', 'jpeg', 'gif', 'png'];

    /**
     * @param $base64String
     * @param $fileName
     * @param $dirPath
     * @return string
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public static function saveBase64ToImageFile($base64String, $fileName, $dirPath)
    {
        {
            if (!is_dir($dirPath)) {
                try {
                    FileHelper::createDirectory($dirPath);
                } catch
                (Exception $exc) {
                    $msg = "Error received while creating directory: {$exc->getMessage()}. Please contact an administrator!";
                    throw new Exception(Yii::t('app', $msg), $exc->getCode());
                }
            }

        }
        try {
            $type = self::$allowedImageTypes[1];
            $image = self::decodeBase64ImageString($base64String, $type);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage(), $exc->getCode());
        }
        $fileFullPath = "{$dirPath}/{$fileName}.{$type}";
        if (!file_put_contents($fileFullPath, $image)) {
            throw new BadRequestHttpException(Yii::t('app', 'The image could not be saved!', 400));
        }

        return $fileFullPath;

    }

    public static function decodeBase64ImageString($base64String, $type)
    {
        $image = false;
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            throw new BadRequestHttpException('Could not match data URI with image data', 400);
        }

        $image = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]);
        if (!in_array($type, self::$allowedImageTypes)) {
            throw new BadRequestHttpException("Invalid image type {$type}. Allowed image types: " . implode(',', self::$allowedImageTypes), 400);
        }

        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);
        if ($image === false) {
            throw new BadRequestHttpException('The base64 string could not be decoded', 400);
        }

        return $image;
    }
}
