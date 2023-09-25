<?php

namespace backend\modules\crm\models;

use backend\modules\adm\models\User;
use Yii;
use yii\web\BadRequestHttpException;

class BrandModel extends BrandModelParent
{
    const EVENT_CHAIN_DELETE_MODEL = 'chain-delete-model';
    const EVENT_CHAIN_ACTIVATE_MODEL = 'chain-activate-model';

    public $deleteValue = 1;
    public static $names = [];
    public static $addedBy = [];
    public static $updatedBy = [];

    public function init()
    {
        $this->on(self::EVENT_CHAIN_DELETE_MODEL, [$this, 'chainDelete']);
        $this->on(self::EVENT_CHAIN_ACTIVATE_MODEL, [$this, 'chainActivate']);

        parent::init();
    }

    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.brand_model';
    }

    /**
     * Populates the above $names with id as key and name as value.
     * We created this function because is more sql optimized
     * Will use this instead of ArrayHelper::map(Speciality::....., 'id', 'name') and also on other places where possible
     * and also on gridView where we display brand name
     * @author Diana Basoc
     * @since 12/10/2021
     */
    public static function setNames()
    {
        self::$names = [];
        $models = self::find()->where(['deleted' => 0])->orderBy('name')->all();
        foreach ($models as $model) {
            self::$names[$model->id] = $model->name;
        }
    }

    public static function setAddedBy()
    {
        self::$addedBy = [];

        $models = self::find()->distinct('added_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$addedBy[$model->added_by] = User::$users[$model->added_by];
        }
    }

    public static function setUpdatedBy()
    {
        self::$updatedBy = [];

        $models = self::find()->distinct('updated_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$updatedBy[$model->updated_by] = $model->updated_by ? User::$users[$model->updated_by] : null;
        }
    }

    public function chainDelete()
    {
        if ($this->deleted === 0) {
            $this->updated = date('Y-m-d H:i:s');
            $this->updated_by = Yii::$app->user->id;
        }
        $this->deleted = $this->deleteValue;

        $this->validate();
        if ($this->hasErrors()) {
            foreach ($this->errors as $error) {
                throw new BadRequestHttpException(Yii::t('crm', $error[0]));
            }
        }
        if (!$this->save(false)) {
            throw new BadRequestHttpException(Yii::t('crm', 'The model could not be deleted.'));
        }
        return true;
    }

    public function chainActivate()
    {
        if ($this->deleted === 1) {
            $this->updated = date('Y-m-d H:i:s');
            $this->updated_by = Yii::$app->user->id;
        }
        $this->deleted = 0;

        $this->validate();
        if ($this->hasErrors()) {
            foreach ($this->errors as $error) {
                throw new BadRequestHttpException(Yii::t('crm', $error[0]));
            }
        }
        if (!$this->save(false)) {
            throw new BadRequestHttpException(Yii::t('crm', 'The car model could not be activated.'));
        }

        return true;
    }
}