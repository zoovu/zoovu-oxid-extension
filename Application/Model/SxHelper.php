<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Core\Services\Search\Sorting\SortingOption;

use OxidEsales\Eshop\Core\Registry;

class SxHelper {

    protected $_sxFolder = "export/semknox/";

    protected $_sxUploadBatchSize = 200;
    protected $_sxCollectBatchSize = 100;
    protected $_sxRequestTimeout = 15;

    protected $_sxSandboxApiUrl = "https://stage-oxid-v3.semknox.com/";
    protected $_sxApiUrl = "https://api-v3.semknox.com/";

    protected $_sxMasterConfig = false;
    protected $_sxMasterConfigPath = "masterConfig.json";

    protected $_sxDeleteQueuePath = "delete-queue/";
    protected $_sxUpdateQueuePath = "update-queue/";


    public function __construct()
    {
        $workingDir = Registry::getConfig()->getLogsDir().'../'. $this->_sxFolder;

        //$this->_sxFolder = $logsDir . $this->_sxFolder;

        $this->_sxMasterConfigPath = $workingDir . $this->_sxMasterConfigPath;

        $this->_sxDeleteQueuePath = $workingDir . $this->_sxDeleteQueuePath;
        $this->_sxUpdateQueuePath = $workingDir . $this->_sxUpdateQueuePath;

    }


    /**
     * Get a value
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        // check if availalbe in config
        $oxRegistry = Registry::getConfig();
        $value = $oxRegistry->getConfigParam($key);
        if($value) return $value;

        // check preset values or take default
        $sxKey = '_'. $key;

        return isset($this->$sxKey)
            ? $this->$sxKey
            : $default;
    }

    /**
     * get and merges masterconfig values 
     * 
     * @param array $configValues 
     * @return array 
     */
    public function getMasterConfig(array $configValues = [])
    {
        $masterConfigPath = $this->_sxMasterConfigPath;

        // performance
        if(!is_array($this->_sxMasterConfig)){

            if(file_exists($masterConfigPath) && $masterConfig = file_get_contents($masterConfigPath)){
                $masterConfig = json_decode($masterConfig, true);
                $this->_sxMasterConfig = $masterConfig;
            } else {
                $this->_sxMasterConfig = [];
            }

        } 

        if($this->_sxMasterConfig && is_array($this->_sxMasterConfig))
        {

            $masterConfig = $this->_sxMasterConfig;
            $configValues = array_merge($configValues, $masterConfig);

            if (isset($masterConfig['projectId']) && isset($masterConfig['apiKey']) && isset($configValues['shopId'])) {
                // for masterConfig routine (merge multiple subshops with same products)
                $configValues['userGroup'] = $configValues['shopId'].'-'.$configValues['lang'];
            }

        }

        return $configValues;
    }

    /**
     * check if string is an encoded semknox option
     * 
     * @param string $sStringToTranslate 
     * @return bool 
     */
    public function isEncodedOption($sStringToTranslate = '')
    {
        return stripos((string) $sStringToTranslate, 'sxoption') === 0;
    }


    /**
     * encode semknox sort option to usable string
     * 
     * @param Option $filter 
     * @return string 
     */
    public function encodeSortOption(SortingOption $option)
    {
        return 'sxoption_'.$option->getKey().'_' .$option->getName();
    }

    
    /**
     * decode string to semknox sort option
     * 
     * @param string $filter 
     * @return SortingOption 
     */
    public function decodeSortOption($optionString = '', $additionalData = array())
    {
        $optionArray = explode('_', $optionString, 3);

        // empty option if decoding impossible
        if(count($optionArray) < 3) return new SortingOption([]);

        $optionData = [
            'key' => $optionArray[1],
            'name' => $optionArray[2]
        ];

        $optionData = array_merge($optionData, $additionalData);

        return new SortingOption($optionData);
    }

}