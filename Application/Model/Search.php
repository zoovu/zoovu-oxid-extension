<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\ArticleList;
use Semknox\Productsearch\Application\Model\SxSetting;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;

class Search extends Search_parent
{
    private $_sxCore, $_sxConfigValues, $_sxConfig, $_sxSearch, $_sxSearchResponse;

    private $_oxAbbrLanguage;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

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
        $this->_oxAbbrLanguage = ucfirst(\OxidEsales\Eshop\Core\Registry::getLang()->getLanguageAbbr());

        $sxProjectId = $this->getConfig()->getConfigParam('sxProjectId' . $this->_oxAbbrLanguage);
        $sxApiKey = $this->getConfig()->getConfigParam('sxApiKey' . $this->_oxAbbrLanguage);

        $sxFrontendActive = $this->getConfig()->getConfigParam('sxFrontendActive' . $this->_oxAbbrLanguage);

        if($sxFrontendActive && $sxProjectId && $sxApiKey){

            $sxSetting = new SxSetting;
            $sxIsSandbox = $this->getConfig()->getConfigParam('sxIsSandbox' . $this->_oxAbbrLanguage);
            $sxApiUrl = $sxIsSandbox ? $sxSetting->get('SandboxApiUrl') : $sxSetting->get('ApiUrl');

            $this->_sxConfigValues = [
                // required options
                'projectId' => $sxProjectId,
                'apiKey' => $sxApiKey,
                'apiUrl' => $sxApiUrl,
            ];
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
        if (!$this->_sxConfigValues) return parent::getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer);

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

        $this->_sxSearchResponse = $sxSearch->search();
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getResults() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        $oArtList = new ArticleList;
        $oArtList->loadIds($oxArticleIds);

        $sxAnswerActive = $this->getConfig()->getConfigParam('sxAnswerActive' . $this->_oxAbbrLanguage);
        if($sxAnswerActive){
            // set answer
            $sxAnswerText = (string) $this->_sxSearchResponse->getAnswerText();
            $oArtList->setArticleListInterpretation($sxAnswerText);
        }

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
            $iCnt = $this->_sxSearchResponse->getTotalResults();
        } else {
            $this->getSearchArticles($sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer);
            $iCnt = $this->_sxSearchResponse->getTotalResults();
        }

        return $iCnt;
    }



}
