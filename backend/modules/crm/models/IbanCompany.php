<?php

namespace backend\modules\crm\models;

use Yii;
use yii\db\Exception;

class IbanCompany extends IbanCompanyParent
{
    public $list = [];

    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.iban_company';
    }

    public static function saveIban($model, $ibanPost, $errorMsg)
    {
        if (!$model->save()) {
            $errorMsg .= $ibanPost . Yii::t('crm', ' -> The error: ');
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    $errorMsg .= Yii::t('crm', $error[0]);
                    break;
                }
            } else {
                $errorMsg .= Yii::t('crm', 'Unknown error');
            }
            $errorMsg .= '<br>';
        }

        return $errorMsg;
    }

    /**
     * Validates IBAN inserted by user
     * @param $ibanPost
     * @return bool|string
     * @since 13/05/2022
     * @author Andrei I.
     * @update 15/06/2022
     * @author Andrei I.
     */
    public static function validateIban($ibanPost)
    {
        if (!empty($ibanPost)) {
            $ibanCountry = substr(strtoupper($ibanPost), 0, 2);
            $ibanLength = strlen(str_replace(" ", "", $ibanPost));
            $ibanLength24 = array('RO', 'AD', 'CZ', 'MD', 'PK', 'SA', 'SK', 'ES', 'SE', 'TN', 'VG');
            $ibanLength28 = array('AL', 'AZ', 'BY', 'CY', 'DO', 'SV', 'SK', 'GT', 'HU', 'LB');
            $ibanLength20 = array('AT', 'BA', 'EE', 'XK', 'LT', 'LU');
            $ibanLength22 = array('BH', 'BH', 'CR', 'GE', 'DE', 'IE', 'ME', 'RS', 'GB', 'VA');
            $ibanLength29 = array('BR', 'EG', 'PS', 'QA', 'UA');
            $ibanLength21 = array('HR', 'LV', 'LI', 'CH');
            $ibanLength18 = array('DK', 'FO', 'FI', 'GL', 'NL');
            $ibanLength23 = array('TL', 'GI', 'IQ', 'IL', 'AE');
            $ibanLength27 = array('FR', 'GR', 'IT', 'MR', 'MC', 'SM');
            $ibanLength26 = array('IS', 'TR');
            $ibanLength30 = array('JO', 'KW', 'MU');
            $ibanLength31 = array('MT', 'SC');
            $ibanLength19 = array('MK', 'SI');
            $message = "One of the company IBAN is not valid";
            $msg = "IBAN must be unique";

            $createIbanRemoveWhiteSpace = trim(str_replace(" ", "", strtoupper($ibanPost)));
            $createIban = chunk_split($createIbanRemoveWhiteSpace, 4, ' ');
            $formatIban = trim($createIban);

            $checkIban = self::find()->where(['iban' => $formatIban])->count();

            if ($checkIban == 1) {
                throw new Exception(Yii::t('crm', "{$msg}"));
            }

            if ($ibanLength <= 14) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength >= 33) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 17) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 24 && !(in_array($ibanCountry, $ibanLength24))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 28 && !(in_array($ibanCountry, $ibanLength28))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }
            if ($ibanLength == 20 && !(in_array($ibanCountry, $ibanLength20))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 22 && !(in_array($ibanCountry, $ibanLength22))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 16 && $ibanCountry != 'BE') {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 29 && !(in_array($ibanCountry, $ibanLength29))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 21 && !(in_array($ibanCountry, $ibanLength21))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 18 && !(in_array($ibanCountry, $ibanLength18))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 23 && !(in_array($ibanCountry, $ibanLength23))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 27 && !(in_array($ibanCountry, $ibanLength27))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 26 && !(in_array($ibanCountry, $ibanLength26))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 30 && !(in_array($ibanCountry, $ibanLength30))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 31 && !(in_array($ibanCountry, $ibanLength31))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 19 && !(in_array($ibanCountry, $ibanLength19))) {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 15 && $ibanCountry != 'NO') {
                throw new Exception(Yii::t('crm', "{$message}"));
            }

            if ($ibanLength == 32 && $ibanCountry != 'LC') {
                throw new Exception(Yii::t('crm', "{$message}"));
            }
        }
        return true;
    }
}
