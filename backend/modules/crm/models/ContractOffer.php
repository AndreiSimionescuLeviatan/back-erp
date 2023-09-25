<?php

namespace backend\modules\crm\models;

use Yii;

class ContractOffer extends ContractOfferParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.contract_offer';
    }
}
