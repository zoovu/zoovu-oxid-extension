<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;
use Semknox\Productsearch\Application\Model\SxHelper;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;

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

        $this->setConfig($configValues);
    }


    public function setConfig($configValues = [])
    {

        // really needed 
        $configValues['productTransformer'] = ArticleTransformer::class;
        $defaultValues['storagePath'] = $this->_oxConfig->getConfigParam('sShopDir') . $this->_sxHelper->get('sxFolder');

        $configValues = array_merge($defaultValues, $configValues);

        $this->_sxConfig = new SxConfig($configValues);
        $this->_sxCore = new SxCore($this->_sxConfig);
        $this->_sxUploader = $this->_sxCore->getInitialUploader();
        $this->_sxUpdater = $this->_sxCore->getProductUpdater();
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

            // check if groupId is set
            $transformerArgs = [
                'lang' => $sxLang,
            ];
            if ($userGroup = $this->_sxConfig->get('userGroup')) {
                $transformerArgs['userGroup'] = (string) $userGroup;
            }
            if ($this->_sxConfig->get('disableCategories', false)) {
                $transformerArgs['disableCategories'] = true;
            }


            foreach ($oxArticleList as $oxArticle) {
                $this->_sxUploader->addProduct($oxArticle, $transformerArgs);
            }

            // if ready, start uploading
            if (count($oxArticleList) < $pageSize) {
                $response = $this->_sxUploader->startUploading();

                if($response['status'] !== 'success'){
                    $logger = $this->_oxRegistry->getLogger();
                    $logger->error($response['satus'].': '.$response['message'], [__CLASS__, __FUNCTION__]);
                }
            }

        } else {
            // uploading

            // continue uploading...
            $response = $this->_sxUploader->sendUploadBatch();

            if($response === false){
                $logger = $this->_oxRegistry->getLogger();
                $logger->error('Failure in upload batch', [__CLASS__, __FUNCTION__]);
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
            $logger = $this->_oxRegistry->getLogger();
            $logger->error($response['message'], [__CLASS__, __FUNCTION__]);
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
            return $shopStatus->getPhase() == "UPLOADING" && $shopStatus->getCollectingProgress() >= 100;
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
            return $shopStatus->getPhase() == "UPLOADING" && $shopStatus->getCollectingProgress() >= 100 && $shopStatus->getUploadingProgress() >= 100;
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
        $oxShopList->getIdTitleList();

        $languages = $this->_getLanguages();
        $sxShopConfigs = array();

        foreach ($oxShopList->getArray() as $oxShop) {

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
                ];

                $currentShopConfig = $this->_sxHelper->getMasterConfig($currentShopConfig);

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

        if ($productsSent) {
            $logger = $this->_oxRegistry->getLogger();
            $logger->debug($productsSent . ' products sent to SEMKNOX.', [__CLASS__, __FUNCTION__]);
        }

        return $productsSent;
    }
}
