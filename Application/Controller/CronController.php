<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\ShopList;

use Semknox\Productsearch\Application\Controller\UploadController;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\ArticleTransformer;

class CronController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'sxproductsearch_cron.tpl';

    private $_oxRegistry, $_oxConfig, $_oxLang;
    private $_currentMinute, $_currentHour;


    /**
     * Constructor
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


    protected function _cronRunner()
    {
        $sxUpload = new UploadController([]);
        $sxShopConfigs = $sxUpload->getShopConfigs();

        // check if Uploads needs to be startet
        foreach ($sxShopConfigs as $key => $shopConfig) {

            if ($this->_currentHour == $shopConfig['cronjobHour'] && $this->_currentMinute == $shopConfig['cronjobMinute']) {
                $sxUpload = new UploadController($shopConfig);
                $sxUpload->startUpload();

                unset($sxShopConfigs[$key]); // not directly continue;
            }

        }

        // check if Uploads needs to be continued (always just one job per cronrun!)
        foreach ($sxShopConfigs as $shopConfig) {

            $sxUpload = new UploadController($shopConfig);
            
            if($sxUpload->isRunning()){
                $sxUpload->continueUpload();
                break; // (always just one job per cronrun!)
            }

        }

        $this->sxResponse = ['status' => 'success'];

    }

}
