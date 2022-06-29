<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;
use Semknox\Productsearch\Application\Model\SxHelper;
use Semknox\Productsearch\Application\Model\SxLogger;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;
use Semknox\Core\Exceptions\DuplicateInstantiationException;

class UploadController
{
    private $_sxCore, $_sxConfig, $_sxUploader, $_sxUpdater;
    private $_oxRegistry, $_oxConfig, $_oxLang;


    /**
     * Class constructor. 
     */
    public function __construct($configValues = [])
    {
        $this->_oxRegistry = new Registry;
        $this->_oxConfig = $this->_oxRegistry->getConfig();
        $this->_oxLang = $this->_oxRegistry->getLang();

        $this->_sxHelper = new SxHelper;
        $this->_sxLogger = new SxLogger;

        $this->setConfig($configValues);
    }


    public function setConfig($configValues = [], $noUploader = false)
    {

        // really needed 
        $configValues['loggingService'] = $this->_sxLogger;
        $configValues['productTransformer'] = ArticleTransformer::class;
        $defaultValues['storagePath'] = $this->_oxConfig->getConfigParam('sShopDir') . $this->_sxHelper->get('sxFolder');

        $configValues = array_merge($defaultValues, $configValues);

        $this->_sxConfig = new SxConfig($configValues);

        try {
            $this->_sxCore = new SxCore($this->_sxConfig);

            if (!$noUploader) {
                $this->_sxUploader = $this->_sxCore->getInitialUploader();
                $this->_sxUpdater = $this->_sxCore->getProductUpdater();
            }
        } catch (DuplicateInstantiationException $e) {

            $this->_sxHelper->log('Duplicate instantiation of uploader. Cronjob execution to close? | '.__CLASS__.'::'.__FUNCTION__, 'error');
            exit();
        }
    }

    /**
     * start new product upload
     * 
     */
    public function startFullUpload()
    {
        $shopId = $this->_sxConfig->get('shopId') ? $this->_sxConfig->get('shopId') : null;

        $oxArticleList = new ArticleList;
        $oxArticleQty = $oxArticleList->getAllArticlesCount($shopId);

        if($oxArticleQty){
            $this->_sxUploader->startCollecting([
                'expectedNumberOfProducts' => $oxArticleQty
            ]);
        }
    }


    /**
     * continue running product upload
     * 
     */
    public function continueFullUpload()
    {
        if ($this->_sxUploader->isCollecting()) {
            // collecting

            $sxLang = $this->_sxConfig->get('lang');
            $sxLangId = $this->_sxConfig->get('langId');
            $sxCollectBatchSize = $this->_sxConfig->get('collectBatchSize');

            // set Store
            $shopId = $this->_sxConfig->get('shopId');
            $this->_oxConfig->setShopId($shopId);
            $this->_oxConfig->reinitialize(); // empty cache

            // set Language
            $this->_oxLang->setBaseLanguage($sxLangId);
            //$this->_oxLang->resetBaseLanguage();

            $pageSize = $sxCollectBatchSize;
            $page = ((int) $this->_sxUploader->getNumberOfCollected() / $pageSize) + 1;

            $oxArticleList = new ArticleList;
            
            $oxArticleList->loadAllArticles($pageSize, $page, $shopId);
            //$oxArticleList->loadAllArticlesWithoutParentsThatHaveChildren($pageSize, $page, $shopId);

            // get default currency 
            $currencySymbol = '';
            foreach($this->_oxConfig->getConfigParam('aCurrencies') as $currencyEntry){

                $currency = explode('@', $currencyEntry);
                if((int) trim($currency[1]) == 1){
                    $currencySymbol = trim($currency[0]);
                    break;
                }
            }

            // check if groupId is set
            $transformerArgs = [
                'lang' => $sxLang,
                'currency' => $currencySymbol
            ];
            if ($userGroup = $this->_sxConfig->get('userGroup')) {
                $transformerArgs['userGroup'] = (string) $userGroup;
            }
            if ($this->_sxConfig->get('disableCategories', false)) {
                $transformerArgs['disableCategories'] = true;
            }

            // imageUrlSuffix
            if ($imageUrlSuffix = $this->_sxConfig->get('imageUrlSuffix')) {
                $transformerArgs['imageUrlSuffix'] = $imageUrlSuffix;
            }


            $transformerArgs['languages'] = $this->_getLanguages();
            foreach ($oxArticleList as $oxArticle) {
                $this->_sxUploader->addProduct($oxArticle, $transformerArgs);
            }

            // if ready, start uploading
            if (count($oxArticleList) < $pageSize) {
                $response = $this->_sxUploader->startUploading();

                if($response['status'] !== 'success'){
                    $this->_sxHelper->log($response['satus'] . ': ' . $response['message'] .' | '.__CLASS__.'::'.__FUNCTION__, 'error');
                }
            }

        } else {
            // uploading

            // continue uploading...
            $response = $this->_sxUploader->sendUploadBatch(true);

            if(is_array($response) && $response['status'] !== 'success'){

                $message = $response['message'];
                if(isset($response['validation'][0]['schemaErrors'][0])){
                    $message .=' ('. $response['validation'][0]['schemaErrors'][0].')';
                }

                $this->_sxHelper->log($response['status'] . ':' . $message . ' | ' . __CLASS__ . '::' . __FUNCTION__, 'error');
            }
        }
    }


    /**
     * finalize running product upload
     * 
     */
    public function finalizeFullUpload($signalApi = true)
    {
        $response = $this->_sxUploader->finalizeUpload($signalApi);

        if ($response['status'] !== 'success') {
            $this->_sxHelper->log($response['message']. ' | ' . __CLASS__ . '::' . __FUNCTION__, 'error');
        }
    }


    /**
     * stop running product upload
     * 
     */
    public function stopFullUpload()
    {
        if ($this->isRunning()) {
            $this->_sxUploader->abort();
        }
    }


    /**
     * is currently an upload running for this config
     * 
     */
    public function isRunning()
    {
        return $this->_sxUploader->isRunning();
    }


    /**
     * collecting finished, ready to upload
     * 
     */
    public function isReadyToUpload()
    {
        if ($shopStatus = $this->getCurrentShopStatus()) {
            return $shopStatus->getPhase() == "UPLOADING" /*&& $shopStatus->getCollectingProgress() >= 100*/;
        }

        return false;
    }


    /**
     * uploading finished, ready to finalize
     * 
     */
    public function isReadyToFinalize()
    {
        if ($shopStatus = $this->getCurrentShopStatus()) {
            return $shopStatus->getPhase() == "UPLOADING" /*&& $shopStatus->getCollectingProgress() >= 100*/ && $shopStatus->getUploadingProgress() >= 100;
        }

        return false;
    }


    /**
     * get status of alle uploads
     * 
     */
    public function getCurrentShopStatus()
    {
        $shopId = $this->_sxConfig->get('shopId');
        $lang = $this->_sxConfig->get('lang');

        $status = $this->getStatus();

        if ($shopId && $lang && isset($status[$shopId . '-' . $lang])) {
            return $status[$shopId . '-' . $lang];
        }

        return false;
    }


    /**
     * get status of alle uploads
     * 
     */
    public function getStatus()
    {
        $uploadOverview = $this->_sxCore->getInitialUploadOverview();
        return $uploadOverview->getRunningUploads();
    }



    /**
     * get the configs of all shops
     * 
     * @return array
     */
    public function getShopConfigs()
    {
        $currentLanguage = $this->_oxLang->getBaseLanguage();

        $oxShopList = new ShopList;
        $oxShopList->getAll();

        $languages = $this->_getLanguages();
        $sxShopConfigs = array();

        foreach ($oxShopList->getArray() as $oxShop) {

            if((int) $oxShop->oxshops__oxactive < 1) continue; // ignore inactive shops

            $shopId = (string) $oxShop->oxshops__oxid;

            $this->_oxConfig->setShopId($shopId);
            $this->_oxConfig->reinitialize(); // empty cache

            foreach ($languages as $langId => $lang) {

                $this->_oxLang->setBaseLanguage($langId);
                $this->_oxLang->resetBaseLanguage();

                $projectId = $this->_oxConfig->getConfigParam('sxProjectId' . $lang);
                $apiKey = $this->_oxConfig->getConfigParam('sxApiKey' . $lang);
                $sandbox = $this->_sxHelper->get('sxIsSandbox' . $lang);

                $uploadActive = $this->_oxConfig->getConfigParam('sxUploadActive' . $lang);

                if (!$uploadActive) continue;

                $currentShopConfig = [
                    'projectId' => $projectId,
                    'apiKey' => $apiKey,
                    'sandbox' => $sandbox,

                    'apiUrl' => $sandbox ? $this->_sxHelper->get('sxSandboxApiUrl') : $this->_sxHelper->get('sxApiUrl'),

                    'lang' => $lang,
                    'langId' => $langId,
                    'shopId' => $shopId,
                    'cronjobHour' => (int) $this->_sxHelper->get('sxCronjobHour'),
                    'cronjobMinute' => (int) $this->_sxHelper->get('sxCronjobMinute'),

                    'collectBatchSize' => (int) $this->_sxHelper->get('sxCollectBatchSize'),
                    'uploadBatchSize' => (int) $this->_sxHelper->get('sxUploadBatchSize'),
                    'requestTimeout' => (int) $this->_sxHelper->get('sxRequestTimeout'),

                    'storeIdentifier' => $shopId . '-' . $lang,

                    // shopsystem settings
                    'sxFrontendActive' => $this->_sxHelper->get('sxFrontendActive', true),
                    'sxUploadActive' => $this->_sxHelper->get('sxUploadActive', true),
                    'sxIncrementalUpdatesActive' => $this->_sxHelper->get('sxIncrementalUpdatesActive', true),
                    'sxAnswerActive' => $this->_sxHelper->get('sxAnswerActive', true),


                    'shopsystem' => 'oxid',
                    //'shopsystemversion' => '2',
                    //'extensionversion' => '3.2',
                    
                    
                ];

                $currentShopConfig = $this->_sxHelper->getMasterConfig($currentShopConfig, $lang);

                // since its possible to set login data by masterConfig, this check has to be the last one
                if ($currentShopConfig['projectId'] && $currentShopConfig['apiKey']) {
                    $sxShopConfigs[$shopId . '_' . $lang] = $currentShopConfig;
                }
            }
        }

        // reset to current language
        $this->_oxLang->setBaseLanguage($currentLanguage);
        $this->_oxLang->resetBaseLanguage();

        return $sxShopConfigs;
    }


    /**
     * get languages
     * 
     * @return array
     */
    protected function _getLanguages()
    {
        $languages = array();

        foreach ($this->_oxLang->getLanguageArray() as $lang) {

            if ((int) $lang->active < 1) continue; // ignore inactive languages
            
            $languages[$lang->id] = ucfirst($lang->oxid);
        }

        return $languages;
    }



    /**
     * add articles to update (single)
     * 
     */
    public function addArticleUpdates($oxArticleIds)
    {
        // check if groupId is set
        $transformerArgs = [];
        if ($userGroup = $this->_sxConfig->get('userGroup')) {
            $transformerArgs['userGroup'] = (string) $userGroup;
        }

        // set Store
        $this->_oxConfig->setShopId($this->_sxConfig->get('shopId'));
        $this->_oxConfig->reinitialize(); // empty cache

        // set Language
        $this->_oxLang->setBaseLanguage($this->_sxConfig->get('langId'));

        $oxArticleList = new ArticleList;
        $oxArticleList->loadIds($oxArticleIds);

        $transformerArgs['languages'] = $this->_getLanguages();
        foreach ($oxArticleList as $oxArticle) {
            $this->_sxUpdater->addProduct($oxArticle, $transformerArgs);
        }
    }


    /**
     * send article updates
     * 
     */
    public function sendUpdate()
    {
        $productsSent = $this->_sxUpdater->sendUploadBatch();

        if ($productsSent !== FALSE && $productsSent > 0) {
            $this->_sxHelper->log($productsSent . ' products sent to SEMKNOX.'. ' | ' . __CLASS__ . '::' . __FUNCTION__, 'info');
        }

        return $productsSent;
    }
}
