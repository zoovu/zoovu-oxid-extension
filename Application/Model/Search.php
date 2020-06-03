<?php

namespace Semknox\Productsearch\Application\Model;

use \OxidEsales\Eshop\Core\Registry;

use Semknox\Productsearch\Application\Model\ArticleList;

use Semknox\Core\SxConfig;
use Semknox\Core\SxCore;

class Search extends Search_parent
{
    private $_sxCore, $_sxConfig, $_sxSearch, $_sxSearchResponse;
    private $_oxRegistry;

    /**
     * Class constructor. Executes search lenguage setter
     */
    public function __construct()
    {
        parent::__construct();

        $this->_oxRegistry = new Registry;

        $configValues = [
            // required options
            'projectId'    => '24',
            'apiKey' => '85owx55emd2gmoh8dtx7y49so44fy745',
            'apiUrl' => 'https://stage-magento-v3.semknox.com',
        ];

        $this->_sxConfig = new SxConfig($configValues);
        $this->_sxCore = new SxCore($this->_sxConfig);

        $this->_sxSearch = $this->_sxCore->getSearch();
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
        // sets active page
        $this->iActPage = (int) \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('pgNr');
        $this->iActPage = ($this->iActPage < 0) ? 0 : $this->iActPage;

        // searching ..
        $this->_sxSearchResponse = $this->_sxSearch->query($sSearchParamForQuery)->search();
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getResults() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->id();
        }

        $oArtList = new ArticleList;
        $oArtList->loadIds($oxArticleIds);

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
