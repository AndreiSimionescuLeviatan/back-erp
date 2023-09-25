<?php

namespace backend\modules\location\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Address extends AddressParent
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['added'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'added_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'street_id' => Yii::t('app', 'Street ID'),
            'number' => Yii::t('location', 'Number field'),
            'deleted' => Yii::t('app', 'Deleted'),
            'added' => Yii::t('app', 'Added'),
            'added_by' => Yii::t('app', 'Added By'),
            'updated' => Yii::t('app', 'Updated'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @param $streetAttributes = [
     * 'city_id' => 1,
     * 'name' => 'str. Liteni',
     * 'number' => '96',
     * ];
     * @return array
     * @throws \Exception
     */

    public static function createAddress($streetAttributes)
    {
        if (
            empty($streetAttributes)
            || empty($streetAttributes['city_id'])
        ) {
            throw new \Exception(Yii::t('app', 'No data was received to create the address'));
        }

        $streetModel = Street::getByAttributes($streetAttributes, $streetAttributes);
        if ($streetModel === null) {
            throw new \Exception(Yii::t('location', 'An error occurred while saving the street') . Yii::t('location', 'Contact an administrator'));
        }

        $addressAttributes = [
            'street_id' => $streetModel->id,
            'number' => trim($streetAttributes['number']) ?? null,
            'block' => trim($streetAttributes['block']) ?? null,
            'scale' => trim($streetAttributes['scale']) ?? null,
            'floor' => trim($streetAttributes['floor']) ?? null,
            'apartment' => trim($streetAttributes['apartment']) ?? null,
        ];
        $addressModel = Address::getByAttributes($addressAttributes, $addressAttributes);
        if ($addressModel === null) {
            throw new \Exception(Yii::t('location', 'An error occurred while saving the address') . Yii::t('location', 'Contact an administrator'));
        }

        return [
            'street_model' => $streetModel,
            'address_model' => $addressModel,
        ];
    }

}