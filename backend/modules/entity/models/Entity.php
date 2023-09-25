<?php

namespace backend\modules\entity\models;

use Yii;
use yii\data\ArrayDataProvider;

class Entity extends EntityParent
{
    const BUILD_ARTICLE_ID = 1;
    const PROCUREMENT_OFFER_PACKAGE_CONTENT_ID = 8;
    const BUILD_PACKAGE_ID = 10;
    const BUILD_PACKAGE_SPECIALITY_ID = 11;
    const PROCUREMENT_OFFER_ID = 16;
    const PROCUREMENT_ITEM_MERGE_ID = 17;
    const DESIGN_SPECIALITY_ID = 18;

    public static $names = [];
    public static $entities = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_ENTITY . '.entity';
    }

    public static function setNames($conditions = [['deleted' => 0]])
    {
        self::$names = Entity::getList($conditions, ['key' => 'id', 'value' => 'description']);
    }

    public static function setEntities()
    {
        self::$entities = [];
        $entities = EntityActiveRecord::queryAll(Entity::getSqlEntity());
        foreach ($entities as $entity) {
            self::$entities[$entity['id']] = '`' . EntityActionLog::PREFIX_DB . "{$entity['name_domain']}`.`{$entity['name']}`";
        }
    }

    public static function getSqlEntity($condition = '')
    {
        return "SELECT `e`.*, `d`.`name` AS `name_domain`
                FROM " . Entity::tableName() . " AS `e`
                INNER JOIN " . Domain::tableName() . " `d` ON `d`.`id`=`e`.`domain_id`
                {$condition}";
    }

    public static function getEntityByDomainAndEntityName($nameDomain, $nameEntity)
    {
        $condition = "WHERE `d`.`deleted` = 0 AND `e`.`deleted` = 0 AND `d`.`name` = '{$nameDomain}' AND `e`.`name` = '{$nameEntity}'";
        $sql = Entity::getSqlEntity($condition);
        return EntityActiveRecord::queryOne($sql);
    }

    public static function getEntityByID($entityID)
    {
        $condition = "WHERE `d`.`deleted` = 0 AND `e`.`deleted` = 0 AND `e`.`id` = {$entityID}";
        $sql = Entity::getSqlEntity($condition);
        return EntityActiveRecord::queryOne($sql);
    }

    public static function getEntitiesDescription($condition = '')
    {
        $sql = 'SELECT `id`, `description` AS `name` FROM ' . Entity::tableName() . " WHERE 1 = 1 {$condition}";
        return EntityActiveRecord::queryAll($sql);
    }

    public static function getDataProviderEntityDescription($condition = '')
    {
        $allModels = self::getEntitiesDescription($condition);
        return new ArrayDataProvider([
            'allModels' => $allModels,
            'sort' => [
                'attributes' => [],
            ],
        ]);
    }

    public static function getValuesByEntityId($entityId, $column = 'name', $condition = '`deleted` = 0')
    {
        Entity::setEntities();
        if (!isset(Entity::$entities[$entityId])) {
            return [];
        }

        $data = [];
        try {
            $tblEntity = Entity::$entities[$entityId];
            $sql = "SELECT `id`, `{$column}` FROM {$tblEntity} WHERE {$condition} ORDER BY {$column}";
            $allModels = EntityActiveRecord::queryAll($sql);
            foreach ($allModels as $model) {
                $data[$model['id']] = $model[$column];
            }
        } catch (\Exception $exc) {
            return $data;
        }

        return $data;
    }

}
