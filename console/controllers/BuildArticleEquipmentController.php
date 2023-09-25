<?php

namespace console\controllers;

use api\models\Speciality;
use backend\components\MailSender;
use backend\modules\adm\models\Settings;
use backend\modules\adm\models\User;
use backend\modules\build\models\Article;
use backend\modules\build\models\Equipment;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class BuildArticleEquipmentController extends Controller
{
    /**
     * @var int
     * The delay that the notifications should be taken from DB.
     * The default value is 3600 seconds(1hour) witch means that the query that retrieves the changes
     * will include only changes from last hour
     */
    public $delay = 3600;//default delay is 1 hour in seconds

    public function actionIndex()
    {
        User::setUsers(true);

        $this->setViewPath('@app/mail');
        Yii::info("\nCreate Article/Equipment cron service is running...", 'buildArticleEquipmentCreate');
        $now = time();
        $changesStartTime = date('Y-m-d H:i:s', ($now - $this->delay));
        $changesStopTime = date('Y-m-d H:i:s', $now);

        try {
            $specialities = Speciality::find()->select('id')->indexBy('code')->where(['deleted' => 0])->column();
            $settings = Settings::find()->where("`name` like 'ARTICLE_EQUIPMENT_CREATE_%'")->asArray()->all();
            if (empty($settings)) {
                throw new Exception(Yii::t('app', 'Missing receivers emails'));
            }

            foreach ($settings as $setting) {
                $receiversList = explode(",", $setting['value']);

                foreach ($receiversList as $receiver) {
                    $receiver = User::findByEmail($receiver);
                    if (empty($receiver)) {
                        continue;
                    }

                    $specialityCode = str_replace('ARTICLE_EQUIPMENT_CREATE_', '', $setting['name']);
                    $specialityID = $specialities[$specialityCode];

                    $subject = Yii::t('app', 'Articles/Equipment created in the last hour, which require validation');
                    $articles = Article::find()->with('speciality')->where(['deleted' => 0, 'speciality_id' => $specialityID])
                        ->asArray()->andWhere(['>=', 'added', $changesStartTime])->all();
                    $equipments = Equipment::find()->with('speciality')->where(['deleted' => 0, 'speciality_id' => $specialityID])
                        ->asArray()->andWhere(['>=', 'added', $changesStartTime])->all();

                    if (!empty($articles) || !empty($equipments)) {
                        $mailBody = $this->renderPartial('build-article-equipment-html', [
                            'articles' => $articles,
                            'equipments' => $equipments,
                            'receiver' => $receiver,
                            'changesStartTime' => $changesStartTime,
                            'changesStopTime' => $changesStopTime,
                            'specialityID' => $specialityID
                        ]);

                        MailSender::sendMail($subject, $mailBody, $receiver);
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::debug($exc->getMessage(), 'buildArticleEquipmentCreate');
            return ExitCode::OK;
        }

        return true;
    }
}