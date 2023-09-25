<?php

namespace backend\modules\crm\models;

use Yii;
use yii\web\BadRequestHttpException;

class EntityDomain extends EntityDomainParent
{
    /**
     * Finds the EntityDomain model based on item_id.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $item_id
     * @return EntityDomain the loaded model
     * @throws BadRequestHttpException if the model cannot be found
     */
    public static function findEntityDomainByItem($item_id, $createIfNotExists = false)
    {
        if (($model = self::find()->where("item_id = {$item_id}")->one()) !== null) {
            return $model;
        }

        if ($createIfNotExists) {
            $model = new self();
            $model->added = date('Y-m-d H:i:s');
            $model->added_by = Yii::$app->user->id;
            $model->item_id = $item_id;
            return $model;
        }

        throw new BadRequestHttpException(Yii::t('crm', 'The requested entity does not exist.'));
    }
}
