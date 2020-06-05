<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;
use Semknox\Productsearch\Application\Model\SxSetting;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;

class UploadController
{
    private $_sxCore, $_sxConfig, $_sxUploader;
    private $_oxRegistry, $_oxConfig, $_oxLang;


    /**
     * Class constructor. 
     */
    public function __construct($configValues)
    {
        $this->_oxRegistry = new Registry;
        $this->_oxConfig = $this->_oxRegistry->getConfig();
        $this->_oxLang = $this->_oxRegistry->getLang();

        $this->_sxSetting = new SxSetting;

        $configValues['apiUrl'] = (isset($configValues['sandbox']) && $configValues['sandbox']) ? $this->_sxSetting->get('SandboxApiUrl') : $this->_sxSetting->get('ApiUrl');
        $configValues['productTransformer'] = ArticleTransformer::class;
        $configValues['storagePath'] = $this->_oxRegistry->getConfig()->getConfigParam('sShopDir'). $this->_sxSetting->get('Folder');

        $configValues['initialUploadBatchSize'] = isset($configValues['uploadBatchSize']) ? $configValues['uploadBatchSize'] : $this->_sxSetting->get('UploadBatchSize');
        $configValues['collectBatchSize'] = isset($configValues['collectBatchSize']) ? $configValues['collectBatchSize'] : $this->_sxSetting->get('CollectBatchSize');
        $configValues['requestTimeout'] = isset($configValues['requestTimeout']) ? $configValues['requestTimeout'] : $this->_sxSetting->get('RequestTimeout');

        if($configValues['shopId']){
            $configValues['initialUploadIdentifier'] = $configValues['shopId'].'-'. $configValues['lang'];
        }

        $this->_sxConfig = new SxConfig($configValues);
        $this->_sxCore = new SxCore($this->_sxConfig);

        $this->_sxUploader = $this->_sxCore->getInitialUploader();
    
    }

    /**
     * start new product upload
     * 
     */
    public function startUpload()
    {       
        $oxArticleList = new ArticleList;
        $oxArticleQty = $oxArticleList->getAllArticlesCount();

        $this->_sxUploader->startCollecting([
            'expectedNumberOfProducts' => $oxArticleQty
        ]);
    }


    /**
     * continue running product upload
     * 
     */
    public function continueUpload()
    {

        if($this->_sxUploader->isCollecting()){
            // collecting

            $sxLang = $this->_sxConfig->get('lang');
            $sxLangId = $this->_sxConfig->get('langId');
            $sxCollectBatchSize = $this->_sxConfig->get('collectBatchSize');

            // set Store
            $this->_oxConfig->setShopId($this->_sxConfig->get('shopId'));
            $this->_oxConfig->reinitialize(); // empty cache

            // set Language
            $this->_oxLang->setBaseLanguage($sxLangId);
            //$this->_oxLang->resetBaseLanguage();

            $pageSize = $sxCollectBatchSize;
            $page = ((int) $this->_sxUploader->getNumberOfCollected() / $pageSize) + 1;

            $oxArticleList = new ArticleList;
            $oxArticleList->loadAllArticles($pageSize, $page);

            foreach ($oxArticleList as $oxArticle) {
                $this->_sxUploader->addProduct($oxArticle);
            }

            // if ready, start uploading
            if(count($oxArticleList) < $pageSize){
                $this->_sxUploader->startUploading();
            }

        } else {
            // uploading

            // continue uploading...
            if($this->_sxUploader->sendUploadBatch() <= 0){
                $this->_sxUploader->finalizeUpload();
            }

        }

    }


    /**
     * stop running product upload
     * 
     */
    public function stopUpload()
    {
        if($this->isRunning()){
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
                $sandbox = $this->_oxConfig->getConfigParam('sxIsSandbox' . $lang);

                $uploadActive = $this->_oxConfig->getConfigParam('sxUploadActive' . $lang);

                if (!$uploadActive || !$projectId || !$apiKey) continue;

                $sxShopConfigs[] = [
                    'projectId' => $projectId,
                    'apiKey' => $apiKey,
                    'sandbox' => $sandbox,

                    'lang' => $lang,
                    'langId' => $langId,
                    'shopId' => $shopId,
                    'cronjobHour' => (int) $this->_oxConfig->getConfigParam('sxCronjobHour'),
                    'cronjobMinute' => (int) $this->_oxConfig->getConfigParam('sxCronjobMinute'),

                    'collectBatchSize' => (int) $this->_oxConfig->getConfigParam('sxCollectBatchSize'),
                    'uploadBatchSize' => (int) $this->_oxConfig->getConfigParam('sxUploadBatchSize'),
                    'requestTimeout' => (int) $this->_oxConfig->getConfigParam('sxRequestTimeout')
                ];
            }
        }

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


}