<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use console\models\Invoice;
use console\models\InvoiceHeaderFormatted;
use console\models\InvoiceInitialImage;

class FinanceInvoiceCloneToClassifiedFilesController extends Controller
{
    public function actionIndex()
    {
        $invoices = Invoice::findByAttributes([
            'deleted' => 0
        ], true);
        echo count($invoices) . "\n";

        $status = InvoiceInitialImage::CONDITION_INVOICES_TYPING;
        $imagesDir = Yii::getAlias('@backend/' . Yii::$app->params['financeImagesToBeClassifiedUploadDir']) . "/{$status}";
        if (!is_dir($imagesDir)) {
            echo "Invoice {$invoice->id}: directory {$imagesDir} not found" . "\n";
            exit(1);
        }

        foreach ($invoices as $invoice) {
            echo "Invoice {$invoice->id}: start cloning" . "\n";

            $invoiceFileName = !empty($invoice->image_name) ? $invoice->image_name : "{$invoice->id}.png";

            // check if file exist
            if ($invoice->company === null) {
                $invoice->setCompany();
            }
            $rootFile = "/{$invoice->company->cui}/";
            $rootFile .= "{$invoice->number_of_records}/{$invoiceFileName}";

            $localFile = '/var/www/ecf-erp/backend/' . Yii::$app->params['invoiceUploadDir'] . $rootFile;
            if (!file_exists($localFile)) {
                echo "Invoice {$invoice->id}: file {$localFile} not found" . "\n";
                continue;
            }

            // compute the hash of the file
            $hash = hash_file('sha256', $localFile);
            echo "Invoice {$invoice->id}: file hash: {$hash}" . "\n";

            // check if the hash exist in the classify files
            $invoiceInitialImage = InvoiceInitialImage::findByAttributes([
                'image_hash' => $hash
            ]);
            if ($invoiceInitialImage !== null) {
                echo "Invoice {$invoice->id}: was already cloned" . "\n";
                continue;
            }

            // copy file
            echo "Invoice {$invoice->id}: cloning file" . "\n";
            if (!copy($localFile, "{$imagesDir}/{$invoiceFileName}")) {
                echo "Invoice {$invoice->id}: could not copy file" . "\n";
                continue;
            }
            if (!file_exists("{$imagesDir}/{$invoiceFileName}")) {
                echo "Invoice {$invoice->id}: copied file {$imagesDir}/{$invoiceFileName} not found" . "\n";
                continue;
            }

            $newHash = hash_file('sha256', "{$imagesDir}/{$invoiceFileName}");
            echo "Invoice {$invoice->id}: file hash: {$newHash}" . "\n";

            $attributes = [
                'invoice_id' => $invoice->id,
                'is_invoice' => 1,
                'status' => $status,
                'vendor_company_id' => $invoice->vendor_company_id,
                'customer_company_id' => $invoice->customer_company_id,
                'number_of_records' => $invoice->number_of_records,
                'image_name' => $invoiceFileName,
                'image_hash' => $newHash,
                'added' => '2021-12-31 23:59:59',
                'added_by' => $invoice->added_by
            ];
            $invoiceHeaderFormatted = InvoiceHeaderFormatted::find()->select('number_invoice, date_invoice')->where([
                'invoice_id' => $invoice->id
            ])->one();
            if ($invoiceHeaderFormatted !== null) {
                $attributes['number_invoice'] = $invoiceHeaderFormatted->number_invoice;
                $timestamp = strtotime("{$invoiceHeaderFormatted->date_invoice} 00:00:00");
                if ($timestamp) {
                    $attributes['year'] = date('Y', $timestamp);
                    $attributes['month'] = date('m', $timestamp);
                    $attributes['day'] = date('d', $timestamp);
                    $attributes['added'] = date('Y-m-01 H:i:s', $timestamp);
                }
            }
            echo "Invoice {$invoice->id}: attributes: " . json_encode($attributes) . "\n";
            InvoiceInitialImage::createByAttributes($attributes);

            echo "Invoice {$invoice->id}: done" . "\n";
        }

        return ExitCode::OK;
    }
}
