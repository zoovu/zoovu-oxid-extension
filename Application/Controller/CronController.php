<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;

use Semknox\Productsearch\Application\Controller\UploadController;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;
use Semknox\Productsearch\Application\Model\SxQueue;
use OxidEsales\Eshop\Core\ShopVersion;

class CronController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'sxproductsearch_cron';

    private $_oxRegistry, $_oxShopVersion;
    private $_currentMinute, $_currentHour;


    /**
     * Class constructor. 
     */
    public function __construct()
    {

        $this->_oxRegistry = new Registry;
        $this->_oxShopVersion = new ShopVersion();

        if (version_compare($this->_oxShopVersion->getVersion(), "7.0.0") < 0) {
            $this->setTemplateName($this->_sThisTemplate . '.tpl');
        } else {
            $this->setTemplateName('@sxproductsearch/'.$this->_sThisTemplate);
        }


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
        $startTime = \microtime(true); // for logging duration
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
            } elseif ($shopUploader->isRunning()) {
                $flags['running'] = true;
            }
        }


        // [-2-] check queue actions
        if (isset($flags['running'])) {

            foreach ($sxShopUloads as $key => $shopUploader) {
                if(!$shopUploader->isRunning()) unset($sxShopUloads[$key]);
            }

            // empty queues
            if (isset($flags['running'])) {
                $sxQueue = new SxQueue();

                // empty update queue
                $sxQueue->set('update');
                $sxQueue->empty();

                // empty delete queue
                $sxQueue->set('delete');
                $sxQueue->empty();
            }

        } else {
            // do incremental uploads

            $sxQueue = new SxQueue();
            $oxArticleIds = $sxQueue->getArticles(10);


            foreach ($sxShopUloads as $key => $shopUploader) {

                if (!$shopUploader->config['sxIncrementalUpdatesActive']) continue;

                if($oxArticleIds && $shopUploader->config['sxIncrementalUpdatesActive']){
                    $shopUploader->addArticleUpdates($oxArticleIds);
                }
                
                $shopUploader->sendUpdate();

            }

            $sxQueue->removeArticle($oxArticleIds);        
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

        $this->sxResponse = ['status' => 'success', 'duration' => (microtime(true) - $startTime) / 1000];
    }

}
