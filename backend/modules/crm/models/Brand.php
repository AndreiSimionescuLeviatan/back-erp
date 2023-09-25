<?php

namespace backend\modules\crm\models;

use backend\modules\adm\models\User;
use Yii;
use yii\web\BadRequestHttpException;

/**
 * This is the model class that extends the "BrandParent" class.
 */
class Brand extends BrandParent
{

    public static $addedBy = [];
    public static $updatedBy = [];
    const EVENT_CHAIN_DELETE_BRAND = 'chain-delete-brand';
    const EVENT_CHAIN_ACTIVATE_BRAND = 'chain-activate-brand';

    public $deleteValue = 1;
    /**
     * The brand list that will contain mapped brands in form of key=>value
     * This var is populated when setNames function is called
     * @var array
     */
    public static $names = [];
    public static $brand = [];

    public static function setAddedBy()
    {
        self::$addedBy = [];

        $models = Brand::find()->distinct('added_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$addedBy[$model->added_by] = User::$users[$model->added_by];
        }
    }

    public static function setUpdatedBy()
    {
        self::$updatedBy = [];

        $models = Brand::find()->distinct('updated_by')->where(['deleted' => 0])->all();
        foreach ($models as $model) {
            self::$updatedBy[$model->updated_by] = $model->updated_by ? User::$users[$model->updated_by] : null;
        }
    }

    public function init()
    {
        $this->on(self::EVENT_CHAIN_DELETE_BRAND, [$this, 'chainDelete']);
        $this->on(self::EVENT_CHAIN_ACTIVATE_BRAND, [$this, 'chainActivate']);

        parent::init();
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_CRM . '.brand';
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

    /**
     * Populates the above $brands with id as key and name as value.
     * We created this function because is more sql optimized
     * Will use this instead of ArrayHelper::map(Speciality::....., 'id', 'name') and also on other places where possible
     * and also on gridView where we display brand name for auto domain
     * @author Alex G.
     * @since 24/02/2022
     */
    public static function setNamesBrandsModel()
    {
        $itemsIDs = EntityDomain::find()->where(['domain_id' => 1, 'entity_id' => 1, 'subdomain_id' => 1])->all();
        foreach ($itemsIDs as $itemsID) {
            $items[] = $itemsID->item_id;
        }
        $brandMarks = [];
        if (!empty($items)) {
            $brandMarks = self::find()->where(['in', 'id', $items])->all();
        }
        self::$brand = [];
        foreach ($brandMarks as $brandMark) {
            self::$brand[$brandMark->id] = $brandMark->name;
        }
    }

    /**
     * Function used in grid view for viewing if there are deleted Brand
     *
     * @author Mihnea G
     * @since 18/02/2022
     *
     */
    public static function viewDeletedBrand($model, $page): array
    {
        $elOptions = [
            'class' => $page === 'index' ? 'text-center' : 'text-left'
        ];
        $brandName = !empty($model->brand) ? $model->brand->name : '-';
        $elValue = $page === 'index' ? "<div class='scroll_grid_view_columns'>" . $brandName . "</div>" :
            $brandName;

        if (!empty($model->brand) && $model->brand->deleted != 0) {
            $elOptions['class'] .= ' bg-danger';
            $elOptions['class'] .= $page === 'index' ? ' info_icon_for_deleted_entities_index' : ' info_icon_for_deleted_entities_view';
            $elOptions['data-toggle'] = 'tooltip';
            $elOptions['title'] = Yii::t('crm', 'The brand "{brand}" was deleted! Contact administrator!', [
                'brand' => $brandName
            ]);

            $elValue = "<i class='fas fa-info-circle'></i>" . $elValue;
        }
        return ['elOptions' => $elOptions, 'elValue' => $elValue];
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
            throw new BadRequestHttpException(Yii::t('crm', 'The brand could not be deleted.'));
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
            throw new BadRequestHttpException(Yii::t('crm', 'The brand could not be activated.'));
        }

        return true;
    }
}
