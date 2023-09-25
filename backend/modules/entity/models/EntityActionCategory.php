<?php

namespace backend\modules\entity\models;

use Yii;
use yii\data\ArrayDataProvider;


class EntityActionCategory extends EntityActionCategoryParent
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.entity_action_category';
    }

    public static function getSQLActionCategory($condition = '')
    {
        $tblEntityActionCategory = self::tableName();
        $tblEntity = Entity::tableName();
        $tblDomain = Domain::tableName();
        $tblCategoryCheck = CategoryCheck::tableName();

        return "SELECT eac.* , cc.`name`, e.`name` as `entity_name`, d.`name` AS `domain_name`
                FROM {$tblEntityActionCategory} AS eac
                INNER JOIN {$tblCategoryCheck} cc ON cc.`id`= eac.`category_check_id`
                INNER JOIN {$tblEntity} e ON e.`id`= eac.`entity_id`
                INNER JOIN {$tblDomain} `d` ON `d`.`id`=`e`.`domain_id`
                {$condition}";
    }

    public static function getDataProvider($condition = '')
    {
        $allModels = self::queryAll(self::getSQLActionCategory($condition));
        return new ArrayDataProvider([
            'allModels' => $allModels,
            'sort' => [
                'attributes' => [],
            ],
        ]);
    }
}
