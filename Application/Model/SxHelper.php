<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Core\Services\Search\Sorting\SortingOption;

use OxidEsales\Eshop\Core\Registry;
use Semknox\Productsearch\Application\Model\SxLogger;

class SxHelper {

    protected $_sxFolder = "export/semknox/";

    protected $_sxUploadBatchSize = 200;
    protected $_sxCollectBatchSize = 100;
    protected $_sxRequestTimeout = 15;

    protected $_sxSandboxApiUrl = "https://stage-oxid-v3.semknox.com/";
    protected $_sxApiUrl = "https://api-oxid-v3.semknox.com/";

    protected $_sxMasterConfig = false;
    protected $_sxMasterConfigPath = "masterConfig%s.json";

    protected $_sxDeleteQueuePath = "delete-queue/";
    protected $_sxUpdateQueuePath = "update-queue/";


    public function __construct()
    {
        $this->_oxRegistry = new Registry();
        $this->_oxConfig = $this->_oxRegistry->getConfig();

        $workingDir = $this->_oxConfig->getLogsDir().'../'. $this->_sxFolder;

        //$this->_sxFolder = $logsDir . $this->_sxFolder;

        $this->_sxMasterConfigPath = $workingDir . $this->_sxMasterConfigPath;

        $this->_sxDeleteQueuePath = $workingDir . $this->_sxDeleteQueuePath;
        $this->_sxUpdateQueuePath = $workingDir . $this->_sxUpdateQueuePath;

        $this->_logger = new SxLogger();

    }

    public function log($message, $logLevel = 'info')
    {
        $this->_logger->log($message, $logLevel);
    }


    /**
     * get all exetnsion config values
     * 
     */
    public function getConfig($langAbbr = null)
    {
        include __DIR__.'/../../metadata.php';

        // [1] get config keys
        $configKeys = ['sxSandboxApiUrl' => 'str', 'sxApiUrl' => 'str'];

        foreach($aModule['settings'] as $field){

            // no lang set
            if(!$langAbbr){
                $configKeys[$field['name']] = $field['type'];
                continue;
            }

            $group = $field['group'];

            // no lang setting
            if(stripos($group, 'SemknoxProductsearchLanguageSettings') === false){
                $configKeys[$field['name']] = $field['type'];
                continue;
            }

            // lang setting
            $groupLang = str_replace('SemknoxProductsearchLanguageSettings','', $group);

            if (strtolower($groupLang) == strtolower($langAbbr)) {
                $configKeys[$field['name']] = $field['type'];
                continue;
            }
        }

        // [2] get values
        $config = array();
        foreach($configKeys as $key => $type){

            $value = $type == 'bool' ? boolval($this->get($key)) : $this->get($key);

            if(substr($key, -2) == $langAbbr){
                $key = substr($key,0, -2);
            }

            if (substr($key, 0,2) == 'sx') {
                $key = substr($key, 2);
            }

            $config[lcfirst($key)] = $value;
        }

        // [3] check for sandbox mode
        if($config['isSandbox']){
            $config['apiUrl'] = $config['sandboxApiUrl'];
        }

        // [4] add additional data
        $config['shopId'] = $this->_oxConfig->getShopId();
        $config['lang'] = $langAbbr;

        // [5] add masterConfig values
        $config = $this->getMasterConfig($config, $langAbbr);

        return $config;

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
        $value = trim($this->_oxConfig->getConfigParam($key));
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
    public function getMasterConfig(array $configValues = [], $lang = '')
    {
        $lang = ucfirst(strtolower($lang));
        $masterConfigPath = sprintf($this->_sxMasterConfigPath, $lang);

        // performance
        if(!is_array($this->_sxMasterConfig[$lang])){

            if(file_exists($masterConfigPath) && $masterConfig = file_get_contents($masterConfigPath)){
                $masterConfig = json_decode($masterConfig, true);
                $this->_sxMasterConfig[$lang] = $masterConfig;
            } else {
                $this->_sxMasterConfig[$lang] = [];
            }

        } 

        if($this->_sxMasterConfig[$lang] && is_array($this->_sxMasterConfig[$lang]))
        {

            $masterConfig = $this->_sxMasterConfig[$lang];
            $configValues = array_merge($configValues, $masterConfig);

            if (!isset($configValues['userGroup']) && isset($masterConfig['projectId']) && isset($masterConfig['apiKey']) && isset($configValues['shopId'])) {
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
    static function isEncodedOption($sStringToTranslate = '')
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