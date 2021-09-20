<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxHelper;

use Semknox\Core\SxCore;
use Semknox\Core\SxConfig;
use OxidEsales\Eshop\Core\Registry;

class ArticleList extends ArticleList_parent
{

    protected $_sxArticleListInterpretation;
    protected $_sxAvailableSortingOptions;

    public $isSxArticleList = false;



    public function __construct()
    {
        parent::__construct();

        $this->_sxHelper = new SxHelper();

        $this->setSxConfigValues();
        if (!$this->_sxConfigValues) return;

        $this->_sxConfig = new SxConfig($this->_sxConfigValues);
        $this->_sxCore = new SxCore($this->_sxConfig);

        $this->_sxSearch = $this->_sxCore->getSearch();

        if (isset($this->_sxConfigValues['categoryQuery']) || $this->_sxConfigValues['categoryQuery']) {
            $this->isSxArticleList = true;
        }

    }


    public function setSxConfigValues()
    {

        $oxRegistry = new Registry();

        $this->_oxAbbrLanguage = ucfirst($oxRegistry->getLang()->getLanguageAbbr());

        $sxConfigValues = $this->_sxHelper->getConfig($this->_oxAbbrLanguage);

        if ($sxConfigValues['frontendActive']) {
            $this->_sxConfigValues = $sxConfigValues;
        }

        return;
    }






    /**
     * Load all Article in pages articles
     *
     * @param int $pageSize number of articles to get per page
     * @param int $page nr page to get
     */
    public function loadAllArticles($pageSize = 50, $page = 1, $shopId = null)
    {
        $offset = ($page < 1) ? 0 : (($page - 1) * $pageSize);

        $sSelect = "SELECT * FROM oxarticles WHERE oxactive = 1 AND oxhidden = 0";

        if ($shopId) {
            $sSelect .= " AND oxshopid = '$shopId'";
        }

        $sSelect .= " ORDER BY oxartnum LIMIT $pageSize";

        if ($offset) $sSelect .= " OFFSET $offset";

        $this->selectString($sSelect);
    }

    /**
     * Load the list by article ids
     *
     * @param array $aIds Article ID array
     *
     * @return null;
     */
    public function loadIdsByGivenOrder($aIds)
    {
        if (!count($aIds)) {
            $this->clear();
            return;
        }

        $oBaseObject = $this->getBaseObject();
        $sArticleTable = $oBaseObject->getViewName();
        $sArticleFields = $oBaseObject->getSelectFields();

        $oxIdsSql = implode(',', \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quoteArray($aIds));

        $sSelect = "select $sArticleFields from $sArticleTable ";
        $sSelect .= "where $sArticleTable.oxid in ( " . $oxIdsSql . " ) and ";
        $sSelect .= $oBaseObject->getSqlActiveSnippet();

        $sSelect .= " order by FIELD(`oxid`,$oxIdsSql)";

        $this->selectString($sSelect);
    }



    /**
     * get quantity of all articles
     *
     */
    public function getAllArticlesCount($shopId = null)
    {
        $sSelect = "SELECT COUNT(*) FROM oxarticles WHERE oxactive = 1 AND oxhidden = 0";

        if($shopId){
            $sSelect .= " AND oxshopid = '$shopId' ";
        }

        return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSelect);
    }

    /**
     * set article list interpretation text
     * 
     * @param string $content 
     * @return void 
     */
    public function setArticleListInterpretation($content = '')
    {
        $this->_sxArticleListInterpretation = (string) $content;
    }


    /**
     * get article list interpretation text
     * 
     * @return mixed 
     */
    public function getArticleListInterpretation()
    {
        if($this->_sxArticleListInterpretation)
        {
            return $this->_sxArticleListInterpretation;
        } 

        return '';
    }


    /**
     * set available filters
     * 
     * @param string $content 
     * @return void 
     */
    public function setAvailableFilters($filters = array())
    {
        $this->_sxAvailableFilters = $filters;
    }

    /**
     * set available range filters
     * 
     * @param string $content 
     * @return void 
     */
    public function setAvailableRangeFilters($filters = array())
    {
        $this->_sxAvailableRangeFilters = $filters;
    }



    /**
     * get available filters
     * 
     * @return array 
     */
    public function getAvailableFilters()
    {
        if ($this->_sxAvailableFilters) {
            return $this->_sxAvailableFilters;
        }

        return array();
    }

    /**
     * get available range filters
     * 
     * @return array 
     */
    public function getAvailableRangeFilters()
    {
        if ($this->_sxAvailableRangeFilters) {
            return $this->_sxAvailableRangeFilters;
        }

        return array();
    }

    /**
     * get attribute options options (js array)
     * @return void 
     */
    public function getAttributeOptions()
    {
        if ($this->_sxAttributeOptions) {
            return json_encode($this->_sxAttributeOptions);
        }

        return '[]';
    }

    /**
     * set attribute options options (js array)
     * @return void 
     */
    public function setAttributeOptions($sxAttributeOptions)
    {

        $this->_sxAttributeOptions = $sxAttributeOptions;
    }



    /**
     * set available sorting options
     * 
     * @param string $content 
     * @return void 
     */
    public function setAvailableSortingOptions($options = array())
    {
        $this->_sxAvailableSortingOptions = $options;
    }


    /**
     * get available sorting options
     * 
     * @return array 
     */
    public function getAvailableSortingOptions()
    {
       
        if ($this->_sxAvailableSortingOptions) {
            return $this->_sxAvailableSortingOptions;
        }

        return array();
    }


    /**
     * fixes customSorting for non Search pages
     * 
     * @param string $sqlSorting 
     * @return void 
     */
    public function setCustomSorting($sqlSorting)
    {
        $trimedsqlSorting = ltrim($sqlSorting, '`');

        if(!$sqlSorting || !$trimedsqlSorting || $this->_sxHelper->isEncodedOption($trimedsqlSorting)){
            $sqlSorting = false;
        }

        return parent::setCustomSorting($sqlSorting);

    }








    protected function _getCategorySelect($sFields, $sCatId, $aSessionFilter) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if (!isset($this->_sxConfigValues['categoryQuery']) || !$this->_sxConfigValues['categoryQuery']) {
            return parent::_getCategorySelect($sFields, $sCatId, $aSessionFilter);
        }

        $categoryPath = $this->_sxHelper->getCategoryPath($sCatId);

        // get articles
        $sxSearch = $this->_sxSearch->queryCategory($categoryPath);

        $sxSearch->setLimit(1000);
        //$sxSearch->setPage($this->_sxHelper->getPageNr());

        $this->_sxSearchResponse = $sxSearch->search();

        // set available sorting options
        $sxAvailableSortingOptions = array();
        foreach ($this->_sxSearchResponse->getAvailableSortingOptions() as $option) {
            $sxAvailableSortingOptions[$option->getKey()] = $this->_sxHelper->encodeSortOption($option);
        }
        $this->setAvailableSortingOptions($sxAvailableSortingOptions);
        
        
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        $sArticleTable = getViewName('oxarticles');

        //$oxArticleIds = \array_reverse($oxArticleIds);

        $oxIdsSql = implode(',', \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quoteArray($oxArticleIds));

        $sSelect = "SELECT $sFields, $sArticleTable.oxtimestamp from $sArticleTable ";
        $sSelect .= "WHERE $sArticleTable.oxid IN ( " .  $oxIdsSql . " ) AND ";
        $sSelect .= $this->getBaseObject()->getSqlActiveSnippet();
        $sSelect .= " ORDER BY FIELD(`oxid`, $oxIdsSql)";


        return $sSelect;
    }


    /*
    public function loadCategoryArticles($sCatId, $aSessionFilter, $iLimit = null)
    {
        return parent::loadCategoryArticles($sCatId, $aSessionFilter, $iLimit);


        if(!isset($this->_sxConfigValues['categoryQuery']) || !$this->_sxConfigValues['categoryQuery']){
            return parent::loadCategoryArticles($sCatId, $aSessionFilter, $iLimit);
        }

        $categoryPath = $this->_sxHelper->getCategoryPath($sCatId);

        // get articles
        $sxSearch = $this->_sxSearch->queryCategory($categoryPath);

        $sxSearch->setLimit($this->_sxHelper->getPageLimit());
        $sxSearch->setPage($this->_sxHelper->getPageNr());

        $this->_sxSearchResponse = $sxSearch->search();
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            $oxArticleIds[] = $sxArticle->getId();
        }

        $this->loadIdsByGivenOrder($oxArticleIds);

        return $this->_sxSearchResponse->getTotalProductResults();

    }
    */

}
