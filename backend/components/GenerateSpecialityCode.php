<?php

namespace backend\components;

use backend\modules\build\models\Article;
use backend\modules\build\models\Equipment;
use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\web\BadRequestHttpException;

class GenerateSpecialityCode extends Behavior
{
    /**
     * @return string[]
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate'
        ];
    }

    /**
     * @param $event
     * @return void
     * @throws BadRequestHttpException
     */
    public function beforeValidate($event)
    {
        if ($event->sender->generateItemCode === false) {
            return;
        }

        if (!empty($event->sender->equipment_type)) {
            $initialCode = 'D';
            if ($event->sender->equipment_type == '1') {
                $initialCode = 'E' . $event->sender->speciality->code;
            }
            $entity = Equipment::find()->where("code LIKE '{$initialCode}%'")->orderBy('LENGTH(code) DESC, code DESC')->one();
        } else {
            if (empty($event->sender->speciality)) {
                throw new BadRequestHttpException(Yii::t('app', "Could not determine the item speciality. Please contact an administrator!"));
                /**
                 * @todo check if is another way to add error to model instead of `throw new BadRequestHttpException`
                 */
                //$event->sender->addError('speciality_id', Yii::t('app', "Could not determine the item speciality. Please contact an administrator!"));
            }
            $initialCode = $event->sender->speciality->code;
            $entity = Article::find()->where("code LIKE '{$initialCode}%'")->orderBy('LENGTH(code) DESC, code DESC')->one();
        }

        if (!empty($entity)) {
            $lastEntityNumber = str_replace($initialCode, '', $entity->code);
            $newEntityNumber = $lastEntityNumber + 1;
        } else {
            $newEntityNumber = 1;
        }
        $newEntityCode = $initialCode . $newEntityNumber;
        $event->sender->code = $newEntityCode;
    }

}