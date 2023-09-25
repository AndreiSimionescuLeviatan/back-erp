<?php

namespace console\controllers;

use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use console\models\Invoice;
use console\models\InvoiceDownloadTask;
use backend\modules\finance\models\MSSharePoint;

class FinanceInvoiceDownloadHeaderController extends Controller
{

    public function actionIndex()
    {
        ini_set("memory_limit", "4096M");
        set_time_limit(0);

        $tableName = InvoiceDownloadTask::tableName();

        $tasks = $this->getTasks();
        if (empty($tasks)) {
            $this->log('No tasks to get HEADER details from SharePoint for');
            return ExitCode::OK;
        }

        $processed = 1;
        foreach ($tasks as $task) {
            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Processing {$processed} of " . count($tasks) . ' ...');

            $req =
                '' . DIRECTORY_SEPARATOR .
                '' . LEVIATAN_DOMAIN .
                '' . DIRECTORY_SEPARATOR .
                '' . LEVIATAN_SITE .
                '' . DIRECTORY_SEPARATOR .
                '' . INVOICE_HEADER_RAW_LIST_ID .
                '' . DIRECTORY_SEPARATOR .
                '' . INVOICE_HEADER_RAW_FIELDS .
                "&filter=fields/Task_Id eq {$task->id}";

            $data = MSSharePoint::sendRequest($req);
            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Data from SharePoint: " . json_encode($data));

            if (empty($data)) {
                $msg = 'No HEADER details found in SharePoint';
                $attributes = [
                    'status_header' => InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_ERRORS,
                    'observations_header' => $msg,
                    'retries_header' => $task->retries_header - 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];
                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - " . $msg);

                $processed++;
                continue;
            }

            $invoiceHeaderRow = $this->prepareInvoiceHeaderRow($data);
            if (empty($invoiceHeaderRow)) {
                $msg = 'No HEADER details prepared found in SharePoint';
                $attributes = [
                    'status_header' => InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_ERRORS,
                    'observations_header' => $msg,
                    'retries_header' => $task->retries_header - 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];
                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - " . $msg);

                $processed++;
                continue;
            }

            $finishedWithSuccess = true;

            try {
                $output = $task->saveHeaderDetails($invoiceHeaderRow);
                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Save Header Details Output: " . json_encode($output));
            } catch (\Exception $exc) {
                $msg = $exc->getMessage();

                $attributes = [
                    'status_header' => InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_ERRORS,
                    'observations_header' => $msg,
                    'retries_header' => $task->retries_header - 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];
                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Exception: " . $msg);

                $finishedWithSuccess = false;

                break;
            }

            $processed++;

            if ($finishedWithSuccess) {
                $msg = 'Updated HEADER details successfully';

                $attributes = [
                    'status_header' => InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_SUCCESS,
                    'sharepoint_invoice_header_id' => $task->sharepoint_invoice_header_id,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];

                $updatedTask = InvoiceDownloadTask::findOneByAttributes([
                    'id' => $task->id
                ]);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Body status is: {$updatedTask->status_body}");

                if (
                    $updatedTask !== null
                    && $updatedTask->status_body == InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_SUCCESS
                ) {
                    $attributes['status'] = InvoiceDownloadTask::STATUS_FINISHED_WITH_SUCCESS;

                    if ($task->invoice === null) {
                        $task->setInvoice();
                    }

                    if ($task->invoice === null) {
                        $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Invoice not found");
                    } else {
                        $updated = $task->invoice->updateByAttributes([
                            'status' => Invoice::STATUS_EXTRACTED,
                            'updated' => date('Y-m-d H:i:s'),
                            'updated_by' => Yii::$app->params['superAdmin']
                        ]);

                        if ($updated) {
                            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Invoice status successfully set to EXTRACTED");
                        } else {
                            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Could not set invoice status to EXTRACTED");
                        }
                    }
                } else {
                    $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - No need to update the invoice status");
                }

                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - " . $msg);
                continue;
            }
            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Unknown behaviour");
        }

        return ExitCode::OK;
    }

    public function getTasks()
    {
        $tableName = InvoiceDownloadTask::tableName();

        $sql = "SELECT id FROM {$tableName} ";
        $sql .= 'WHERE (status_header IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_HEADER_NEW,
                InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_ERRORS
            ]) . ') ';
        $sql .= 'OR (status_header IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_HEADER_IN_PROGRESS
            ]) . ')
            AND added > CURDATE() - INTERVAL 1 DAY)) ';
        $sql .= 'OR (status IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_IN_PROGRESS
            ]) . ')
              AND added > CURDATE() - INTERVAL 1 DAY) ';
        $sql .= ' AND retries_header > 0 ';
        $sql .= ' ORDER BY id DESC ';
        $sql .= ' LIMIT ' . InvoiceDownloadTask::$sharePointPageSize;
        $sql .= ';';

        $rows = InvoiceDownloadTask::queryAll($sql);
        if (empty($rows)) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }
        if (empty($ids)) {
            return [];
        }

        $sql = "UPDATE {$tableName} ";
        $sql .= ' SET status_header = ' . InvoiceDownloadTask::STATUS_HEADER_IN_PROGRESS;
        $sql .= ', status = ' . InvoiceDownloadTask::STATUS_IN_PROGRESS;
        $sql .= ' WHERE id IN (' . implode(',', $ids) . ') ';
        $sql .= ';';
        InvoiceDownloadTask::execute($sql);

        return InvoiceDownloadTask::find()->where([
            'in', 'id', $ids
        ])->orderBy(['id' => SORT_DESC])->all();
    }

    public function prepareInvoiceHeaderRow($data)
    {
        foreach ($data as $rowInvoiceHeaderRaw) {
            $arrRowHeaderRaw = json_decode(json_encode($rowInvoiceHeaderRaw), true);
            $fieldsInvoiceHeaderRaw = $arrRowHeaderRaw['fields'];

            if (
                empty($fieldsInvoiceHeaderRaw)
                || empty($fieldsInvoiceHeaderRaw['Invoice_Id'])
                || empty($fieldsInvoiceHeaderRaw['Task_Id'])
            ) {
                continue;
            }

            return $fieldsInvoiceHeaderRaw;
        }

        return [];
    }

    private function log($message)
    {
        echo $this->currentDateTime() . " - {$message}" . "\n";
    }

    private function currentDateTime()
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        return $now->format("Y-m-d H-i-s.u");
    }
}
