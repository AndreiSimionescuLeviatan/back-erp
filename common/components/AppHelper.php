<?php

namespace common\components;

use backend\modules\build\models\Article;
use backend\modules\build\models\Equipment;
use Yii;
use yii\helpers\Html;
use yii\web\BadRequestHttpException;

class AppHelper
{
    public static $names = [];

    /**
     * Function used in grid view for viewing if there are some deleted relation
     *
     * @author Calin.B
     * @since 17/01/2022
     *
     * Just some optimization of code
     * In previous version we used one more function to find the item name
     * Now we find the name using relations, like so: $model->relation->name
     * First checking if there is a relation, and returning '-', if no relation found
     * @updated 16.02.2022
     * @updated_by Calin B.
     *
     * Move the function here to use it whenever it needed with the new '$relation' attribute
     * that represents the entity we'll color with a red background
     * @updated 25.02.2022
     * @updated_by Calin B.
     */
    public static function viewDeletedEntities($model, $page, $relation, $column = 'name'): array
    {
        $elOptions = [
            'class' => $page === 'index' ? 'text-center' : 'text-left'
        ];
        $relationName = !empty($model->$relation) ? $model->$relation->$column : '-';
        $elValue = $page === 'index' ? "<div class='scroll_grid_view_columns'>" . $relationName . "</div>" :
            $relationName;

        if (!empty($model->$relation) && $model->$relation->deleted != 0) {
            $elOptions['class'] .= ' bg-danger';
            $elOptions['class'] .= $page === 'index' ? ' info_icon_for_deleted_entities_index' : ' info_icon_for_deleted_entities_view';
            $elOptions['data-toggle'] = 'tooltip';
            $elOptions['title'] = Yii::t('app', "The entity '{relationName}' was deleted! Contact administrator!", [
                'relationName' => $model->$relation->$column
            ]);

            $elValue = "<i class='fas fa-info-circle'></i>{$elValue}";
        }
        return ['elOptions' => $elOptions, 'elValue' => $elValue];
    }

    /**
     * Function to use to set names for an entity
     *
     * @author Calin B.
     * @since 31.03.2022
     */
    public static function setNames($modelKey, $modelClass, $column, $includeDeletedEntities = false)
    {
        self::$names[$modelKey] = [];
        $where = '`deleted` = 0';
        if ($includeDeletedEntities) {
            $where = '1 = 1';
        }

        $models = $modelClass::find()->where($where)->orderBy($column)->all();
        foreach ($models as $model) {
            self::$names[$modelKey][$model->id] = $model->$column;
        }
    }

    /**
     * Function to use to delete entities either in chain (deleted = 2) or not (deleted = 1)
     *
     * @author Calin B.
     * @since 31.03.2022
     */
    public static function chainDelete($model, $isChainDelete = false)
    {
        $deleteValue = $isChainDelete ? 2 : 1;

        if ((int)$model->deleted === 0) {
            $model->deleted = $deleteValue;
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;

            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new BadRequestHttpException(Yii::t('app', $error[0]));
                    }
                }

                throw new BadRequestHttpException(Yii::t('app', "Failed to delete!"));
            }
        }
    }

    /**
     * Function to use to activate entities
     *
     * @author Calin B.
     * @since 31.03.2022
     */
    public static function chainActivate($model, $isChainActivate = false)
    {
        if (!$isChainActivate || ($isChainActivate && $model->deleted === 2)) {
            $model->deleted = 0;
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;

            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        throw new BadRequestHttpException(Yii::t('app', $error[0]));
                    }
                }

                throw new BadRequestHttpException(Yii::t('app', "Failed to activate!"));
            }
        }
    }

    /**
     * Function is used to check permission of viewing deleted entities
     * returns true if you have permission and false if you don't
     * the function will be used in grid view for showing/hiding the update button, and switching the delete/activate buttons,
     * also we'll use it in SearchModel for filtering the grid view by deleted column if the user has permission
     * finally the function will be used in index to set the toggle value
     * @param int $urlParam - represents the 'deleted' param in url, the grid view will be filtered by
     * @param string $permissionName - represents the name of permission we'll check (usually it will be the activate permission)
     *
     * @author Calin B.
     * @since 12.05.2022
     */
    public static function checkPermissionViewDeletedEntities($urlParam, $permissionName)
    {
        if (isset($urlParam) && (int)$urlParam === 1 && Yii::$app->user->can($permissionName)) {
            return true;
        }

        return false;
    }

    /**
     * this function return an array with id as key and code/name as value
     * return only options who are in that specific table
     * @param $modelClass
     * @param $modelName
     * @param $column
     * @param $relationName
     * @param null $tableName
     * @return mixed
     * @author Marian.N
     * @since 28.06.2022
     */
    public static function getOptionsFilters($modelClass, $modelName, $column, $relationName, $tableName = null)
    {
        $where = '0 = 0';
        $tableNameAndColumn = "{$modelName}.{$column}";

        // only for procurement price history indexes
        if (!empty($_GET['ArticleProcurementPriceHistorySearch']['company_id'])) {
            $where = "`article_procurement_price_history`.`company_id` = {$_GET['ArticleProcurementPriceHistorySearch']['company_id']}";
        } elseif (!empty($_GET['EquipmentProcurementPriceHistorySearch']['company_id'])) {
            $where = "`equipment_procurement_price_history`.`company_id` = {$_GET['ArticleProcurementPriceHistorySearch']['company_id']}";
        }

        // only for equipment index
        if ($modelClass == get_class(new Equipment())) {
            if (!empty($_GET['speciality']) && $modelName !== 'speciality') {
                $where = "`equipment`.`speciality_id` = {$_GET['speciality']}";
            }
            if (!empty($_GET['category']) && $modelName === 'subcategory') {
                $where .= " AND `equipment`.`category_id` = {$_GET['category']}";
            }
            $where .= " AND equipment.deleted = 0";
        }

        if ($modelClass == get_class(new Article())) {
            $where .= " AND article.deleted = 0";
        }

        if (!empty($tableName)) {
            $tableNameAndColumn = "{$tableName}.{$column}";
        }

        return $modelClass::find()->joinWith($relationName)->select($tableNameAndColumn)->where($where)->indexBy("{$modelName}_id")->orderBy('name ASC')->column();
    }

    /**
     * Function to return the style and params for gridView tables
     * @param $searchModel
     * @return array
     */
    public static function setGridViewTableLayout($searchModel, $totalItems, $defaultPageSize = 20)
    {
        $searchModelFullName = explode("\\", get_class($searchModel));
        $urlParamName = end($searchModelFullName);
        $message = Yii::t('app', 'Records per page');

        return [
            'summary' => '<div class="summary"><b class="min-rows">{begin}-</b><b class="max-rows">{end}</b> (total: <b class="total_count">{totalCount}</b>) ',
            'layout' => "<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}" .
                ($totalItems > 0 ? ' / ' : '') . $message .
                Html::dropDownList('page-size', !empty($_GET[$urlParamName]['pageSize']) ? $_GET[$urlParamName]['pageSize'] : $defaultPageSize,
                    Yii::$app->params['pagination'], [
                        'class' => 'ml-1 page-size',
                    ]) . "</div></div><div class='col-sm-6'>{pager}</div></div>{items}" .
                "<div class='row mb-3 align-items-center'><div class='col-sm-6'>{summary}" .
                ($totalItems > 0 ? ' / ' : '') . $message .
                Html::dropDownList('page-size', !empty($_GET[$urlParamName]['pageSize']) ? $_GET[$urlParamName]['pageSize'] : $defaultPageSize,
                    Yii::$app->params['pagination'], [
                        'class' => 'ml-1 page-size',
                    ]) .
                "</div></div><div class='col-sm-6'>{pager}</div>",
            'tableOptions' => ['class' => 'table table-sm table-bordered table-striped table-valign-middle'],
        ];
    }

    /**
     * This function return the text and class used to display the button, depending on the status received.
     * @return array
     *
     * Moved in AppHelper and replaced bootstrap icons with svg-s made by Bogdan Pamparau.
     */
    public static function getIconByStatus($status, $centralizer = false, $estimate = false)
    {
        $url = '';
        switch ($status) {
            case 0:
                $iconStatus = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">' .
                    '<title>' . Yii::t('app', "Status new") . '</title>' .
                    '<defs><style>.bg-color-new{fill:#007bff;}.color-new{fill:#fff;}</style></defs>' .
                    '<g transform="translate(52.304 56.478)"><circle class="bg-color-new" cx="12" cy="12" r="12" transform="translate(-52.304 -56.478)"/>' .
                    '<path class="color-new" d="M29.815,8.515l-.563.975a.563.563,0,0,1-.769.206L25.457,7.948v3.49a.563.563,0,0,1-.563.563H23.' .
                    '768a.563.563,0,0,1-.563-.563V7.948L20.2,9.7a.563.563,0,0,1-.769-.206l-.563-.975a.562.562,0,0,1,.206-.768L22.1,6,' .
                    '19.073,4.254a.563.563,0,0,1-.206-.769l.563-.975A.563.563,0,0,1,20.2,2.3l3.026,1.748-.02-3.49A.563.563,0,0,1,23.768,' .
                    '0h1.126a.563.563,0,0,1,.563.563v3.49L28.483,2.3a.563.563,0,0,1,.769.206l.563.975a.562.562,0,0,1-.206.768L26.6,6,' .
                    '29.63,7.747A.539.539,0,0,1,29.815,8.515Z" transform="translate(-64.652 -50.479)"/></g></svg>';
                $class = 'btn btn-xs btn-primary text-white';
                $title = Yii::t('app', "Edit form");
                break;
            case 1:
                $iconStatus = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">' .
                    '<title>' . Yii::t('app', "Status in progress") . '</title>' .
                    '<defs><style>.bg-color-progress{fill:#ffc107;}</style></defs>' .
                    '<path class="bg-color-progress" d="M12,24A12,12,0,1,1,24,12,12,12,0,0,1,12,24ZM10.875,12a1.007,1.007,0,0,0,.5.9l4.5,3a1.032,' .
                    '1.032,0,0,0,1.519-.272,1.086,1.086,0,0,0-.272-1.561l-4-2.662V5.625A1.132,1.132,0,0,0,11.958,4.5a1.15,1.15,0,0,0-1.125,1.125Z"/>' .
                    '</svg>';
                $class = 'btn btn-xs btn-primary text-white';
                $title = Yii::t('app', "Edit form");
                break;
            case 2:
                $iconStatus = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">' .
                    '<title>' . Yii::t('app', "Status completed") . '</title>' .
                    '<defs><style>.bg-color-completed{fill:#28a745;}</style></defs>' .
                    '<path class="bg-color-completed" d="M0,12A12,12,0,1,1,12,24,12,12,0,0,1,0,12ZM17.428,9.928a1.313,1.313,0,0,' .
                    '0-1.856-1.856L10.5,13.144,8.428,11.072a1.313,1.313,0,0,0-1.856,1.856l3,3a1.316,1.316,0,0,0,1.856,0Z"/>' .
                    '</svg>';
                $class = 'btn btn-xs btn-primary text-white';
                $title = Yii::t('app', "Edit form");
                break;
            case 3:
                $iconStatus = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">' .
                    '<title>' . Yii::t('app', "Status closed") . '</title>' .
                    '<defs><style>.bg-color-closed{fill:#dc3545;}.color-closed{fill:#fff;}</style></defs>' .
                    '<g transform="translate(-510.579 -540.666)"><circle class="bg-color-closed" cx="12" cy="12" r="12" transform="translate(510.579 540.666)"/>' .
                    '<path class="color-closed" d="M1.875,4.5V3.375a3.375,3.375,0,0,1,6.75,0V4.5H9A1.5,1.5,0,0,1,10.5,6v4.5A1.5,1.5,0,0,1,9,' .
                    '12H1.5A1.5,1.5,0,0,1,0,10.5V6A1.5,1.5,0,0,1,1.5,4.5Zm1.5,0h3.75V3.375a1.875,1.875,0,1,0-3.75,0Z" transform="translate(517.328 546.666)"/></g>' .
                    '</svg>';
                $class = 'btn btn-xs btn-secondary text-white';
                $title = Yii::t('app', "Access denied");
                $url = null;
                break;
            default:
                $iconStatus = '<i class="text-danger" title=" ' .
                    Yii::t('app', 'Contact the administrator!') . ' ">' . Yii::t('app', 'Error') . '</i>';
                $class = 'btn btn-xs btn-primary text-white';
                $title = Yii::t('app', "Edit form");
        }
        return ['iconStatus' => $iconStatus, 'class' => $class, 'url' => $url, 'title' => $title];
    }
}
