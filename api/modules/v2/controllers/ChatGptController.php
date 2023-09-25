<?php

namespace api\modules\v2\controllers;

use Yii;
use yii\base\InvalidConfigException;

/**
 * RoomReservation controller
 */
class ChatGptController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\RoomReservation';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    /**
     * @return array|mixed
     * @throws InvalidConfigException|\Exception
     */
    public function actionIndex()
    {
        $get = Yii::$app->request->get('question');

        $sql = "INSERT INTO ecf_bemaria.question (question, ip_address, added, added_by) ";
        $sql .= " VALUES ('{$get}', '" . Yii::$app->request->getUserIP() . "', '" . date('Y-m-d H:i:s') . "', " . Yii::$app->user->id . ");";
        Yii::$app->db->createCommand($sql)->execute();

        $questionID = Yii::$app->db->getLastInsertID();

        $url = 'https://83.103.166.70:8181';
        $command = "curl -k -X POST -d '" . json_encode([
            'question' => $get,
            'id' => $questionID
        ]) . "' {$url}";

        $result = exec($command, $output, $resultCode);

        $this->return['answer'] = '';
        $this->return['question'] = $get;
        if ($resultCode !== 0) {
            Yii::$app->response->statusCode = 400;
            $this->return['message'] = Yii::t('api-logistic', 'Huston we have a problem!');
            $this->return['question_id'] = $questionID;

            $sql = "UPDATE ecf_bemaria.question SET ";
            $sql .= " status = 3, "; // 3 - error
            $sql .= " observations = '" . json_encode(['result' => $result, 'output' => $output, 'resultCode' => $resultCode]) . "', "; // 3 - error
            $sql .= " updated = '" . date('Y-m-d H:i:s') . "', ";
            $sql .= " updated_by = " . Yii::$app->user->id;
            $sql .= " WHERE id = {$questionID};";
            Yii::$app->db->createCommand($sql)->execute();

            return $this->return;
        }
        $this->return['answer'] = $result;
        // $this->return['answer'] = 'Te rugăm să revii mai târziu. Pregătim sistemul pentru a-ți da răspunsul cel mai bun. Mulțumim de înțelegere.';
        $this->return['message'] = Yii::t('api-logistic', 'ChatGPT are un răspuns pentru tine');
        $this->return['question_id'] = $questionID;

        $sql = "UPDATE ecf_bemaria.question SET ";
        $sql .= " answer = '{$this->return['answer']}', ";
        $sql .= " status = 1, "; // 1 - success
        $sql .= " updated = '" . date('Y-m-d H:i:s') . "', ";
        $sql .= " updated_by = " . Yii::$app->user->id;
        $sql .= " WHERE id = {$questionID};";
        Yii::$app->db->createCommand($sql)->execute();

        return $this->return;
    }

    public function actionFeedback()
    {
        $id = Yii::$app->request->get('id');
        $feedback = Yii::$app->request->get('feedback');

        $sql = "INSERT INTO ecf_bemaria.question_feedback (question_id, feedback, ip_address, added, added_by) ";
        $sql .= " VALUES ({$id}, {$feedback}, '" . Yii::$app->request->getUserIP() . "', '" . date('Y-m-d H:i:s') . "', " . Yii::$app->user->id . ");";
        Yii::$app->db->createCommand($sql)->execute();

        $this->return['message'] = Yii::t('api-logistic', 'Raspunsul a fost salvat cu succes');
        return $this->return;
    }
}