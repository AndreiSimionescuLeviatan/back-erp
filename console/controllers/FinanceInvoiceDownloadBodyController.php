<?php

namespace console\controllers;

use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use console\models\Invoice;
use console\models\InvoiceDownloadTask;
use backend\modules\finance\models\MSSharePoint;

class FinanceInvoiceDownloadBodyController extends Controller
{

    public function actionIndex()
    {
        ini_set("memory_limit", "4096M");
        set_time_limit(0);

        // $req =
        //     '' . DIRECTORY_SEPARATOR .
        //     '' . LEVIATAN_DOMAIN .
        //     '' . DIRECTORY_SEPARATOR .
        //     '' . LEVIATAN_SITE .
        //     '' . DIRECTORY_SEPARATOR .
        //     '' . INVOICE_BODY_RAW_LIST_ID .
        //     '' . DIRECTORY_SEPARATOR .
        //     '' . INVOICE_BODY_RAW_FIELDS .
        //     "&orderby=ID desc";

        // $data = MSSharePoint::sendRequest($req);
        // $invoiceBodyRows = $this->prepareInvoiceBodyRows($data);
        // $this->log(count($invoiceBodyRows));

        // foreach ($invoiceBodyRows as $row) {
        //     $this->log(json_encode($row));
        //     break;
        // }        

        // return ExitCode::OK;

        $tableName = InvoiceDownloadTask::tableName();

        $tasks = $this->getTasks();
        if (empty($tasks)) {
            $this->log('No tasks to get BODY details from SharePoint for');
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
                '' . INVOICE_BODY_RAW_LIST_ID .
                '' . DIRECTORY_SEPARATOR .
                '' . INVOICE_BODY_RAW_FIELDS .
                "&filter=fields/Task_Id eq {$task->id}";

            $data = MSSharePoint::sendRequest($req);
            $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Data from SharePoint: " . json_encode($data));

            if (empty($data)) {
                $msg = 'No BODY details found in SharePoint';
                $attributes = [
                    'status_body' => InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_ERRORS,
                    'observations_body' => $msg,
                    'retries_body' => $task->retries_body - 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];
                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - " . $msg);

                $processed++;
                continue;
            }

            $invoiceBodyRows = $this->prepareInvoiceBodyRows($data);
            if (empty($invoiceBodyRows)) {
                $msg = 'No BODY details prepared found in SharePoint';
                $attributes = [
                    'status_body' => InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_ERRORS,
                    'observations_body' => $msg,
                    'retries_body' => $task->retries_body - 1,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];
                $task->updateByAttributes($attributes);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - " . $msg);

                $processed++;
                continue;
            }

            $finishedWithSuccess = true;
            foreach ($invoiceBodyRows as $row) {
                try {
                    $output = $task->saveBodyRow($row);
                    $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Save Body Row Output: " . json_encode($output));
                } catch (\Exception $exc) {
                    $msg = $exc->getMessage();

                    $attributes = [
                        'status_body' => InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_ERRORS,
                        'observations_body' => $msg,
                        'retries_body' => $task->retries_body - 1,
                        'updated' => date('Y-m-d H:i:s'),
                        'updated_by' => Yii::$app->params['superAdmin']
                    ];
                    $task->updateByAttributes($attributes);
    
                    $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Exception: " . $msg);

                    $finishedWithSuccess = false;
                    
                    break;
                }
            }
            $processed++;

            if ($finishedWithSuccess) {
                $msg = 'Updated BODY details successfully';

                $attributes = [
                    'status_body' => InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_SUCCESS,
                    'sharepoint_invoice_body_id' => $task->sharepoint_invoice_body_id,
                    'updated' => date('Y-m-d H:i:s'),
                    'updated_by' => Yii::$app->params['superAdmin']
                ];

                $updatedTask = InvoiceDownloadTask::findOneByAttributes([
                    'id' => $task->id
                ]);

                $this->log("Invoice: {$task->invoice_id} - Task: {$task->id} - Header status is: {$updatedTask->status_header}");

                if (
                    $updatedTask !== null
                    && $updatedTask->status_header == InvoiceDownloadTask::STATUS_HEADER_FINISHED_WITH_SUCCESS
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
        $sql .= 'WHERE (status_body IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_BODY_NEW,
                InvoiceDownloadTask::STATUS_BODY_FINISHED_WITH_ERRORS
            ]) . ') ';
        $sql .= 'OR (status_body IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_BODY_IN_PROGRESS
            ]) . ')
            AND added > CURDATE() - INTERVAL 1 DAY)) ';
        $sql .= 'OR (status IN (' . implode(',', [
                InvoiceDownloadTask::STATUS_IN_PROGRESS
            ]) . ')
              AND added > CURDATE() - INTERVAL 1 DAY) ';
        $sql .= ' AND retries_body > 0 ';
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
        $sql .= ' SET status_body = ' . InvoiceDownloadTask::STATUS_BODY_IN_PROGRESS;
        $sql .= ', status = ' . InvoiceDownloadTask::STATUS_IN_PROGRESS;
        $sql .= ' WHERE id IN (' . implode(',', $ids) . ') ';
        $sql .= ';';
        InvoiceDownloadTask::execute($sql);

        return InvoiceDownloadTask::find()->where([
            'in', 'id', $ids
        ])->orderBy(['id' => SORT_DESC])->all();
    }

    public function prepareInvoiceBodyRows($data)
    {
        $invoiceBodyRows = [];
        foreach ($data as $rowInvoiceBodyRaw) {
            $arrRowBodyRaw = json_decode(json_encode($rowInvoiceBodyRaw), true);
            $fieldsInvoiceBodyRaw = $arrRowBodyRaw['fields'];

            if (
                empty($fieldsInvoiceBodyRaw) 
                || empty($fieldsInvoiceBodyRaw['Invoice_Id'])
                || empty($fieldsInvoiceBodyRaw['Task_Id'])
                || empty($fieldsInvoiceBodyRaw['OrderRecord'])
            ) {
                continue;
            }

            $invoiceBodyRows[] = $fieldsInvoiceBodyRaw;
        }

        return $invoiceBodyRows;
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
