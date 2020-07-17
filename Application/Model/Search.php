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

        $sxFrontendActive = $this->_sxHelper->get('sxFrontendActive' . $this->_oxAbbrLanguage);

        if($sxFrontendActive){

            $sxIsSandbox = $this->_sxHelper->get('sxIsSandbox' . $this->_oxAbbrLanguage);
            $sxApiUrl = $sxIsSandbox ? $this->_sxHelper->get('sxSandboxApiUrl') : $this->_sxHelper->get('sxApiUrl');

            $sxProjectId = $this->_sxHelper->get('sxProjectId' . $this->_oxAbbrLanguage);
            $sxApiKey = $this->_sxHelper->get('sxApiKey' . $this->_oxAbbrLanguage);

            $oxShopId = $oxRegistry->getConfig()->getShopId();

            $sxRequestTimeout = (int) $this->_sxHelper->get('sxRequestTimeout');

            $sxConfigValues = [
                // required options
                'projectId' => $sxProjectId,
                'apiKey' => $sxApiKey,
                'apiUrl' => $sxApiUrl,
                'requestTimeout' => $sxRequestTimeout,
                'shopId' => $oxShopId,
                'lang' => $this->_oxAbbrLanguage,
            ];

            $sxConfigValues = $this->_sxHelper->getMasterConfig($sxConfigValues);

            // since its possible to set login data by masterConfig, this check has to be the last one
            if($sxConfigValues['projectId'] && $sxConfigValues['apiKey']){
                $this->_sxConfigValues = $sxConfigValues;
            }

        }

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

            if(!is_array($options)){
                $options = [ (string) $options ];
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

            $logger = $this->_oxRegistry->getLogger();
            $logger->error($e->getMessage(), [__CLASS__, __FUNCTION__]);

            return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $sSortBy);
        }


        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        $oArtList->loadIds($oxArticleIds);

        // set search interpretation text
        $sxAnswerActive = $this->getConfig()->getConfigParam('sxAnswerActive' . $this->_oxAbbrLanguage);
        if($sxAnswerActive){
            // set answer
            $sxAnswerText = (string) $this->_sxSearchResponse->getAnswerText();
            $oArtList->setArticleListInterpretation($sxAnswerText);
        }

        // set available filter
        $sxAvailableFilters = new AttributeList();
        foreach ($this->_sxSearchResponse->getAvailableFilters() as $filter) {

            // oxid does not support range filter
            if ($filter instanceof RangeFilter) {
                continue;
            } 

            $attribute = new Attribute();
            $attribute->setTitle($filter->getName());
            $attribute->setId($filter->getName()); // since api changed

            foreach($filter->getOptions() as $option) {
                $attribute->addValue($option->getName());

                if($option->isActive()){
                    $attribute->setActiveValue($option->getName());
                }

            }

            if($filter->getOptions()){
                $sxAvailableFilters->add($attribute); 
            }

        }
        $oArtList->setAvailableFilters($sxAvailableFilters);


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
