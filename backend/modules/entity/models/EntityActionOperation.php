<?php

namespace backend\modules\entity\models;

use Yii;

class EntityActionOperation extends EntityActionOperationParent
{
    const BUILD_ARTICLE_REPLACE_ARTICLE_BEN_PRICE_HISTORY = 1;
    const BUILD_ARTICLE_REPLACE_ARTICLE_PROC_PRICE_HISTORY = 2;
    const BUILD_ARTICLE_REPLACE_ARTICLE_QUANTITY = 5;
    const BUILD_ARTICLE_REPLACE_QUANTITY_LIST_CHANGE = 7;
    const BUILD_ARTICLE_DELETE = 8;
    const BUILD_ARTICLE_REPLACE_PROCUREMENT_OFFER_PACKAGE = 10;
    const BUILD_ARTICLE_REPLACE_PROCUREMENT_OFFER_PROVISION = 12;
    const BUILD_PACKAGE_REPLACE_ARTICLE_QUANTITY = 13;
    const BUILD_PACKAGE_REPLACE_EQUIPMENT_QUANTITY = 14;
    const BUILD_PACKAGE_REPLACE_QUANTITY_LIST_CHANGE = 15;
    const BUILD_PACKAGE_REPLACE_PROCUREMENT_OFFER_PACKAGE = 16;
    const BUILD_PACKAGE_REPLACE_PROVISION_NEW_ELEMENT = 17;
    const BUILD_PACKAGE_REPLACE_PROCUREMENT_OFFER_PROVIDER = 18;
    const BUILD_PACKAGE_SPECIALITY_DELETE = 19;
    const BUILD_PACKAGE_DELETE = 20;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.entity_action_operation';
    }

    public static function getSqlEntityActionOperation($condition = ''): string
    {
        $sqlTableEntity = Entity::getSqlEntity();
        $tblEntityActionOp = EntityActionOperation::tableName();
        $tblEntityActionCategory = EntityActionCategory::tableName();
        return "SELECT  `eaop`.*, `es`.`name_domain` AS `domain_source`, `ec`.`name_domain` AS `domain_change`,
                        `es`.`name` AS `entity_source`, `ec`.`name` AS `entity_change`
                FROM {$tblEntityActionOp} `eaop`
                INNER JOIN {$tblEntityActionCategory} AS `eac` ON eac.`id`=`eaop`.`action_category_id`
                INNER JOIN ({$sqlTableEntity}) AS `es` ON  `es`.`id`=`eac`.`entity_id`
                INNER JOIN ({$sqlTableEntity}) AS `ec` ON  `ec`.`id`=`eaop`.`entity_change_id`
                {$condition}";
    }
}
