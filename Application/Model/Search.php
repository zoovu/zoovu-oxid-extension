<?php

namespace Semknox\Productsearch\Application\Model;

use Exception;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\SxHelper;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\AttributeList;
use OxidEsales\Eshop\Application\Model\Attribute;
use Semknox\Core\SxCore;
use Semknox\Core\SxConfig;
use Semknox\Core\Services\Search\Filters\RangeFilter;
use Semknox\Core\Services\Search\Filters\CollectionFilter;
use Semknox\Core\Services\Search\Filters\Option;

class Search extends Search_parent
{
    private $_sxCore, $_sxConfigValues, $_sxConfig, $_sxSearch, $_sxSearchResponse, $_sxHelper;

    private $_oxAbbrLanguage, $_oxRegistry;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->_sxHelper = new SxHelper();

        $this->setSxConfigValues();
        if(!$this->_sxConfigValues) return;

        $this->_sxConfigValues['requestTimeout'] = '2'; // for search request should not be longer

        $this->_sxConfig = new SxConfig($this->_sxConfigValues);
        $this->_sxCore = new SxCore($this->_sxConfig);

        $this->_sxSearch = $this->_sxCore->getSearch();

        $this->_oxRegistry = new Registry();
    }

    /**
     * set Config values
     * 
     * @return void 
     */
    public function setSxConfigValues()
    {

        $oxRegistry = new Registry();

        $this->_oxAbbrLanguage = ucfirst($oxRegistry->getLang()->getLanguageAbbr());

        $sxConfigValues = $this->_sxHelper->getConfig($this->_oxAbbrLanguage);

        if($sxConfigValues['frontendActive']){
            $this->_sxConfigValues = $sxConfigValues;
        }

        return;

        echo '<pre>';
        var_dump($this->_sxConfigValues);
        die;

    }

    /**
     * get Config values
     * 
     * @return void 
     */
    public function getSxConfigValues()
    {
        return $this->_sxConfigValues;
    }

    /**
     * Returns a list of articles according to search parameters. Returns matched
     *
     * @param string $sSearchParamForQuery       query parameter
     * @param string $sInitialSearchCat          initial category to seearch in
     * @param string $sInitialSearchVendor       initial vendor to seearch for
     * @param string $sInitialSearchManufacturer initial Manufacturer to seearch for
     * @param string $sSortBy                    sort by
     *
     * @return ArticleList
     */
    public function getSearchArticles($sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false, $sInitialSearchManufacturer = false, $sSortBy = false)
    {
        $oxRegistry = new Registry();
        $logger = $oxRegistry->getLogger();

        if (!$this->_sxConfigValues){
            $sSortBy = !is_array($sSortBy) ? (string) $sSortBy : false;
            return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $sSortBy);
        }

        $oArtList = new ArticleList;

        // is semknox ArticleList
        $oArtList->isSxArticleList = true;


        // sets active page
        $this->iActPage = (int) \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('pgNr');
        $this->iActPage = ($this->iActPage < 0) ? 0 : $this->iActPage;
        $this->iActPage++;

        // load only articles which we show on screen
        //setting default values to avoid possible errors showing article list
        $iNrofCatArticles = $this->getConfig()->getConfigParam('iNrofCatArticles');
        $iNrofCatArticles = $iNrofCatArticles ? $iNrofCatArticles : 10;

        // searching ..
        $sxSearch = $this->_sxSearch->query($sSearchParamForQuery);
        $sxSearch->setLimit($iNrofCatArticles);
        $sxSearch->setPage($this->iActPage);

        // filter
        $filter = Registry::getConfig()->getRequestParameter('attrfilter', []);
        foreach($filter as $filterId => $options){

            if(!$options) continue;

            if(!is_array($options) && stripos($options,'___') === FALSE && stripos($options, '###') === FALSE){
                $options = [ (string) $options ];
            } elseif(stripos($options, '___') > 0) {
                // range filter
                $options = explode('___', (string) $options);
                $options = [$options[0], $options[1]];
            } elseif (stripos($options, '###') !== FALSE) {
                // range filter
                $options = explode('###', (string) $options);
                $options = array_filter($options);
                $options = array_values($options);
            } 
            
            foreach($options as &$option){
                $option = html_entity_decode($option);
            }
            
            $sxSearch->addFilter($filterId, $options);
        }

        // sort
        if(is_array($sSortBy)){
            $option = $this->_sxHelper->decodeSortOption($sSortBy['sortby'], ['sort' => $sSortBy['sortdir']]);
            $sxSearch->sortBy($option->getKey(), $option->getSort());
        }

        // set userGroup
        if(isset($this->_sxConfigValues['userGroup'])){
            $sxSearch->setUserGroup($this->_sxConfigValues['userGroup']);
        }


        // do search...
        try{
            $this->_sxSearchResponse = $sxSearch->search();
        } catch(Exception $e){
            // fallback
            $this->_sxConfigValues = null;

            $logger->error($e->getMessage(), [__CLASS__, __FUNCTION__]);

            return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $sSortBy);
        }

        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        $oArtList->loadIdsByGivenOrder($oxArticleIds);

        // set search interpretation text
        $sxAnswerActive = $this->getConfig()->getConfigParam('sxAnswerActive' . $this->_oxAbbrLanguage);
        if($sxAnswerActive){
            // set answer
            $sxAnswerText = (string) $this->_sxSearchResponse->getAnswerText();
            $oArtList->setArticleListInterpretation($sxAnswerText);
        }

        // set available filter
        $sxAvailableFilters = new AttributeList();
        $sxAvailableRangeFilters = new AttributeList();
        $sxAttributeOptions = array();

        try {
            $sxAvailableFiltersFromResponse = $this->_sxSearchResponse->getAvailableFilters();
        } catch (Exception $e) {
            $sxAvailableFiltersFromResponse = array();
            $logger->error($e->getMessage(), [__CLASS__, __FUNCTION__]);
        }

        foreach ($sxAvailableFiltersFromResponse as $filter) {

            $attribute = new Attribute();

            $filterName = $filter->getName();
            $attribute->setTitle($filterName);
            $attribute->setId($filterName); // since api changed

            if ($filter->getType() == 'RANGE') {

                $minValue = $filter->getMin();
                $maxValue = $filter->getMax();

                if($minValue == $maxValue) continue;

                // find out what steps to take
                $suffix = 'integer';
                foreach ($filter->getOptions() as $option) {
                    $value = $option->getName();

                    if( (int) $value != $value){
                        $suffix = 'float';
                        break;
                    }
                }  

                // add one value to initialize slider
                $attribute->addValue($minValue.'___'. $maxValue.'___'. $suffix);
                $attribute->setActiveValue($filter->getActiveMin() . '___' . $filter->getActiveMax());


                $attribute->sxTitle = $filterName;
                if(!$this->_sxConfig->get('hideRangeInRangeSliderTitle', false)){
                    $attribute->sxTitle .= " <span class='sxTitleRange'>(" . $filter->getActiveMin() . ' ' . $filter->getUnit() . " - " . $filter->getActiveMax() . ' ' . $filter->getUnit() . ")</span>";
                }
                $attribute->setTitle($filterName);
                $attribute->unit = $filter->getUnit();

                $sxAvailableRangeFilters->add($attribute);

                // add fake attribute for reset function
                $attributeFake = new Attribute();
                $attributeFake->setTitle($filterName);
                $attributeFake->setId($filterName); // since api changed
                if($filter->getActiveMin() != $minValue || $filter->getActiveMax() != $maxValue){
                    $attributeFake->addValue($minValue . '___' .$maxValue . '___' . $suffix);
                    $attributeFake->setActiveValue($filter->getActiveMin() . '___' . $filter->getActiveMax());
                }
                $sxAvailableFilters->add($attributeFake);                

            } else {

                $attribute->setTitle($filterName);
                $attribute->setId($filterName); // since api changed

                $activeValues = [];

                foreach ($filter->getOptions() as $option) {

                    if(!strlen($option->getName())) continue;

                    $attribute->addValue($option->getName());

                    $sxAttributeOption = [
                        'value' => $option->getValue(),
                        'active' => false
                    ];

                    if($this->getConfig()->getConfigParam('sxFilterOptionCounterActive' . $this->_oxAbbrLanguage)){
                         $sxAttributeOption['count'] = $option->getNumberOfResults();
                    }

                    if ($option->isActive()) {
                        $activeValues[] = $option->getName();
                         $sxAttributeOption['active'] = true;
                    }

                    $sxAttributeOptions['attrfilter['.$filter->getName().']'][$option->getName()] = $sxAttributeOption;
                }

                $attribute->setActiveValue(implode('###', $activeValues));

                if (!$filter->getOptions()) continue;

                $sxAvailableFilters->add($attribute); 
            }
                
        }
        $oArtList->setAvailableFilters($sxAvailableFilters);
        $oArtList->setAvailableRangeFilters($sxAvailableRangeFilters);
        $oArtList->setAttributeOptions($sxAttributeOptions);

        // set available sorting options
        $sxAvailableSortingOptions = array();
        foreach($this->_sxSearchResponse->getAvailableSortingOptions() as $option){
            $sxAvailableSortingOptions[$option->getKey()] = $this->_sxHelper->encodeSortOption($option);
        }

        $oArtList->setAvailableSortingOptions($sxAvailableSortingOptions);

        return $oArtList;
    }


    /**
     * Returns the amount of articles according to search parameters.
     *
     * @param string $sSearchParamForQuery       query parameter
     * @param string $sInitialSearchCat          initial category to seearch in
     * @param string $sInitialSearchVendor       initial vendor to seearch for
     * @param string $sInitialSearchManufacturer initial Manufacturer to seearch for
     *
     * @return int
     */
    public function getSearchArticleCount($sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false, $sInitialSearchManufacturer = false)
    {
        if (!$this->_sxConfigValues) return parent::getSearchArticleCount($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer);

        $iCnt = 0;

        if($this->_sxSearchResponse){
            $iCnt = $this->_sxSearchResponse->getTotalProductResults();
        } else {
            $this->getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer);
            $iCnt = $this->_sxSearchResponse->getTotalProductsResults();
        }

        return $iCnt;
    }

}
