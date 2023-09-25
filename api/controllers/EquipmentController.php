<?php

namespace api\controllers;

use api\models\Device;
use api\models\Equipment;
use api\models\Speciality;
use Yii;

/**
 * User controller
 */
class EquipmentController extends RestController
{
    public $modelClass = 'api\models\Equipment';

    /**
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    private static function getDb()
    {
        return Yii::$app->get('ecf_build_db');
    }

    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete", "create", "update" and "view" actions
        unset($actions['delete'], $actions['create'], $actions['update'], $actions['view']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function verbs()
    {
        return [
            'index' => ['POST']
        ];
    }

    public function prepareDataProvider()
    {
        $post = Yii::$app->request->post();

        try {
            Device::auth($post, 'token');
        } catch (\Exception $exc) {
            return $this->prepareResponse($exc->getMessage(), $exc->getCode());
        }

        $where = '`deleted` = 0';
        if (!empty($post['speciality'])) {
            if (empty(Speciality::find()->where('`deleted` = 0 AND `id` = :id', [':id' => $post['speciality']])->one())) {
                $message = Yii::t('app', "Speciality with id '{id}' does not exist", [
                    'id' => $post['speciality']
                ]);

                return $this->prepareResponse($message);
            }

            $where .= " AND `speciality_id` = {$post['speciality']}";
        }
        $equipments = Equipment::find()->select('id, code, long_name as name')->indexBy('id')->where($where)->asArray()->all();

        $this->return['equipments'] = $equipments;

        $message = Yii::t('app', 'Successfully sent the equipments');
        return $this->prepareResponse($message);
    }
}
