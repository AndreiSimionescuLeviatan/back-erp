<?php

namespace api\modules\v1\models;

use common\components\HttpStatus;
use common\components\MicrosoftGraphMailHelper;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\validators\EmailValidator;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class MailHelper extends MicrosoftGraphMailHelper
{
    /**
     * @param $data
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function sendEmailNotification($data)
    {
        try {
            $this->validateInput($data);
        } catch (BadRequestHttpException $exc) {
            throw new BadRequestHttpException($exc->getMessage());
        }
        $toList = explode(',', $data['to']);
        $validToList = $this->validateEmailsList($toList);
        if (empty($validToList)) {
            throw new BadRequestHttpException(Yii::t('app-notification', 'Emails are not valid') . '!');
        }

        $this->subject = $data['subject'];

        $this->content = [
            "contentType" => "html",
            "content" => $this->alignNewLineText($data['content'])
        ];

        $this->toRecipients = [];
        foreach ($validToList as $emailTO) {
            $this->toRecipients[] = [
                "emailAddress" => [
                    "name" => '',
                    "address" => $emailTO,
                ]
            ];
        }

        $this->ccRecipients = [];
        if (!empty($data['cc'])) {
            $ccList = explode(',', $data['cc']);
            $validCcList = $this->validateEmailsList($ccList);
            if (!empty($validCcList)) {
                foreach ($validCcList as $emailTO) {
                    $this->ccRecipients[] = [
                        "emailAddress" => [
                            "name" => '',
                            "address" => $emailTO,
                        ]
                    ];
                }
            }
        }

        $uploadFile = UploadedFile::getInstancesByName("attachment");
        if (!empty($uploadFile)) {
            $this->createAttachmentContent($uploadFile);
        }
        return $this->sendEmail();
    }

    /**
     * @param $text
     * @return mixed|string
     * format text input
     */
    public function alignNewLineText($text)
    {
        if ($text === null) {
            return Yii::t('app-notification', 'Missing content');
        }
        return nl2br($text);
    }

    /**
     * @param $uploadFile
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     * add and save attachment
     */
    public function addAttachment($uploadFile)
    {
        if (empty($uploadFile)) {
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            $this->return['message'] = Yii::t('app-notification', 'Some error occurred while uploading the document, document is missing, please try again');
            return $this->return;
        }

        $filePath = Yii::getAlias("@backend/upload/email-files/");
        if (!is_dir($filePath)) {
            FileHelper::createDirectory($filePath);
        }
        $fileName = "{$uploadFile->name}";
        if ($uploadFile->size > '10485760') {
            Yii::$app->response->statusCode = HttpStatus::BAD_REQUEST;
            $this->return['status'] = HttpStatus::BAD_REQUEST;
            $this->return['message'] = Yii::t('app-notification', "The document is too large, please try again with a smaller document");
            return $this->return;
        } else {
            $uploadFile->saveAs($filePath . $fileName);

            $fileAttach = Yii::getAlias($uploadFile);
            if (!empty($fileAttach)) {
                return $fileAttach;
            } else {
                throw new NotFoundHttpException(Yii::t('app-notification', "{file} is not found", ['file' => $uploadFile]) . "!");
            }
        }
    }

    /**
     * @param $uploadFile
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function createAttachmentContent($uploadFile)
    {
        foreach ($uploadFile as $upload) {
            $attach = $this->addAttachment($upload);
            $getFile = Yii::getAlias('@backend/upload/email-files/' . $attach->name);
            $tmpfile_contents = file_get_contents($getFile);
            $base64 = base64_encode($tmpfile_contents);
            $this->attachments[] =
                [
                    '@odata.type' => "#microsoft.graph.fileAttachment",
                    "name" => $attach->name,
                    "contentType" => $attach->type,
                    "contentBytes" => chunk_split($base64)
                ];
        }
    }

    /**
     * @param $data
     * @return bool
     * @throws BadRequestHttpException
     */
    public function validateInput($data)
    {
        if (empty($data['to'])) {
            throw new BadRequestHttpException(Yii::t('app-notification', 'No emails address send to set') . '!');
        }

        if (empty($data['subject'])) {
            throw new BadRequestHttpException(Yii::t('app-notification', 'No email subject set') . '!');
        }

        if (empty($data['content'])) {
            throw new BadRequestHttpException(Yii::t('app-notification', 'No email content set') . '!');
        }

        return true;
    }

    /**
     * @param $list
     * @return array
     */
    public function validateEmailsList($list)
    {
        $validList = [];
        if (
            !is_array($list)
            || empty($list)
        ) {
            return $validList;
        }
        $validator = new EmailValidator();

        foreach ($list as $email) {
            $email = trim($email);

            if (!$validator->validate($email)) {
                continue;
            }
            $validList[] = $email;
        }

        return $validList;
    }
}