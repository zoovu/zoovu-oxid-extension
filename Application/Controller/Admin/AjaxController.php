<?php

namespace Semknox\Productsearch\Application\Controller\Admin;

use Semknox\Productsearch\Application\Controller\UploadController;
use OxidEsales\Eshop\Core\Registry;



class AjaxController extends \OxidEsales\Eshop\Application\Controller\Admin\AdminController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'admin_sxproductsearch_ajax.tpl';

    private $_sxUpload;
    private $_oxRegistry, $_oxConfig;

    /**
     * Class constructor. 
     */
    public function __construct()
    {
        $this->_oxRegistry = new Registry;
        $this->_sxUpload = new UploadController([]);
        $this->_oxConfig = $this->_oxRegistry->getConfig();
    }

    /**
     * The render function
     * 
     * @return string
     */
    public function render()
    {
        //todo: set header tp application/json

        return $this->_sThisTemplate;
    }

    /**
     * get status
     */
    public function getStatus()
    {
        //http://[url]/index.php?cl=sxproductsearch_ajax&fnc=geStatus
        
        $responseData = array();

        $shopConfigs = $this->_sxUpload->getShopConfigs();

        foreach($shopConfigs as $shop){
            $shopIdentifier = strtolower($shop['shopId'] . '-' . $shop['lang']);
            $responseData[$shopIdentifier]['phase'] = 'PENDING';
        }


        if(count($responseData)){

            $status = $this->_sxUpload->getStatus();

            foreach($status as $shop => $sxProject){

                $shopIdentifier = strtolower($shop);

                if(!isset($responseData[$shopIdentifier])) continue;

                $responseData[$shopIdentifier] = [
                    'phase' => $sxProject->getPhase(),
                    'articlesCollected' => $sxProject->getNumberOfCollected(),
                    'articlesUploaded' => $sxProject->getNumberOfUploaded(),
                    'totalPercentage' => $sxProject->getTotalProgress(),
                    'collectingPercentage' => $sxProject->getCollectingProgress(),
                    'uploadingPercentage' => $sxProject->getUploadingProgress()
                ];

            }
        }


        $this->sxAjaxResponse = ['status' => 'success', 'data' => $responseData];

    }

    /**
     * start upload
     */
    public function startUpload()
    {
        return $this->_controlUpload(__FUNCTION__, false);
    }

    /**
     * stop upload
     */
    public function stopUpload()
    {
        return $this->_controlUpload(__FUNCTION__, true);
    }

    /**
     * upload action
     * 
     * @param mixed $action 
     * @param bool $isRunningCondition 
     * @return void 
     */
    protected function _controlUpload( $action , $isRunningCondition = false)
    {
        $shopId = (int) $this->_oxConfig->getRequestParameter('shopId');
        $shopLang = $this->_oxConfig->getRequestParameter('shopLang');

        if ($shopId && $shopLang) {

            $configValues = [
                'shopId' => $shopId,
                'lang' => ucfirst($shopLang)
            ];

            $this->_sxUpload = new UploadController($configValues);

            $isRunning = $isRunningCondition ? $this->_sxUpload->isRunning() : !$this->_sxUpload->isRunning();

            if ($isRunning) {
                $this->_sxUpload->{$action}();
            }
        }

        $this->sxAjaxResponse = ['status' => 'success'];
    }

}