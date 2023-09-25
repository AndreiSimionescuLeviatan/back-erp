<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\HolidayType;
use api\modules\v2\models\RequestRecord;
use Yii;

/**
 * V2 of Holiday Type controller
 */
class VacationTypeController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\HolidayType';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['view']);
        return $actions;
    }


    public function actionIndex()
    {
        $vacationType = HolidayType::find()->where([
            'deleted' => 0,
            'parent_id' => null
        ])->with([
            'vacationTypeSubcats' => function ($query) {
                $query->andWhere('deleted = 0');
            },
        ])->asArray()->all();


        $this->return['vacation_types'] = $vacationType;
        return $this->return;
    }


    public function actionView($id)
    {
        $model = HolidayType::find()->where([
            'id' => $id
        ])->one();

        if (empty($model)) {
            $this->return['status'] = 404;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "No vacation to match your request was found");
            return $this->return;
        }

        if (empty(Yii::$app->user->identity->employee)) {
            $this->return['status'] = 400;
            Yii::$app->response->statusCode = $this->return['status'];
            $this->return['message'] = Yii::t('api-hr', "Your user dont have an employee account set");
            return $this->return;
        }

        $emplRequests = RequestRecord::find()->where([
            'employee_id' => Yii::$app->user->identity->employee->id,
            'holiday_type_id' => $id,
        ])->sum('counter');

        if (!empty($model->unit) && !empty($emplRequests)) {
            $model->unit = $model->unit - ($model->measure_unit === 1 ? $emplRequests / 60 / 24 : $emplRequests / 60);
        }

        $this->return['vacation_details'] = $model;
        return $this->return;
    }
}