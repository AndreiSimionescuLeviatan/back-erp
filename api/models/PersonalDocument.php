<?php

namespace api\models;

use Yii;
use yii\db\Exception;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "personal_document".
 *
 * @property int $id
 * @property int $user_id
 * @property int $car_id
 * @property string $name
 * @property string $image_name
 * @property int $type tipul documentului
 * @property string $added
 * @property int $added_by
 */
class PersonalDocument extends \yii\db\ActiveRecord
{
    public $uploadFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'personal_document';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_auto_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'car_id', 'name', 'image_name', 'type', 'added', 'added_by'], 'required'],
            [['user_id', 'car_id', 'type', 'added_by'], 'integer'],
            [['name', 'image_name'], 'string'],
            [['added'], 'safe'],
            [['uploadFile'], 'file', 'skipOnEmpty' => false]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'car_id' => Yii::t('app', 'Car ID'),
            'name' => Yii::t('app', 'Name'),
            'image_name' => Yii::t('app', 'Image Name'),
            'type' => Yii::t('app', 'Type'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
        ];
    }

    public static function prepareFileCreate($model)
    {
//        $uploadedFileExt = pathinfo($_FILES['ProductHistory']['name']['uploadFile'], PATHINFO_EXTENSION);
//        $model->file_name = "{$deviceType}-v{$model->version}.{$uploadedFileExt}";
        $model->uploadFile = UploadedFile::getInstance($model, 'uploadFile');

//        var_dump($model->uploadFile);
//        die();

        if (!$model->validate()) {
            throw new Exception(Yii::t('app', 'Can not validate the inputs data'));
        }

        $signatureDir = Yii::getAlias('@backend/upload/signatures');
        if (!is_dir($signatureDir)) {
            try {
                FileHelper::createDirectory($signatureDir);
            } catch (\Exception $exc) {
                $msg = "The directory for uploaded files could not be created. \n";
                $msg .= "Error received: {$exc->getMessage()} \n";
                $msg .= "Please contact an administrator!";
                throw new Exception(Yii::t('app', $msg));
            }
        }

        return true;
    }
}
