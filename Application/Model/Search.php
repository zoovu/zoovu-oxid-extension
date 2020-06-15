<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\SxHelper;
use OxidEsales\Eshop\Core\Registry;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;

class Search extends Search_parent
{
    private $_sxCore, $_sxConfigValues, $_sxConfig, $_sxSearch, $_sxSearchResponse, $_sxHelper;

    private $_oxAbbrLanguage;


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
                'subShopId' => $oxShopId,
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
            $sSortBy = is_array($sSortBy) ? $sSortBy : false;
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

        // sort
        if(is_array($sSortBy)){
            $option = $this->_sxHelper->decodeSortOption($sSortBy['sortby'], ['sort' => $sSortBy['sortdir']]);
            $sxSearch->sortBy($option->getKey(), $option->getSort());
        }

        $this->_sxSearchResponse = $sxSearch->search();
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

        // set available sorting options
        $sxAvailableSortingOptions = array();
        foreach($this->_sxSearchResponse->getAvailableSortingOptions() as $option){
            $sxAvailableSortingOptions[$option->getKey()] = $this->_sxHelper->encodeSortOption($option);
        }

        /// todo: remove... just for testing!!!!!!!!!!!!!!!!!!!!!
        if(empty($sxAvailableSortingOptions)){
            $sxAvailableSortingOptions = [11 => 'sxoption_11_FAKE-Test', 22 => 'sxoption_22_FAKE-Name'];
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
