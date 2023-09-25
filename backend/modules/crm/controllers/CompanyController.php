<?php

namespace backend\modules\crm\controllers;

use backend\modules\finance\models\InvoiceLayout;
use common\components\AppHelper;
use backend\modules\adm\models\Domain;
use backend\modules\adm\models\Entity;
use backend\modules\adm\models\Subdomain;
use backend\modules\adm\models\User;
use backend\modules\location\models\City;
use backend\modules\location\models\Country;
use backend\modules\crm\models\EntityDomain;
use backend\modules\crm\models\IbanCompany;
use backend\modules\location\models\State;
use Yii;
use backend\modules\crm\models\Company;
use backend\modules\crm\models\search\CompanySearch;
use yii\db\Exception;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CompanyController implements the CRUD actions for Company model.
 */
class CompanyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'generate-code' => ['POST']
                ],
            ],
            [
                'class' => 'yii\filters\AjaxFilter',
                'only' => [
                    'generate-code'
                ]
            ]
        ];
    }

    /**
     * Lists all Company models.
     * @return mixed
     */
    public function actionIndex()
    {
        User::setUsers(true);
        Url::remember('', 'company');
        Company::setFilterOptions('added_by');
        Company::setFilterOptions('updated_by');

        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Company model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Imports an excel file with companies
     * @added_by Anca P.
     * @return string
     * @since 25.07.2022
     */
    public function actionImport()
    {
        $model = new Company();
        return $this->render('import', ['model' => $model]);
    }

    /**
     * Creates a new Company model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @updated_by Andrei I
     * @since 13/05/2022
     * Creates a new IBAN model.
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        $companyModel = new Company();
        $companyModel->added = date('Y-m-d H:i:s');
        $companyModel->added_by = Yii::$app->user->id;

        $entityDomainModel = new EntityDomain();
        $entityDomainModel->added = date('Y-m-d H:i:s');
        $entityDomainModel->added_by = Yii::$app->user->id;

        $ibanCompanyModel = new IbanCompany();
        $ibanCompanyModel->list[] = '';

        $tva = null;

        AppHelper::setNames('country', get_class(new Country()), 'name');
        AppHelper::setNames('state', get_class(new State()), 'name');
        AppHelper::setNames('city', get_class(new City()), 'name');
        Domain::setNames();
        Company::setStatusTVA();

        $post = Yii::$app->request->post();

        $entityDomainModel->load($post);

        if (!empty($post['IbanCompanies'])) {
            $ibanCompanyModel->list = $post['IbanCompanies'];
        }

        if (!isset($post['Company']['tva']) == 0) {
            $tva = 'RO';
        }

        $transaction = Yii::$app->ecf_crm_db->beginTransaction();
        try {
            if ($companyModel->load($post)) {
                $companyModel->tva = $post['Company']['tva'];
                $companyModel->state_id = strtoupper($post['Company']['state_id']);
                $companyModel->cui = trim(str_replace(" ", "", strtoupper($post['Company']['cui'])));

                try {
                    Company::validateCIF($companyModel->cui);
                } catch (Exception $exc) {
                    throw new Exception(Yii::t('crm', $exc->getMessage()));
                }

                if (!$companyModel->save()) {
                    if ($companyModel->hasErrors()) {
                        foreach ($companyModel->errors as $error) {
                            throw new Exception(Yii::t('crm', $error[0]));
                        }
                    }
                    throw new Exception(Yii::t('crm', 'Failed to save'));
                }

                $entityDomainModel->item_id = $companyModel->id;
                if ($entityDomainModel->load($post)) {
                    if (!$entityDomainModel->save()) {
                        if ($entityDomainModel->hasErrors()) {
                            foreach ($entityDomainModel->errors as $error) {
                                throw new Exception(Yii::t('crm', $error[0]));
                            }
                        }

                        throw new Exception(Yii::t('crm', 'Failed to save'));
                    }
                }

                $errorMsg = '';

                if (!empty($post['IbanCompanies'])) {
                    foreach ($ibanCompanyModel->list as $iban) {
                        try {
                            IbanCompany::validateIban($iban);
                        } catch (Exception $exc) {
                            throw new Exception(Yii::t('crm', $exc->getMessage()));
                        }
                        if ($iban !== '') {
                            $createIbanRemoveWhiteSpace = trim(str_replace(" ", "", strtoupper($iban)));
                            $createIban = chunk_split($createIbanRemoveWhiteSpace, 4, ' ');

                            $ibanCompanyModel = new IbanCompany();
                            $ibanCompanyModel->company_id = $companyModel->id;;
                            $ibanCompanyModel->iban = trim($createIban);
                            $ibanCompanyModel->added = date('Y-m-d H:i:s');
                            $ibanCompanyModel->added_by = Yii::$app->user->id;

                            if (!$ibanCompanyModel->save()) {
                                $errorMsg .= $iban . Yii::t('crm', ' -> The error: ');
                                if ($ibanCompanyModel->hasErrors()) {
                                    foreach ($ibanCompanyModel->errors as $error) {
                                        $errorMsg .= Yii::t('crm', $error[0]);
                                        break;
                                    }
                                } else {
                                    $errorMsg .= Yii::t('crm', 'Unknown error');
                                }
                                $errorMsg .= '<br>';
                            }
                        }
                        $ibanCompanyModel->list = $post['IbanCompanies'];
                    }
                }

                $invoiceLayout = InvoiceLayout::getByAttributes([
                    'company_id' => $companyModel->id,
                ], [
                    'company_id' => $companyModel->id,
                    'name' => 'Layout' . implode('', explode(' ', ucwords($companyModel->name))),
                    'layout_type_id' => InvoiceLayout::TYPE_INVOICE
                ]);
                if ($invoiceLayout === null) {
                    throw new \Exception(Yii::t('crm', 'Could not find the vendor layout for vendor {vendorID}', [
                        'vendorID' => $companyModel->id
                    ]));
                }

                $transaction->commit();

                if (!Yii::$app->request->isAjax) {
                    Yii::$app->session->setFlash('success', Yii::t('crm', "Successfully created!"));
                    return $this->redirect(Url::to(['index']));
                }
                return '<option value="' . $companyModel->id . '">' . $companyModel->name . '</option>';
            }
        } catch (\Exception|\Throwable $e) {
            $transaction->rollBack();

            Yii::$app->session->setFlash('danger', Yii::t('crm', $e->getMessage()));
            return $this->render('create', [
                'companyModel' => $companyModel,
                'entityDomainModel' => $entityDomainModel,
                'ibanCompanyModel' => $ibanCompanyModel,
                'tva' => $tva,
                'isNewRecord' => true
            ]);
        }

        return $this->render('create', [
            'companyModel' => $companyModel,
            'entityDomainModel' => $entityDomainModel,
            'ibanCompanyModel' => $ibanCompanyModel,
            'tva' => $tva,
            'isNewRecord' => true
        ]);
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     *
     * @updated_by Andrei I
     * @since 16/05/2022
     * Add IBAN for company, or update an existing data
     */
    public function actionUpdate($id)
    {
        Domain::setNames();
        Entity::setNames();
        Subdomain::setNames();
        Company::setStatusTVA();
        AppHelper::setNames('country', get_class(new Country()), 'name');
        AppHelper::setNames('state', get_class(new State()), 'name');
        AppHelper::setNames('city', get_class(new City()), 'name');


        $companyModel = $this->findModel($id);
        $companyModel->updated = date('Y-m-d H:i:s');
        $companyModel->updated_by = Yii::$app->user->id;

        $tva = null;

        try {
            $entityDomainModel = EntityDomain::findEntityDomainByItem($companyModel->id, true);
        } catch (BadRequestHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(['index']);
        }

        $ibanCompanyModel = IbanCompany::find()->where('company_id = :company_id', [':company_id' => $id])->all();
        foreach ($ibanCompanyModel as $iban) {
            $existingIban[] = $iban->iban;
        }

        $ibanCompanyModel = new IbanCompany();

        $post = Yii::$app->request->post();
        $entityDomainModel->load($post);

        if (empty($post['IbanCompanies'])) {
            if (!empty($existingIban)) {
                $ibanCompanyModel->list = $existingIban;
            }
        } else {
            $ibanCompanyModel->list = $post['IbanCompanies'];
        }
        $ibanCompanyModel->list[] = '';

        $transaction = Yii::$app->ecf_crm_db->beginTransaction();
        try {
            if ($companyModel->load($post)) {
                $companyModel->tva = $post['Company']['tva'];
                $companyModel->state_id = $post['Company']['state_id'];
                $companyModel->cui = trim(str_replace(" ", "", strtoupper($post['Company']['cui'])));

                try {
                    Company::validateCIF($companyModel->cui);
                } catch (Exception $exc) {
                    throw new Exception(Yii::t('crm', $exc->getMessage()));
                }
                if (!$companyModel->save()) {
                    if ($companyModel->hasErrors()) {
                        foreach ($companyModel->errors as $error) {
                            throw new Exception(Yii::t('crm', $error[0]));
                        }
                    }

                    throw new Exception(Yii::t('crm', 'Failed to save'));
                }

                $entityDomainModel->item_id = $companyModel->id;
                if ($entityDomainModel->load($post)) {
                    if (!$entityDomainModel->save()) {
                        if ($entityDomainModel->hasErrors()) {
                            foreach ($entityDomainModel->errors as $error) {
                                throw new Exception(Yii::t('crm', $error[0]));
                            }
                        }

                        throw new Exception(Yii::t('crm', 'Failed to save'));
                    }
                }

                if (!empty($post['IbanCompanies'])) {
                    IbanCompany::deleteAll(['company_id' => $companyModel->id]);
                }
                $errorMsg = '';
                foreach ($ibanCompanyModel->list as $iban) {
                    try {
                        IbanCompany::validateIban($iban);
                    } catch (Exception $exc) {
                        throw new Exception(Yii::t('crm', $exc->getMessage()));
                    }

                    if ($iban !== '') {
                        $createIbanRemoveWhiteSpace = trim(str_replace(" ", "", strtoupper($iban)));
                        $createIban = chunk_split($createIbanRemoveWhiteSpace, 4, ' ');

                        $ibanCompanyModel = new IbanCompany();
                        $ibanCompanyModel->company_id = $companyModel->id;
                        $ibanCompanyModel->iban = trim($createIban);
                        $ibanCompanyModel->added = date('Y-m-d H:i:s');
                        $ibanCompanyModel->added_by = Yii::$app->user->id;

                        if (!$ibanCompanyModel->save()) {
                            $errorMsg .= $iban . Yii::t('crm', ' -> The error: ');
                            if ($ibanCompanyModel->hasErrors()) {
                                foreach ($ibanCompanyModel->errors as $error) {
                                    $errorMsg .= Yii::t('crm', $error[0]);
                                    break;
                                }
                            } else {
                                $errorMsg .= Yii::t('crm', 'Unknown error');
                            }
                            $errorMsg .= '<br>';
                        }
                    }
                    $ibanCompanyModel->list = $post['IbanCompanies'];
                }

                $invoiceLayout = InvoiceLayout::getByAttributes([
                    'company_id' => $companyModel->id,
                ], [
                    'company_id' => $companyModel->id,
                    'name' => 'Layout' . implode('', explode(' ', ucwords($companyModel->name))),
                    'layout_type_id' => InvoiceLayout::TYPE_INVOICE
                ]);
                if ($invoiceLayout === null) {
                    throw new \Exception(Yii::t('crm', 'Could not find the vendor layout for vendor {vendorID}', [
                        'vendorID' => $companyModel->id
                    ]));
                }

                $transaction->commit();

                Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully updated!'));
                return $this->redirect(Url::to(['index']));
            }
        } catch (\Exception|\Throwable $e) {
            $transaction->rollBack();

            Yii::$app->session->setFlash('danger', Yii::t('crm', $e->getMessage()));
            return $this->render('update', [
                'companyModel' => $companyModel,
                'entityDomainModel' => $entityDomainModel,
                'ibanCompanyModel' => $ibanCompanyModel,
                'tva' => $tva,
                'isNewRecord' => false
            ]);
        }

        return $this->render('update', [
            'companyModel' => $companyModel,
            'entityDomainModel' => $entityDomainModel,
            'ibanCompanyModel' => $ibanCompanyModel,
            'tva' => $tva,
            'isNewRecord' => false
        ]);
    }

    /**
     * Deletes an existing Company model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);

            if ($model->tva === null) {
                $model->tva = 2;
            }

            AppHelper::chainDelete($model);

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully deleted!'));
            return $this->redirect(Url::previous('company'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('company'));
        }
    }

    /**
     * Activates a deleted Company model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        try {
            $model = $this->findModel($id);
            AppHelper::chainActivate($model);

            Yii::$app->session->setFlash('success', Yii::t('crm', 'Successfully activated!'));
            return $this->redirect(Url::previous('company'));
        } catch (NotFoundHttpException $exc) {
            Yii::$app->session->setFlash('danger', $exc->getMessage());
            return $this->redirect(Url::previous('company'));
        }
    }

    /**
     * Import companies from Sharepoint
     * @throws GuzzleException
     */
    public function actionImportCompanies()
    {

        $component = new SharePointConnect(1, 2, ['siteId' => 4]);
        VarDumper::dump($component->getLabel(), 10, 1);
        die('done');
        try {
            $guzzle = new Client();
            $url = 'https://login.microsoftonline.com/' . TENANT_ID . '/oauth2/token';
            $user_token = json_decode($guzzle->post($url, [
                'form_params' => [
                    'client_id' => CLIENT_ID,
                    'client_secret' => CLIENT_SECRET,
                    'resource' => 'https://graph.microsoft.com/',
                    'grant_type' => 'password',
                    'username' => SHARE_POINT_USERNAME,
                    'password' => SHARE_POINT_PASS,
                ],
            ])->getBody()->getContents());
            $user_accessToken = $user_token->access_token;

            $graph = new Graph();
            $graph->setAccessToken($user_accessToken);
        } catch (GuzzleException $exc) {
            Yii::$app->session->setFlash('danger', Yii::t('crm', $exc->getMessage()));
//            return $this->redirect(['/finance/invoice/index']);
        }

        $reqEndPointInvoice = '' . DIRECTORY_SEPARATOR . '' . LEVIATAN_DOMAIN . '' . DIRECTORY_SEPARATOR . '' . LEVIATAN_SITE . '' . DIRECTORY_SEPARATOR . '' . INVOICE_LIST_ID . '' . DIRECTORY_SEPARATOR . '' . INVOICE_FIELDS . '&filter=fields/ID gt ' . 10 . ' and fields/ID le ' . 11 . '';
//        $_req = "/sites/leviatandesign.sharepoint.com:/sites/CRM:/lists/0396c1af-638b-4cbf-8523-3b23cc45c761/items";
//        $_req = "/sites/leviatandesign.sharepoint.com:/sites/CRM:/lists/Company/items?expand=fields";
//        $_req = "/sites/leviatandesign.sharepoint.com:/sites/CRM:/lists/Company/columns";
        $_req = "/sites/leviatandesign.sharepoint.com:/sites/CRM:/lists/Company/items?expand=fields(select=id,Name,CUI,Reg_Number,Country,City,Address,Added,Added_By,Updated,Updated_By,CompanyType_Id,Code)";
//        die($_req);

        try {
            $request = $graph->createRequest("GET", $_req)
                ->addHeaders(array(
                    "Accept" => "application/json;odata.metadata=none",
                    "Prefer" => "HonorNonIndexedQueriesWarningMayFailRandomly"
                ))
                ->setReturnType(ListItem::class)
                ->execute();
            //$_test = new ListItem($request->getFields());
            $returnType = new ListItem();
//            $test1 = $request->getDeltaLink();
//            $test2 = $request->getBody();
//            VarDumper::dump($test1,10,1);
//            VarDumper::dump($test2,10,1);
//            $test = $request->getResponseAsObject($returnType);
            foreach ($request as $key => $item) {
//                VarDumper::dump($key, 10, 1);
                $ceva = $item->getProperties()['fields'];
                VarDumper::dump($ceva, 10, 1);
//                foreach ($ceva as $value)
                //VarDumper::dump($value, 10, 1);
            }
        } catch (RequestException $exc) {
            if ($exc->hasResponse()) {
//                Yii::$app->session->setFlash('danger', Yii::t('crm', json_decode($exc->getResponse()->getBody())->error));
//                return $this->redirect(['/finance/invoice/index']);
                VarDumper::dump(json_decode($exc->getResponse()->getBody())->error->message, 10, 1);
                die('fail 1');
            }
        } catch (GraphException $exc) {
            Yii::$app->session->setFlash('danger', Yii::t('crm', $exc->getMessage()));
//            return $this->redirect(['/finance/invoice/index']);
            die('fail 2');
        }
//        foreach ($request as $item){
//            VarDumper::dump($request->getResponseAsObject($returnType), 10, 1);
//        }
//        VarDumper::dump($request, 10, 1);
        die('lll');
        return $this->redirect(['index']);
    }

    /**
     * Generate code for company using name inserted by user
     *
     * @author Andrei I.
     * @since 11/05/2022
     */
    public function actionGenerateCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();
        if (empty($post)) {
            throw new Exception(Yii::t('crm', 'Field cannot be empty'));
        }

        return $post['name'];
    }

    /**
     * Add RO if company has TVA
     *
     * @author Andrei I.
     * @since 14/06/2022
     */
    public function actionAddRo($tva)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($tva == 0) {
            $tva = 'RO';
        } else {
            $tva = null;
        }

        return $tva;
    }

    /**
     * Add RO if company has TVA
     *
     * @author Andrei I.
     * @since 15/06/2022
     */
    public function actionAddRoCui($tva, $company_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $modelCui = Company::find()->select('cui')->where('id = :id', [':id' => $company_id])->one();

        if (is_numeric($modelCui->cui)) {
            $cuiTVA = $modelCui->cui;
        } else {
            $cuiTVA = substr($modelCui->cui, 2);
        }

        if ($tva == 0) {
            $tva = 'RO' . $cuiTVA;
        } else {
            $tva = $cuiTVA;
        }

        return $tva;
    }

    /**
     * Updated the deleted column for all companies selected
     *
     * @author Andrei I.
     * @since 25/07/2022
     */
    public function actionDeleteActivate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        if (empty($post) || empty($post['selectedCompaniesIDs'])) {
            throw new Exception(Yii::t('crm', 'The data is not found.'));
        }
        $selectedCompanies = implode(',', $post['selectedCompaniesIDs']);
        if ($post['status'] == 1) {
            Company::updateAll([
                'deleted' => 1,
                'updated' => date('Y-m-d H:i:s'),
                'updated_by' => Yii::$app->user->id
            ], "id IN ({$selectedCompanies})");
            $message = 'Successfully deleted!';
        } else {
            Company::updateAll([
                'deleted' => 0,
                'updated' => date('Y-m-d H:i:s'),
                'updated_by' => Yii::$app->user->id
            ], "id IN ({$selectedCompanies})");
            $message = 'Successfully activated!';
        }

        Yii::$app->session->setFlash('success', Yii::t('crm', "{$message}"));
        return $this->redirect(Url::previous('company'));
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('crm', 'The requested page does not exist.'));
    }
}
