<?php

namespace api\controllers;

use Yii;

/**
 * Car controller
 */
class CarDocumentController extends RestController
{
    public $modelClass = 'api\models\CarDocument';

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete", "create", "index" actions
        unset($actions['delete'], $actions['index'], $actions['create']);

        return $actions;
    }
}