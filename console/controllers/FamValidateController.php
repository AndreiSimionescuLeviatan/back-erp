<?php

namespace console\controllers;

use backend\modules\build\models\Article;
use backend\modules\build\models\Equipment;
use backend\modules\design\models\Project;
use backend\modules\design\models\Speciality;
use backend\modules\fam\models\Fam;
use backend\modules\fam\models\FamItem;
use backend\modules\fam\models\FamProjectApproval;
use backend\modules\fam\models\FamValidateDesign;
use backend\modules\fam\models\FamVersion;
use backend\modules\fam\models\ProjectApproval;
use backend\modules\fam\models\ProjectApprovalDesigner;
use backend\components\MailSender;
use backend\modules\adm\models\User;
use Microsoft\Graph\Exception\GraphException;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

class FamValidateController extends Controller
{
    /**
     * @throws GraphException
     * @throws Exception
     */
    public function actionIndex()
    {
        $this->setViewPath('@app/mail');
        Yii::info(Yii::t('app', "\nValidate FAM cron service is running..."), 'famValidate');

        try {
            $tblFam = Fam::tableName();
            $tblFamProjectApproval = FamProjectApproval::tableName();
            $tblProjectApproval = ProjectApproval::tableName();
            $tblProjectApprovalDesign = ProjectApprovalDesigner::tableName();
            $tblFamItem = FamItem::tableName();
            $tblProject = Project::tableName();
            $tblSpeciality = Speciality::tableName();
            $tblEquipment = Equipment::tableName();
            $tblArticle = Article::tablename();
            $sqlLastVersions = FamVersion::getLastVersions();
            $lastDesignValidation = FamValidateDesign::getSQLValidateDesign();

            $sql = "SELECT * FROM (
                            SELECT `fv`.`id` AS `fam_version_id`, GROUP_CONCAT(DISTINCT `pad`.`user_id`) as `designer_ids`,
                            IFNULL(`fvd`.`response`,0) AS `response`, `s`.`name` As `speciality_name`
                            FROM {$tblFam} `f`
                            INNER JOIN ({$sqlLastVersions}) `fv` ON `fv`.`fam_id` = `f`.`id`
                            LEFT JOIN {$tblFamProjectApproval} `fpa` ON  `fpa`.`fam_version_id` = `fv`.`id`
                            INNER JOIN {$tblProjectApproval} `pa` ON `pa`.`id` = `fpa`.`project_approval_id`
                            INNER JOIN {$tblProjectApprovalDesign} `pad` ON `pad`.`project_approval_id` = `pa`.`id`
                            LEFT JOIN ({$lastDesignValidation}) `fvd` ON `fvd`.`fam_project_approval_id` = `fpa`.`id`
                            INNER JOIN {$tblSpeciality} `s` ON `s`.`id` = `pa`.`speciality_id`
                            WHERE `fv`.`deleted` = 0 
                            GROUP BY `f`.`project_id`, `f`.`number`, `fv`.`version`, `speciality_name`
                ) `fv` WHERE `fv`.`response` = " . FamVersion::RESPONSE_DEFAULT . " ";

            $dataSet = FamVersion::queryAll($sql);
            $result = '';
            foreach ($dataSet as $data) {
                $result .= $data['designer_ids'] . ',';
            }
            $designerIds = array_unique(explode(',', rtrim($result, ",")));

            if (empty($designerIds)) {
                throw new Exception(Yii::t('app', 'Missing receivers emails'));
            }

            foreach ($designerIds as $designer) {
                $receiver = User::findOneByAttributes(['id' => $designer]);
                if (empty($receiver)) {
                    continue;
                }
                $sql = "SELECT * FROM (
                            SELECT `fv`.`id` AS `fam_version_id`, `p`.`name` AS `project_name`,  `s`.`name` AS `speciality_name`,
                                CONCAT('FAM ', `f`.`number`) AS `code`,
                                CONCAT(
                                    IFNULL( CONCAT(GROUP_CONCAT(DISTINCT `a`.`code` SEPARATOR ' '), ' '),' '), 
                                    IFNULL(GROUP_CONCAT(DISTINCT `e`.`code` SEPARATOR ' '),' ')) as item_codes,
                                IFNULL(`fvd`.`response`,0) AS `response`,
                                CONCAT(',', GROUP_CONCAT(DISTINCT `pad`.`user_id`), ',') as `designer_ids`
                            FROM {$tblFam} `f`
                            INNER JOIN ({$sqlLastVersions}) `fv` ON `fv`.`fam_id` = `f`.`id`
                            LEFT JOIN {$tblFamProjectApproval} `fpa` ON  `fpa`.`fam_version_id` = `fv`.`id`
                            INNER JOIN {$tblProjectApproval} `pa` ON `pa`.`id` = `fpa`.`project_approval_id`
                            INNER JOIN {$tblProjectApprovalDesign} `pad` ON `pad`.`project_approval_id` = `pa`.`id`
                            LEFT JOIN ({$lastDesignValidation}) `fvd` ON `fvd`.`fam_project_approval_id` = `fpa`.`id`
                            INNER JOIN {$tblFamItem} `fi` ON `fv`.`id` = `fi`.`fam_version_id`
                            INNER JOIN {$tblProject} `p` ON `f`.`project_id`=`p`.`id`
                            INNER JOIN {$tblSpeciality} `s` ON `s`.`id` = `pa`.`speciality_id`
                            LEFT JOIN {$tblEquipment} `e` ON `fi`.`item_id` = `e`.`id` AND `fi`.`item_type` <> " . FamVersion::ARTICLE_TYPE . "
                            LEFT JOIN {$tblArticle} `a` ON `fi`.`item_id` = `a`.`id` AND `fi`.`item_type` = " . FamVersion::ARTICLE_TYPE . "
                            WHERE `fv`.`deleted` = 0 
                            GROUP BY `f`.`project_id`, `f`.`number`, `fv`.`version`, `speciality_name`
                        ) `fv` WHERE `fv`.`response` = " . FamVersion::RESPONSE_DEFAULT . " AND `fv`.`designer_ids` LIKE '%,{$receiver->id},%'";

                $data = FamVersion::queryAll($sql);
                if (empty($data)) {
                    continue;
                }
                $result = [];
                $i = 0;
                foreach ($data as $item) {
                    $result[$i]['project'] = $item['project_name'] . ' - ' . $item['speciality_name'];
                    $result[$i]['fam'] = $item['code'] . ' - ' . $item['item_codes'];
                    $result[$i]['fam_version_id'] = $item['fam_version_id'];
                    $i++;
                }
                asort($result);
                $subject = Yii::t('app', 'Validation of FAMs');

                $mailBody = $this->renderPartial('fam-validate-html', [
                    'data' => $result,
                    'receiver' => $receiver,
                ]);

                MailSender::sendMail($subject, $mailBody, $receiver);
            }

        } catch (\Exception $exc) {
            Yii::warning($exc->getMessage() . $exc->getTraceAsString(), 'famValidate');
            return ExitCode::OK;
        }
        return true;
    }
}