<?php

namespace api\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eval_employee_grade".
 *
 * @property Employee $employee
 */
class EvalEmployeeGrade extends EvalEmployeeGradeParent
{
    public static $generalGradeRates = [
        1 => [
            'threshold' => null, //1
            'name' => 'GENERAL_GRADE_MILESTONE_1',
            'image' => 'circle-rating-1.png'
        ],
        2 => [
            'threshold' => null, //1.5
            'name' => 'GENERAL_GRADE_MILESTONE_2',
            'image' => 'circle-rating-2.png'
        ],
        3 => [
            'threshold' => null, //2.5
            'name' => 'GENERAL_GRADE_MILESTONE_3',
            'image' => 'circle-rating-3.png'
        ],
        4 => [
            'threshold' => null, //3.5
            'name' => 'GENERAL_GRADE_MILESTONE_4',
            'image' => 'circle-rating-4.png'
        ],
        5 => [
            'threshold' => null, //4.5
            'name' => 'GENERAL_GRADE_MILESTONE_5',
            'image' => 'circle-rating-5.png'
        ],
        6 => [
            'threshold' => null, //5
            'name' => 'GENERAL_GRADE_MILESTONE_6'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return MYSQL_DB_MODULE_ECF_HR . '.eval_employee_grade';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('ecf_hr_db');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ]);
    }

    /**
     * @throws Exception
     */
    public static function getEmployeeGeneralGradeIcon($employeeId)
    {
        $generalGradeModel = EvalEmployeeGrade::find()->where('employee_id = :employee_id', [
            ':employee_id' => $employeeId
        ])->one();
        if ($generalGradeModel === null) {
            return null;
        }
        if (empty($generalGradeModel->grade)) {
            return null;
        }

        self::setGeneralGradeThresholds();
        return self::compareGradeWithThresholds($generalGradeModel->grade);
    }

    /**
     * @throws Exception
     */
    public static function setGeneralGradeThresholds()
    {
        foreach (self::$generalGradeRates as $key => $value) {
            self::$generalGradeRates[$key]['threshold'] = Settings::getValue($value['name']);
        }

        foreach (self::$generalGradeRates as $key => $value) {
            if ($value['threshold'] === null) {
                throw new Exception(Yii::t('api-hr', 'The interval limits for general grade are not set'));
            }
        }
    }

    /**
     * @param $grade
     * @return string|null
     */
    public static function compareGradeWithThresholds($grade)
    {
        $baseImagesPath = 'images/evaluation-img/';
        $totalGradeRates = count(self::$generalGradeRates);

        for ($i = 1; $i <= $totalGradeRates - 1; $i++) {
            if (
                $grade < self::$generalGradeRates[$totalGradeRates - 1]['threshold']
                && self::gradesUnderLastIntervalConditions($i, $grade)
            ) {
                return $baseImagesPath . self::$generalGradeRates[$i]['image'];
            }
            if (
                $grade >= self::$generalGradeRates[$totalGradeRates - 1]['threshold']
                && self::gradesAtLastIntervalConditions($i, $grade)
            ) {
                return $baseImagesPath . self::$generalGradeRates[$i]['image'];
            }
        }
        return null;
    }

    /**
     * @param $key
     * @param $grade
     * @return bool
     */
    public static function gradesUnderLastIntervalConditions($key, $grade)
    {
        if (
            self::$generalGradeRates[$key]['threshold'] <= $grade
            && $grade < self::$generalGradeRates[$key + 1]['threshold']
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $key
     * @param $grade
     * @return bool
     */
    public static function gradesAtLastIntervalConditions($key, $grade)
    {
        if (
            self::$generalGradeRates[$key]['threshold'] <= $grade
            && $grade <= self::$generalGradeRates[$key + 1]['threshold']
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'employee_id']);
    }
}
