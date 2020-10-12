<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;

use Semknox\Productsearch\Application\Controller\UploadController;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;
use Semknox\Productsearch\Application\Model\SxQueue;

class CronController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'sxproductsearch_cron.tpl';

    private $_oxRegistry, $_oxConfig, $_oxLang;
    private $_currentMinute, $_currentHour;


    /**
     * Class constructor. 
     */
    public function __construct()
    {

        $this->_oxRegistry = new Registry;
        $this->_oxConfig = $this->_oxRegistry->getConfig();
        $this->_oxLang = $this->_oxRegistry->getLang();

        $oxUtilsDate = $this->_oxRegistry->getUtilsDate();
        $time = $oxUtilsDate->getTime();
        $this->_currentMinute = (int) date('i', $time);
        $this->_currentHour = (int) date('G', $time);

        $this->_cronRunner();

    }


    /**
     * cronjob runner (checks what to do start/continue)
     */
    protected function _cronRunner()
    {
        $startTime = time(); // for logging duration
        $flags = array();

        $sxUpload = new UploadController([]);

        $sxShopUloads = array();
        foreach($sxUpload->getShopConfigs() as $key => $shopConfig){

            $sxShopUloads[$key] = new UploadController($shopConfig);
            $sxShopUloads[$key]->config = $shopConfig;
            $sxShopUloads[$key]->start_upload = ($this->_currentHour == $shopConfig['cronjobHour'] && $this->_currentMinute == $shopConfig['cronjobMinute']);
        }


        // [-1-] Check if upload has to be started
        foreach($sxShopUloads as $key => $shopUploader){

            if($shopUploader->start_upload && !$shopUploader->isRunning()){
                $shopUploader->startFullUpload();
                unset($sxShopUloads[$key]);
                $flags['running'] = true;
            } elseif(!$shopUploader->isRunning()){
                unset($sxShopUloads[$key]);
            }
        }


        // [-2-] check queue actions
        if(isset($flags['running'])){
            $sxQueue = new SxQueue();

            // empty update queue
            $sxQueue->set('update');
            $sxQueue->empty();

            // empty delete queue
            $sxQueue->set('delete');
            $sxQueue->empty();
        }


        // [-3-] collecting for all shops
        // >>> check if !!!COLLECTING!!! needs to be continued (always just one job per cronrun!)
        foreach ($sxShopUloads as $key => $shopUploader) {

            if ($shopUploader->isReadyToUpload()) continue;   

            // !!!COLLECTING!!!
            $shopUploader->continueFullUpload();
            $flags['collecting'] = true;
            break; // (always just one job per cronrun!)
        }


        // [-4-] uploading for all shops
        // >>>> check if !!!UPLOADING!!! needs to be continued (always just one job per cronrun!)
        if (!isset($flags['collecting'])) {

            foreach ($sxShopUloads as $key => $shopUploader) {

                if (!$shopUploader->isReadyToUpload() || $shopUploader->isReadyToFinalize()) continue;

                // !!!UPLOADING!!!
                $shopUploader->continueFullUpload();
                $flags['uploading'] = true;
                break; // (always just one job per cronrun!)
            
            }
        }


        // [-5-] finalizing for all shops !!!AT ONCE!!!
        // >>> check if !!!FINALIZE UPLOADING!!! needs to be continued (always just one job per cronrun!)
        if (!isset($flags['collecting']) && !isset($flags['uploading'])) {

            $signalSent = true;

            foreach ($sxShopUloads as $key => $shopUploader) {

                if (!$shopUploader->isReadyToUpload() || !$shopUploader->isReadyToFinalize()) continue;

                // !!!FINALIZE UPLOADING!!!
                $shopUploader->finalizeFullUpload($signalSent);

                if (isset($shopUploader->config['userGroup'])) {
                    $signalSent = false;
                };
        
            }
        }



        $this->sxResponse = ['status' => 'success', 'duration' => time() - $startTime];


    }


    /**
     * cronjob runner (checks what to do start/continue)
     */
    protected function _cronRunnerOLD()
    {
        $startTime = time();
        $sxQueue = new SxQueue();
        $sxUpload = new UploadController([]);
        $sxShopConfigs = $sxUpload->getShopConfigs();

        $initialUploadStarted = false;
        $initialUploadRunning = false;
        $initialUploadCollectingRunning = false;
        $initialUploadUploadingRunning = false;

        // check if Uploads needs to be startet
        foreach ($sxShopConfigs as $key => $shopConfig) {

            if ($this->_currentHour == $shopConfig['cronjobHour'] && $this->_currentMinute == $shopConfig['cronjobMinute']) {
                $sxUpload = new UploadController($shopConfig);

                if($sxUpload->isRunning()) continue;

                $sxUpload->startFullUpload();
                unset($sxShopConfigs[$key]); // not directly continue;
                $initialUploadStarted = true;
            }
        }

        if($initialUploadStarted){

            // empty update queue
            $sxQueue->set('update');
            $sxQueue->empty();

            // empty delete queue
            $sxQueue->set('delete');
            $sxQueue->empty();
        } 

        // [-1-] collecting for all shops
        // >>> check if !!!COLLECTING!!! needs to be continued (always just one job per cronrun!)
        foreach ($sxShopConfigs as $shopConfig) {

            $sxUpload = new UploadController($shopConfig);

            if($sxUpload->isRunning() && !$sxUpload->isReadyToUpload()){ // !!!COLLECTING!!!
                $sxUpload->continueFullUpload();
                $initialUploadRunning = true;
                $initialUploadCollectingRunning = true;
                break; // (always just one job per cronrun!)
            }

        }

        // [-2-] uploading for all shops
        // >>>> check if !!!UPLOADING!!! needs to be continued (always just one job per cronrun!)
        if(!$initialUploadCollectingRunning){
            foreach ($sxShopConfigs as $shopConfig) {

                $sxUpload = new UploadController($shopConfig);

                if ($sxUpload->isRunning() && $sxUpload->isReadyToUpload() && !$sxUpload->isReadyToFinalize()) { // !!!UPLOADING!!!
                    $sxUpload->continueFullUpload();
                    $initialUploadRunning = true;
                    $initialUploadUploadingRunning = true;
                    break; // (always just one job per cronrun!)
                }
            }
        }


        // [-3-] finalizing for all shops !!!AT ONCE!!!
        // >>> check if !!!FINALIZE UPLOADING!!! needs to be continued (always just one job per cronrun!)
        if (!$initialUploadCollectingRunning && !$initialUploadUploadingRunning) {

            $signalSent = true;

            foreach ($sxShopConfigs as $shopConfig) {

                $sxUpload = new UploadController($shopConfig);

                if ($sxUpload->isRunning() && $sxUpload->isReadyToUpload() && $sxUpload->isReadyToFinalize()) { // !!!FINALIZE UPLOADING!!!
                    $sxUpload->finalizeFullUpload($signalSent);
                    $initialUploadRunning = true;

                    if(isset($shopConfig['userGroup'])){
                        $signalSent = false;
                    };
                }
            }
        }

        // do incremental updates
        if(!$initialUploadRunning){

            if( $oxArticleIds = $sxQueue->getArticles(10)){
                
                foreach ($sxShopConfigs as $shopConfig) {

                    if(!$shopConfig['sxIncrementalUpdatesActive']) continue;

                    $sxUpload = new UploadController($shopConfig);
                    $sxUpload->addArticleUpdates($oxArticleIds);
                }

                $sxQueue->removeArticle($oxArticleIds);
            }

            foreach ($sxShopConfigs as $shopConfig) {

                if(!$shopConfig['sxIncrementalUpdatesActive']) continue;

                $sxUpload = new UploadController($shopConfig);
                $sxUpload->sendUpdate();
            }          

        }

        $this->sxResponse = ['status' => 'success','duration' => time()-$startTime];

    }

}
