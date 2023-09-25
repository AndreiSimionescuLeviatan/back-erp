<?php

namespace api\modules\v2\controllers;

use api\modules\v2\models\BeMariaQuestion;
use api\modules\v2\models\BeMariaQuestionFeedback;
use api\modules\v2\models\BeMariaSpeech2Text;
use api\modules\v2\models\BeMariaText2Speech;
use common\components\HttpStatus;
use Yii;
use yii\web\HttpException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * RoomReservation controller
 */
class BeMariaController extends RestV2Controller
{
    public $modelClass = 'api\modules\v2\models\BeMariaQuestion';

    /**
     * @return array|mixed
     * @throws HttpException
     */
    public function actionAsk()
    {
        self::$threadName = 'BeMariaController_actionAsk';
        $question = Yii::$app->request->post('question');
        if (empty($question)) {
            $msg = Yii::t('bemaria-api', "BeMaria did not receive any questions. Do you have a question for her?");
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }
        $model = new BeMariaQuestion();
        $model->question = $question;
        $model->ip_address = Yii::$app->request->getUserIP();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    self::error($error[0]);
                    throw new HttpException(409, $error[0]);
                }
            }
            self::error(Yii::t('bemaria-api', "BeMaria question could not be saved and we don't have any validation errors"));
            throw new HttpException(500, Yii::t('bemaria-api', 'Failed to send your question. Please contact an administrator!'));
        }

        $url = 'https://83.103.166.70:8181';
        $command = "curl -k -X POST -d '" . json_encode([
            'question' => $model->question,
            'id' => $model->id
        ]) . "' {$url}";
        $result = exec($command, $output, $resultCode);

        $this->return['answer'] = '';
        $this->return['question'] = $model->question;
        if ($resultCode !== 0) {
            self::error("CURL response details: " . json_encode(['result' => $result, 'output' => $output, 'resultCode' => $resultCode]));
            $model->status = 3;
            $model->observations = json_encode(['result' => $result, 'output' => $output, 'resultCode' => $resultCode]);
            $model->updated = date('Y-m-d H:i:s');
            $model->updated_by = Yii::$app->user->id;
            if (!$model->save()) {
                if ($model->hasErrors()) {
                    foreach ($model->errors as $error) {
                        self::error($error[0]);
                        throw new HttpException(409, $error[0]);
                    }
                }
                self::error(Yii::t('bemaria-api', "BeMaria model could not be updated with curl error details and we don't have any validation errors"));
                throw new HttpException(500, Yii::t('bemaria-api', 'Failed to update your question. Please contact an administrator!'));
            }

            Yii::$app->response->statusCode = 400;
            $this->return['message'] = Yii::t('bemaria-api', 'Huston we have a problem!');
            $this->return['question_id'] = $model->id;
            $this->return['status'] = Yii::$app->response->statusCode;
            return $this->return;
        }

        $model->answer = $result;
        $model->status = 1;
        $model->updated = date('Y-m-d H:i:s');
        $model->updated_by = Yii::$app->user->id;
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    self::error($error[0]);
                    throw new HttpException(409, $error[0]);
                }
            }
            self::error(Yii::t('bemaria-api', "BeMaria answer could not be saved and we don't have any validation errors"));
            throw new HttpException(500, Yii::t('bemaria-api', 'Failed to save question. Please contact an administrator!'));
        }

        $this->return['answer'] = $result;
        $this->return['message'] = Yii::t('bemaria-api', 'BeMaria has an answer for you');
        $this->return['question_id'] = $model->id;
        return $this->return;
    }

    /**
     * @return array|mixed
     * @throws HttpException
     */
    public function actionFeedback()
    {
        self::$threadName = 'BeMariaController_actionFeedback';
        $id = Yii::$app->request->post('id');
        $feedback = Yii::$app->request->post('feedback');

        if (empty($id)) {
            $msg = Yii::t('bemaria-api', 'BeMaria did not receive question id.');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }

        $model = new BeMariaQuestionFeedback();
        $model->question_id = $id;
        $model->feedback = $feedback;
        $model->ip_address = Yii::$app->request->getUserIP();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    self::error($error[0]);
                    throw new HttpException(409, $error[0]);
                }
            }
            self::error(Yii::t('bemaria-api', "BeMaria feedback could not be saved and we don't have any validation errors"));
            throw new HttpException(500, Yii::t('bemaria-api', 'Failed to save feedback. Please contact an administrator!'));
        }

        $this->return['message'] = Yii::t('bemaria-api', 'The answer has been successfully saved');
        return $this->return;
    }

    /**
     * @return array|mixed
     * @throws HttpException
     */
    public function actionSpeech2Text()
    {
        self::$threadName = 'BeMariaController_actionSpeech2Text';
        $text = Yii::$app->request->post('text');
        $type = Yii::$app->request->post('type');

        if (empty($text)) {
            $msg = Yii::t('bemaria-api', 'BeMaria did not receive text.');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }

        $model = new BeMariaSpeech2Text();
        $model->text = $text;
        if (
            !empty($type)
            && in_array($type, [1, 2])
        ) {
            $model->type = $type;
        }
        $model->ip_address = Yii::$app->request->getUserIP();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    self::error($error[0]);
                    throw new HttpException(409, $error[0]);
                }
            }
            self::error(Yii::t('bemaria-api', "BeMaria text could not be saved and we don't have any validation errors"));
            throw new HttpException(500, Yii::t('bemaria-api', 'Failed to save text. Please contact an administrator!'));
        }

        $this->return['db_id'] = $model->id;
        $this->return['message'] = Yii::t('bemaria-api', 'The text has been successfully saved');
        return $this->return;
    }

    /**
     * @return array|mixed
     * @throws HttpException
     */
    public function actionText2Speech()
    {
        self::$threadName = 'BeMariaController_actionText2Speech';
        $text = Yii::$app->request->post('text');
        $textID = Yii::$app->request->post('text_id');

        if (empty($text)) {
            $msg = Yii::t('bemaria-api', 'BeMaria did not receive the written text');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            return [
                'message' => $msg
            ];
        }

        $model = new BeMariaText2Speech();
        $model->text = $text;
        $model->ip_address = Yii::$app->request->getUserIP();
        $model->added = date('Y-m-d H:i:s');
        $model->added_by = Yii::$app->user->id;
        if (!$model->save()) {
            if ($model->hasErrors()) {
                foreach ($model->errors as $error) {
                    self::error($error[0]);
                    throw new HttpException(409, $error[0]);
                }
            }
            self::error(Yii::t('bemaria-api', "BeMaria text could not be saved and we don't have any validation errors"));
            throw new HttpException(500, Yii::t('bemaria-api', 'Failed to save text. Please contact an administrator!'));
        }

        $client = new Client();
        $body = [
            "content" => [$text],
            // "voice" => "ro-RO-AlinaNeural",     // Alina
            "voice" => "ro-RO-Wavenet-A",       // Andreea
            // "voice" => "Carmen",                // Carmen
            // "voice" => "ro-RO-Standard-A",      // Mihaela
            // "voice" => "ro-RO-Andrei",          // Andrei
            // "voice" => "ro-RO-EmilNeural",      // Emil
            "pronunciations" => [
                [
                    "key" => "Play.ht",
                    "value" => "Play dot H T"
                ]
            ],
            "title" => "text-{$model->id}",
            "narrationStyle" => "string",
            "globalSpeed" => "100%",
            "trimSilence" => true
        ];
        $response = $client->request('POST', 'https://play.ht/api/v1/convert', [
            // 'body' => json_encode($body, JSON_UNESCAPED_SLASHES),
            'body' => '{"content":["' . addslashes($text) . '"],"voice":"ro-RO-Wavenet-A","pronunciations":[{"key":"Play.ht","value":"Play dot H T"}],"title":"' . "text-{$model->id}" . '","narrationStyle":"string","globalSpeed":"100%","trimSilence":true}',
            'headers' => [
                'AUTHORIZATION' => 'dbad351c9f064cbb99df37072346ca70',
                'X-USER-ID' => 'YGxWSVMC09QMpMQhW1KusiIfFP93',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);

        if (!in_array($response->getStatusCode(), [200, 201])) {
            $msg = Yii::t('bemaria-api', 'Eroare la comunicarea cu PlayHT - send TTS job request');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::SERVICE_UNAVAILABLE;
            return [
                'message' => $msg,
                'error_code' => $response->getStatusCode(),
                'errors' => [
                    'getReasonPhrase()' => $response->getReasonPhrase(),
                    'getBody()' => $response->getBody(),
                    'getBody()->getContents()' => $response->getBody()->getContents()
                ]
            ];
        }
        $data = json_decode($response->getBody()->getContents(), true);

        if (
            empty($data["status"])
            || $data["status"] != "CREATED"
            || empty($data["transcriptionId"])
        ) {
            $msg = Yii::t('bemaria-api', 'Date incomplete primite de la PlayHT - send TTS job request');
            self::error($msg);
            Yii::$app->response->statusCode = HttpStatus::SERVICE_UNAVAILABLE;
            return [
                'message' => $msg,
                'data' => $data
            ];
        }
        $transcriptionID = $data["transcriptionId"];

        $audioDuration = 0;
        $audioUrl = '';
        $voice = '';

        

        $milisecondsTimeout = 5000;
        $milisecondsToWait = 2000;
        while ($milisecondsTimeout > 0) {
            $response = $client->request('GET', 'https://play.ht/api/v1/articleStatus?transcriptionId=' . $transcriptionID, [
                'headers' => [
                    'AUTHORIZATION' => 'dbad351c9f064cbb99df37072346ca70',
                    'X-USER-ID' => 'YGxWSVMC09QMpMQhW1KusiIfFP93',
                    'accept' => 'application/json',
                ],
            ]);
            if (in_array($response->getStatusCode(), [200, 201])) {
                $data = json_decode($response->getBody()->getContents(), true);

                if (
                    !empty($data["converted"])
                    && $data["converted"]
                    && !empty($data["audioDuration"])
                    && !empty($data["audioUrl"])
                    && !empty($data["voice"])
                ) {
                    $audioDuration = $data["audioDuration"];
                    $audioUrl = $data["audioUrl"];
                    $voice = $data["voice"];

                    break;
                }
            }

            $milisecondsTimeout -= $milisecondsToWait;
            usleep($milisecondsToWait * 1000);
        }

        $this->return['db_id'] = $model->id;
        $this->return['client_id'] = $textID;
        $this->return['mp3'] = $audioUrl;
        $this->return['data'] = [
            'transcriptionID' => $transcriptionID,
            'audioDuration' => $audioDuration,
            'audioUrl' => $audioUrl,
            'voice' => $voice
        ];
        $this->return['message'] = Yii::t('bemaria-api', 'The written text has been successfully saved');
        return $this->return;
    }
}