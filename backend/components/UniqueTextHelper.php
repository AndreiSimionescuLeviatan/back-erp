<?php

namespace backend\components;

use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\validators\Validator;
use yii\web\BadRequestHttpException;

class UniqueTextHelper extends Validator
{
    public $whereParams;
    public $triggerErrorMinSimilarity;
    /**
     * @var mixed
     */
    private $whereParamsJunction = 'and';
    private $maxFoundSimilarityPercent = 0;
    private $foundSimilarModel = [];
    private $errMsg = null;

    /**
     * @param $model
     * @param $attribute
     * @return void
     */
    public function validateAttribute($model, $attribute)
    {

        if (!property_exists($model, 'isFlaggedAsDuplicate') || !property_exists($model, 'save_anyway')) {
            $errMsg = "One important property is not set in your application. Please contact an administrator!";
            $this->addError($model, $attribute, $errMsg);
        }

        if ($model->save_anyway) {
            return;
        }

        $targetClass = get_class($model);

        $conditions = $this->prepareQueryCondition($model);
        $query = $targetClass::find();
        $query->andWhere($conditions);
        $possibleRelatedModels = $query->asArray()->all();

        $currentItem = preg_replace("/[^A-Za-z0-9 ]/", '', $model[$attribute]);
        foreach ($possibleRelatedModels as $possibleRelatedModel) {
            $possibleRelated = preg_replace("/[^A-Za-z0-9 ]/", '', $possibleRelatedModel[$attribute]);
            if ($model['id'] === $possibleRelatedModel['id'])//dont trigger an error when we edit the model
                continue;
            similar_text($currentItem, $possibleRelated, $perc);
            if ($perc > $this->triggerErrorMinSimilarity && $perc > $this->maxFoundSimilarityPercent) {
                $model->isFlaggedAsDuplicate = true;
                $this->maxFoundSimilarityPercent = $perc;
                $this->foundSimilarModel = $possibleRelatedModel;

                $this->errMsg = \Yii::t('app', "The name of the item you want to add is {similarity}% similar to another item!<br/>");
                $this->errMsg .= \Yii::t('app', "New item name:<br/><span class='text-warning'>{newName}</span><br/>");
                $this->errMsg .= \Yii::t('app', "Existing item name:<br/><span class='text-warning'>{existingName}</span><br/>");
                $this->errMsg .= Html::a(\Yii::t('app', "View existing item"), $attribute == 'name' ?
                    Url::to(['article/view', 'id' => $possibleRelatedModel['id']]) :
                    Url::to(['equipment/view', 'id' => $possibleRelatedModel['id']]), ['target' => 'blank']);
            }
            //if we already have an article 100% similar we can stop and display the error
            if ($this->maxFoundSimilarityPercent === 100)
                break;
        }

        //throw a validation error only if in list we found an article that is similar over our set value
        if ($this->maxFoundSimilarityPercent > $this->triggerErrorMinSimilarity)
            $this->addError($model, $attribute,
                $this->errMsg,
                [
                    'similarity' => round($this->maxFoundSimilarityPercent, 2),
                    'newName' => $model[$attribute],
                    'existingName' => $this->foundSimilarModel[$attribute],
                ]
            );
    }

    /**
     * @param $model
     * @return string[]
     */
    private function prepareQueryCondition($model)
    {
        $conditions = [$this->whereParamsJunction === 'or' ? 'or' : 'and'];
        if (is_array($this->whereParams) && !empty($this->whereParams)) {
            foreach ($this->whereParams as $key => $whereParam) {
                if (is_numeric($key)) {
                    $conditions[] = [$whereParam => $model[$whereParam]];
                } else {
                    $conditions[] = [$key => $whereParam];
                }

            }
        }
        return $conditions;
    }
}