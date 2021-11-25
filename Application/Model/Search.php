<?php

namespace Semknox\Productsearch\Application\Model;

use Exception;
use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\SxHelper;

use OxidEsales\Eshop\Core\Registry;
use Semknox\Core\SxCore;
use Semknox\Core\SxConfig;

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
        $this->_oxRegistry = new Registry();
        $this->_logger = $this->_oxRegistry->getLogger();

        $this->setSxConfigValues();
        if(!$this->_sxConfigValues) return;

        // todo: add seperat config value for frontend output
        //$this->_sxConfigValues['requestTimeout'] = '2'; // for search request should not be longer

        $this->_sxConfig = new SxConfig($this->_sxConfigValues);
        $this->_sxCore = new SxCore($this->_sxConfig);

        $this->_sxSearch = $this->_sxCore->getSearch();
    }

    /**
     * set Config values
     * 
     * @return void 
     */
    public function setSxConfigValues()
    {
        $this->_oxAbbrLanguage = ucfirst($this->_oxRegistry->getLang()->getLanguageAbbr());

        $sxConfigValues = $this->_sxHelper->getConfig($this->_oxAbbrLanguage);

        if($sxConfigValues['frontendActive']){
            $this->_sxConfigValues = $sxConfigValues;
        }

        return;
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
        if (!$this->_sxConfigValues){
            $sSortBy = !is_array($sSortBy) ? (string) $sSortBy : false;
            return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $sSortBy);
        }

        /*
         * [1.] prepare Request
         */

        // create search AND set query
        $sxSearch = $this->_sxSearch->query($sSearchParamForQuery);

        // sets current page
        $this->iActPage = $this->_sxHelper->getPageNr();
        $sxSearch->setPage($this->iActPage);

        // set Page Limit 
        $iNrofCatArticles = $this->_sxHelper->getPageLimit();
        $sxSearch->setLimit($iNrofCatArticles);

        // set filters
        $filters = $this->_sxHelper->getRequestFilter();

        foreach($filters as $filterId => $options){
            $sxSearch->addFilter($filterId, $options);
        }

        // set sort
        if(is_array($sSortBy)){
            $option = $this->_sxHelper->decodeSortOption($sSortBy['sortby'] /*, ['sort' => $sSortBy['sortdir']]*/);
            $sxSearch->sortBy($option->getKey(), $option->getSort());
        }

        // set userGroup
        if(isset($this->_sxConfigValues['userGroup'])){
            $sxSearch->setUserGroup($this->_sxConfigValues['userGroup']);
        }


        /*
         * [2.] execute Request
         */

        try{
            $this->_sxSearchResponse = $sxSearch->search();
        } catch(Exception $e){
            // fallback
            $this->_sxConfigValues = null;
            $this->_sxHelper->log($e->getMessage(). ' | ' . __CLASS__ . '::' . __FUNCTION__, 'error');
            return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $sSortBy);
        }

        /*
         * [3.] evaluate Response
         */


        // create response List
        $oArtList = new ArticleList;

        // set IsSemknox ArticleList
        $oArtList->isSxArticleList = true;

        // add articles
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        try {
            $sxAvailableFiltersFromResponse = $this->_sxSearchResponse->getAvailableFilters();
        } catch (Exception $e) {
            $sxAvailableFiltersFromResponse = array();
            $this->_sxHelper->log($e->getMessage(). ' | ' . __CLASS__ . '::' . __FUNCTION__, 'error');

        }
        $oArtList->loadIdsByGivenOrder($oxArticleIds);

        // add Filter to articleList
        $sxAvailableFiltersFromResponse = $this->_sxSearchResponse->getAvailableFilters();
        $articleListFilter = $this->_sxHelper->addFilterToArticleList($sxAvailableFiltersFromResponse, $this->_sxConfigValues);
        $oArtList->setAvailableFilters($articleListFilter['availableFilters']);
        $oArtList->setAvailableRangeFilters($articleListFilter['availableRangeFilters']);
        $oArtList->setAttributeOptions($articleListFilter['attributeOptions']);


        // add search interpretation text
        if ($this->_sxConfigValues['answerActive']) {
            $oArtList->setArticleListInterpretation((string) $this->_sxSearchResponse->getAnswerText());
        }

        // add available sorting options to articleList
        $sxAvailableSortingOptions = $this->_sxSearchResponse->getAvailableSortingOptions();
        $oArtList = $this->_sxHelper->addSortingToArticleList($oArtList, $sxAvailableSortingOptions);

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
