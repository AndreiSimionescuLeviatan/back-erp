<?php

namespace api\controllers;

use api\models\Article;
use api\models\Device;
use api\models\Speciality;
use Yii;

/**
 * User controller
 */
class ArticleController extends RestController
{
    public $modelClass = 'api\models\Article';

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
        $articles = Article::find()->select('id, code, name')->indexBy('id')->where($where)->asArray()->all();

        $this->return['articles'] = $articles;

        $message = Yii::t('app', 'Successfully sent the articles');
        return $this->prepareResponse($message);
    }
}
