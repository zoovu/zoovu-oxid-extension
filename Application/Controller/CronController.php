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
                    $sxUpload = new UploadController($shopConfig);
                    $sxUpload->addArticleUpdates($oxArticleIds);
                }

                $sxQueue->removeArticle($oxArticleIds);
            }

            foreach ($sxShopConfigs as $shopConfig) {
                $sxUpload = new UploadController($shopConfig);
                $sxUpload->sendUpdate();
            }          

        }

        $this->sxResponse = ['status' => 'success','duration' => time()-$startTime];

    }

}
