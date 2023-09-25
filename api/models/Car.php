<?php

namespace api\models;

use backend\modules\crm\models\Brand;
use backend\modules\crm\models\BrandModel;
use backend\modules\finance\models\Acquisition;
use Yii;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 *
 * @property Acquisition $acquisitionType
 * @property CarDetail $carDetail
 * @property CarDocument $carDocuments
 * @property HrCompany $company
 * @property Brand $brand
 * @property BrandModel $brandModel
 */
class Car extends CarParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_AUTO . '.car';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => HrCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => BrandModel::className(), 'targetAttribute' => ['model_id' => 'id']],
            [['acquisition_type'], 'exist', 'skipOnError' => true, 'targetClass' => Acquisition::className(), 'targetAttribute' => ['acquisition_type' => 'id']],
            [['holder_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['holder_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ]);
    }

    /**
     * Gets query for [[AcquisitionType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAcquisitionType()
    {
        return $this->hasOne(Acquisition::className(), ['id' => 'acquisition_type']);
    }

    /**
     * Gets query for [[AcquisitionType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(HrCompany::className(), ['id' => 'company_id']);
    }

    /**
     * Gets query for [[CarDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarDetail()
    {
        return $this->hasOne(CarDetail::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[CarDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarDocuments()
    {
        return $this->hasOne(CarDocument::className(), ['car_id' => 'id']);
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::className(), ['id' => 'brand_id']);
    }

    /**
     * Gets query for [[BrandModel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrandModel()
    {
        return $this->hasOne(BrandModel::className(), ['id' => 'model_id']);
    }

    /**
     * Gets query for [[holder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHolder()
    {
        return $this->hasOne(User::className(), ['id' => 'holder_id']);
    }

    /**
     * Gets query for [[user]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param $status
     * @param $carId
     * @return bool
     * @throws HttpException
     * @throws ServerErrorHttpException
     */
    public static function setCarStatus($status, $carId, $userId, $carUserId, $carHolderId = false)
    {
        $model = Car::find()->where("id = :id", [':id' => $carId])->one();
        if (empty($model)) {
            throw new HttpException(404, Yii::t('app', 'No car available.'));
        }

        $model->status = $status;
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = $userId;
        if ($carHolderId)
            $model->holder_id = $carHolderId;
        $model->user_id = $carUserId;

        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    throw new HttpException(409, $error[0]);
                }
            }
            throw new HttpException(500, Yii::t('app', 'Failed to update car status'));
        }
        return true;
    }

    /**
     * @param $modelCarZone
     * @return bool
     * @throws ServerErrorHttpException
     */
    public static function setCarZone($modelCarZone)
    {
        $model = CarZone::find()->where(['car_id' => $modelCarZone->car_id, 'zone_id' => $modelCarZone->zone_id])->one();

        if (empty($model)) {
            $carZone = new CarZone();

            $carZone->added = date('Y-m-d H:i:s');
            $carZone->added_by = 41;
        } else {
            $carZone = $model;
            $carZone->updated = $modelCarZone->updated;
            $carZone->updated_by = $modelCarZone->updated_by;
        }
        $carZone->car_id = $modelCarZone->car_id;
        $carZone->zone_id = $modelCarZone->zone_id;
        $carZone->zone_option_id = $modelCarZone->zone_option_id;
        $carZone->observations = $modelCarZone->observations;
        $carZone->zone_photo = $modelCarZone->zone_photo;

        if ($carZone->save()) {
            return true;
        } elseif (!$carZone->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update car zones');
        } else {
            throw new ServerErrorHttpException($model->errors[0][0]);
        }
    }

    public function getDocumentsDetails($car)
    {
        CarDocument::setDocumentType();
        $carDocStatus = $car->carDocuments;
        $documentsDetails = [];
        $documents = CarDocument::$documentTypes;
        foreach ($documents as $documentType => $details) {
            $expDate = explode('_', $documentType)[0] . '_valid_until';
            $details['status'] = CarDocument::getCarDocumentStatus($carDocStatus[$expDate], true);
            $details['exist'] = false;

            $types = [
                1 => '.pdf',
                2 => '.jpg',
                3 => '.jpeg'
            ];
            $docType = strtoupper(explode('_', $documentType)[0]);
            foreach ($types as $type) {
                if ($carDocStatus[$documentType] !== null) {
                    $extension = explode('.', $carDocStatus[$documentType]);
                    $srvPath = Yii::getAlias("@backend/upload/auto/erp/car_{$car->id}/documents/{$docType}/") . $carDocStatus[$documentType] . $type;
                    if (isset($extension[1])) {
                        $srvPath = Yii::getAlias("@backend/upload/auto/erp/car_{$car->id}/documents/{$docType}/") . $carDocStatus[$documentType];
                    }
                    if (file_exists($srvPath)) {
                        $details['exist'] = true;
                    }
                }
            }

            $documentsDetails[] = $details;
        }

        return $documentsDetails;
    }
}
