<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Core\Services\Search\Sorting\SortingOption;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\AttributeList;
use OxidEsales\Eshop\Application\Model\Attribute;

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
        $this->_logger = $this->_oxRegistry->getLogger();


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



    public function getCategoryPath($oxCategoryId)
    {

        $oxCategory = new Category;
        $oxCategory->load($oxCategoryId);

        $categoryPath = [];
        if (!$oxCategory) return $categoryPath;

        while ($oxCategory) {

            if ((string) $oxCategory->oxcategories__oxactive == '1' && (string) $oxCategory->oxcategories__oxhidden == '0') {
                $categoryTitle = strlen($oxCategory->getTitle()) ? $oxCategory->getTitle() : $oxCategory->getId();
                $categoryPath[] = html_entity_decode($categoryTitle);
            }

            $oxCategory = $oxCategory->getParentCategory();
        }

        if (!count($categoryPath)) $categoryPath;

        return array_reverse($categoryPath);

    }


    public function getPageNr()
    {
        $pageNr = (int) \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('pgNr');
        $pageNr = ($pageNr < 0) ? 0 : $pageNr;
        
        return ++$pageNr;
    }

    public function getPageLimit()
    {
        $limit = (int) \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('iNrofCatArticles');
        
        return $limit ? $limit : 10;
    }

    public function getRequestFilter()
    {
        $actControl = Registry::getConfig()->getRequestParameter('actcontrol', false);
        $stoken = Registry::getConfig()->getRequestParameter('stoken', false);
        if (in_array($actControl,['search','alist']) || $stoken) { // workaround to find out if filter have been changed
            $filter = Registry::getConfig()->getRequestParameter('attrfilter', []);
            Registry::getSession()->setVariable('attrfilter', $filter);
        }

        $filter = Registry::getSession()->getVariable('attrfilter') ;
        $filter = $filter && is_array($filter) ? $filter : [];

        $filterReturn = [];
        foreach ($filter as $filterId => $options) {

            if (!$options) continue;

            if (!is_array($options) && stripos($options, '___') === FALSE && stripos($options, '###') === FALSE) {
                $options = [(string) $options];
            } elseif (stripos($options, '___') > 0) {
                // range filter
                $options = explode('___', (string) $options);
                $options = [$options[0], $options[1]];
            } elseif (stripos($options, '###') !== FALSE) {
                // range filter
                $options = explode('###', (string) $options);
                $options = array_filter($options);
                $options = array_values($options);
            }

            foreach ($options as $key => $option) {
                $options[$key] = html_entity_decode($option);
            }

            $filterReturn[$filterId] = $options;
        }
        
        return $filterReturn;

    }

    public function addSortingToArticleList($oArtList, $sxAvailableSortingsFromResponse = [])
    {
        $sortings = [];
        foreach ($sxAvailableSortingsFromResponse as $option) {
            $sortings[$option->getKey()] = $this->encodeSortOption($option);
        }

        if (count($sortings)) $oArtList->setAvailableSortingOptions($sortings);

        return $oArtList;
    }

    public function addFilterToArticleList($sxAvailableFiltersFromResponse, $sxConfigValues = [])
    {
        $sxAvailableFilters = new AttributeList();
        $sxAvailableRangeFilters = new AttributeList();
        $sxAttributeOptions = array();

        foreach ($sxAvailableFiltersFromResponse as $filter) {

            $attribute = new Attribute();

            $filterName = $filter->getName();

            $attribute->setTitle($filterName);
            $attribute->setId($filterName); // since api changed

            if ($filter->getType() == 'RANGE') {

                $minValue = $filter->getMin();
                $maxValue = $filter->getMax();

                if ($minValue == $maxValue) continue;

                // find out what steps to take
                $suffix = 'integer';
                foreach ($filter->getOptions() as $option) {
                    $value = $option->getName();

                    if ((int) $value != $value) {
                        $suffix = 'float';
                        break;
                    }
                }

                // add one value to initialize slider
                $attribute->addValue($minValue . '___' . $maxValue . '___' . $suffix);
                $attribute->setActiveValue($filter->getActiveMin() . '___' . $filter->getActiveMax());


                $attribute->sxTitle = $filterName;
                if (!$sxConfigValues['hideRangeInRangeSliderTitle']) {
                    $attribute->sxTitle .= " <span class='sxTitleRange'>(" . $filter->getActiveMin() . ' ' . $filter->getUnit() . " - " . $filter->getActiveMax() . ' ' . $filter->getUnit() . ")</span>";
                }
                $attribute->setTitle($filterName);
                $attribute->unit = $filter->getUnit();

                $sxAvailableRangeFilters->add($attribute);

                // add fake attribute for reset function
                $attributeFake = new Attribute();
                $attributeFake->setTitle($filterName);
                $attributeFake->setId($filterName); // since api changed
                if ($filter->getActiveMin() != $minValue || $filter->getActiveMax() != $maxValue) {
                    $attributeFake->addValue($minValue . '___' . $maxValue . '___' . $suffix);
                    $attributeFake->setActiveValue($filter->getActiveMin() . '___' . $filter->getActiveMax());
                }
                $sxAvailableFilters->add($attributeFake);

            } else {

                $activeValues = [];
                $options = $filter->getOptions();

                // get active values
                foreach ($options as $option) {
                    if ($option->isActive()) {
                        $activeValues[] = $option->getValue();
                    }
                }


                if ($filter->getType() == 'TREE'){
                    $options = $this->iterateThroughCategoryOptions($options);

                    // find active options
                    $notFoldedParents = [];
                    $activeOptions = [];
                    foreach ($options as $option) {
                        if ($option->isActive()) {
                            $notFoldedParents[] = $option->parentId;
                            $activeOptions = array_merge($activeOptions, $option->parentIds);
                            $activeValues[] = $option->getValue();
                        }
                    }

                    // mark active paths
                    foreach ($options as $key => $option) {
                        if (in_array($option->getId(), $activeOptions) || in_array($option->parentId, $activeOptions)) {
                            $options[$key]->isHidden = false;
                            $notFoldedParents[] = $option->parentId;
                        }
                    }

                    // marke not folded parents
                    foreach ($options as $key => $option) {
                        if (in_array($option->getId(), $notFoldedParents)) {
                            $options[$key]->isFolded = false;
                            $options[$key]->setActive(true);
                        }
                    }
                }

                $activeValues = \array_unique($activeValues);

                foreach ($options as $option) {

                    if (!strlen($option->getName())) continue;

                    $optionName = $option->getName();

                    $sxAttributeOption = [];
                    if ($sxConfigValues['filterOptionCounterActive']) {
                        $sxAttributeOption['count'] = $option->getNumberOfResults();
                    } 

                    if($filter->getType() == 'TREE'){

                        $optionName = $option->getId().$optionName;

                        $sxAttributeOption['css'] = isset($option->css) ? $option->css : '';
                        $sxAttributeOption['isParent'] = isset($option->isParent) ? $option->isParent : false;
                        $sxAttributeOption['id'] = $option->getId();
                        $sxAttributeOption['parentId'] = $option->parentId;
                        $sxAttributeOption['parentIds'] = $option->parentIds;
                        $sxAttributeOption['isTreeNode'] = isset($option->isTreeNode) ? $option->isTreeNode : false;
                        $sxAttributeOption['isHidden'] = isset($option->isHidden) ? $option->isHidden : false;

                        $sxAttributeOption['isFolded'] = isset($option->isFolded) ? $option->isFolded : true;
                        $sxAttributeOption['isFolded'] = $option->isActive() ? false : $sxAttributeOption['isFolded'];
                    }

                    if ($option->isActive()) {
                        $sxAttributeOption['active'] = true;
                        $sxAttributeOption['value'] = implode('###', array_diff($activeValues, [$option->getValue()]));
                    } else {
                        $sxAttributeOption['active'] = false;
                        $sxAttributeOption['value'] = implode('###',array_merge([$option->getValue()], $activeValues));
                    }

                    $attribute->addValue($optionName);
                    $sxAttributeOptions['attrfilter[' . $filterName. ']'][$optionName] = $sxAttributeOption;
                }

                $attribute->setActiveValue(implode('###', $activeValues));

                $sxAvailableFilters->add($attribute);
            }
        }

        return [
            'availableFilters' => $sxAvailableFilters,
            'availableRangeFilters' => $sxAvailableRangeFilters,
            'attributeOptions' => $sxAttributeOptions
        ];
    }

    private function iterateThroughCategoryOptions($options, $level=0, $parent=null)
    {
        $returnOptions = []; // needed for correct order
        $parentActive = !$level ? true : $parent->isActive();
        

        foreach ($options as $key => $option) {
            /* @var $option \Semknox\Core\Services\Search\Filters\Option */
           
            $option->isTreeNode = true;

            $option->css = "";
            if($level > 0){
                $option->css .= "margin-left: " . ($level * 20) . "px;";
                $option->isHidden = !$option->isActive() && !$parentActive ? true : false;
            }

            if ($option->hasChildren()) {
                $option->isParent = true;
            }

            $option->parentId = $parent ? $parent->getId() : 0;
            $option->parentIds = $parent ? array_merge($parent->parentIds,[$option->parentId]) : [];
            
            $returnOptions[] = $option;

            // iterate through children
            if($option->hasChildren()) {
                $returnOptions = array_merge($returnOptions, $this->iterateThroughCategoryOptions($option->getChildren(), $level+1, $option));
            }

        }

        return $returnOptions;
    }


}