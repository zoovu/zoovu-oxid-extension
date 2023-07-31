<?php

namespace Semknox\Productsearch\Application\Model;

use Exception;
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

        if (isset($this->_sxConfigValues['categoryQuery']) && $this->_sxConfigValues['categoryQuery']) {
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

    // if article is parent and has children, do not add
    public function loadAllArticlesWithoutParentsThatHaveChildren($pageSize = 50, $page = 1, $shopId = null, $justCount = false)
    {

        // COUNT or SELECT DATA
        $sSelect = $justCount ? "SELECT COUNT(*) " : "SELECT * ";

        // FROM 
        $sSelect .= "FROM oxarticles as a ";

        // WHERE
        $sSelect .= "WHERE a.oxhidden = 0";
        if (!isset($this->_sxConfigValues['sendInactiveArticles']) || !$this->_sxConfigValues['sendInactiveArticles']) {
            $sSelect .= " AND a.oxactive = 1";
        }

        if (isset($this->_sxConfigValues['ignoreOutOfStockArticles']) && $this->_sxConfigValues['ignoreOutOfStockArticles']) {
            $sSelect .= " AND a.oxstock > 0";
        }

        if ($shopId) {
            $sSelect .= " AND a.oxshopid = '$shopId'";
        }

        $sSelect .= $this->_excludeParentsWithChildrenSubSelect($shopId);

        if($justCount) return \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne($sSelect);

        $sSelect .= " ORDER BY a.oxartnum LIMIT $pageSize";
        $sSelect .= " OFFSET " . (($page < 1) ? 0 : (($page - 1) * $pageSize));

        $this->selectString($sSelect);
    }

    /*
    private function _excludeParentsWithChildrenSubSelect($shopId){

        $sSelect = " AND (SELECT 1 FROM oxarticles as suba WHERE suba.oxhidden = 0 AND a.oxid = suba.oxparentid";
        //$sSelect .= " AND (SELECT COUNT(*) FROM oxarticles as suba WHERE suba.oxhidden = 0 AND a.oxid = suba.oxparentid";

        if (!isset($this->_sxConfigValues['sendInactiveArticles']) || !$this->_sxConfigValues['sendInactiveArticles']) {
            $sSelect .= " AND suba.oxactive = 1";
        }

        if (isset($this->_sxConfigValues['ignoreOutOfStockArticles']) && $this->_sxConfigValues['ignoreOutOfStockArticles']) {
            $sSelect .= " AND suba.oxstock > 0";
        }

        if ($shopId) {
            $sSelect .= " AND suba.oxshopid = '$shopId'";
        }

        $sSelect .=  " LIMIT 1) IS NULL";

        return $sSelect;
    }
    */

    private function _excludeParentsWithChildrenSubSelect($shopId)
    {
        // BETTER PERFORMANCE //

        // get alle Parents with children
        $parentsSelect = "SELECT DISTINCT oxparentid FROM oxarticles WHERE oxparentid!='' AND oxhidden = 0";

        if (!isset($this->_sxConfigValues['sendInactiveArticles']) || !$this->_sxConfigValues['sendInactiveArticles']) {
            $parentsSelect .= " AND oxactive = 1";
        }

        if (isset($this->_sxConfigValues['ignoreOutOfStockArticles']) && $this->_sxConfigValues['ignoreOutOfStockArticles']) {
            $parentsSelect .= " AND oxstock > 0";
        }

        if ($shopId) {
            $parentsSelect .= " AND oxshopid = '$shopId'";
        }

        $parentsWithChildren = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getCol($parentsSelect);

        if(count($parentsWithChildren)){
            $select = " AND a.oxid NOT IN ('" . implode("','", $parentsWithChildren) . "')";
        } else {
            $select = "";
        }

        return $select;
    }

    /**
     * Load all Article in pages articles
     *
     * @param int $pageSize number of articles to get per page
     * @param int $page nr page to get
     */
    public function loadAllArticles($pageSize = 50, $page = 1, $shopId = null)
    {
        $this->loadAllArticlesWithoutParentsThatHaveChildren($pageSize, $page, $shopId);
    }

    /**
     * get quantity of all articles
     *
     */
    public function getAllArticlesCount($shopId = null)
    {
        return $this->loadAllArticlesWithoutParentsThatHaveChildren(0, 0, $shopId, true);
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
        $trimedsqlSorting = is_string($sqlSorting) ? ltrim($sqlSorting, '`') : false;

        if(!$sqlSorting || !$trimedsqlSorting || $this->_sxHelper->isEncodedOption($trimedsqlSorting)){
            $sqlSorting = false;
        }

        return parent::setCustomSorting($sqlSorting);

    }


    protected function _getCategorySelect($sFields, $sCatId, $aSessionFilter) // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {

        // fallback if disabled
        if (!isset($this->_sxConfigValues['categoryQuery']) || !$this->_sxConfigValues['categoryQuery']) {
            return parent::_getCategorySelect($sFields, $sCatId, $aSessionFilter);
        }

        // get category path
        $categoryPath = $this->_sxHelper->getCategoryPath($sCatId);

        /*
         * [1.] prepare Request
         */

        // create search AND set category array
        $sxSearch = $this->_sxSearch->queryCategory($categoryPath);
        
        // set sort/order
        $request = Registry::get(\OxidEsales\Eshop\Core\Request::class);
        $sortBy = $request->getRequestParameter('listorderby');
        if($sortBy && $this->_sxHelper->isEncodedOption($sortBy)){
            $sortOption = $this->_sxHelper->decodeSortOption($sortBy);
            $sxSearch->sortBy($sortOption->getKey(),$sortOption->getSort());
        }

        // sets current page
        $this->iActPage = $this->_sxHelper->getPageNr();
        $sxSearch->setPage($this->iActPage);

        // set Page Limit
        $iNrofCatArticles = $this->_sxHelper->getPageLimit();
        $sxSearch->setLimit($iNrofCatArticles);
        //$sxSearch->setLimit(1000); // todo: improve

        // set filters
        $filters = $this->_sxHelper->getRequestFilter();
        foreach ($filters as $filterId => $options) {
            $sxSearch->addFilter($filterId, $options);
        }

        // set userGroup
        if (isset($this->_sxConfigValues['userGroup'])) {
            $sxSearch->setUserGroup($this->_sxConfigValues['userGroup']);
        }

        /*
         * [2.] execute Request
         */

        try {
            $this->_sxSearchResponse = $sxSearch->search();
        } catch (Exception $e) {
            // fallback
            $this->_sxConfigValues = null;
            $this->_sxHelper->log($e->getMessage() . ' | ' . __CLASS__ . '::' . __FUNCTION__, 'error');
            return parent::_getCategorySelect($sFields, $sCatId, $aSessionFilter);
        }


        /*
         * [3.] evaluate Response
         */

        // set IsSemknox ArticleList
        if($this->_sxTotalResults = $this->_sxSearchResponse->getTotalResults()){
            $this->isSxArticleList = true;
        } else {
            Registry::getSession()->setVariable('attrfilter', []);
            return parent::_getCategorySelect($sFields, $sCatId, $aSessionFilter);
        }


        // add available sorting options to articleList
        $sxAvailableSortingOptions = $this->_sxSearchResponse->getAvailableSortingOptions();
        $this->_sxHelper->addSortingToArticleList($this, $sxAvailableSortingOptions);
        

        // add Filter to articleList
        $sxAvailableFiltersFromResponse = $this->_sxSearchResponse->getAvailableFilters();
        $articleListFilter = $this->_sxHelper->addFilterToArticleList($sxAvailableFiltersFromResponse, $this->_sxConfigValues);
        $this->setAvailableFilters($articleListFilter['availableFilters']);
        $this->setAvailableRangeFilters($articleListFilter['availableRangeFilters']);
        $this->setAttributeOptions($articleListFilter['attributeOptions']);

        // add articles
        $oxArticleIds = array();
        foreach ($this->_sxSearchResponse->getProducts() as $sxArticle) {
            if ($this->_sxConfigValues['resultProduct'] == 'individualVariantProduct') {
                $oxArticleIds[] = $sxArticle->getId();
            } else {
                $oxArticleIds[] = $sxArticle->getGroupId(); // show parent
            }
        }
        $sArticleTable = getViewName('oxarticles');
        $oxArticleIds = \array_unique($oxArticleIds);

        $oxIdsSql = implode(',', \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quoteArray($oxArticleIds));

        $sSelect = "SELECT $sFields, $sArticleTable.oxtimestamp from $sArticleTable ";
        $sSelect .= "WHERE $sArticleTable.oxid IN ( " .  $oxIdsSql . " ) AND ";
        $sSelect .= $this->getBaseObject()->getSqlActiveSnippet();
        $sSelect .= " ORDER BY FIELD(`oxid`, $oxIdsSql)";

        return $sSelect;
    }


    public function loadCategoryArticles($sCatId, $aSessionFilter, $iLimit = null)
    {
        if (!isset($this->_sxConfigValues['categoryQuery']) || !$this->_sxConfigValues['categoryQuery']) {
            return parent::loadCategoryArticles($sCatId, $aSessionFilter, $iLimit);
        }

        $sArticleFields = $this->getBaseObject()->getSelectFields();
        $sSelect = $this->_getCategorySelect($sArticleFields, $sCatId, $aSessionFilter);


        if ($iLimit = (int) $iLimit) {
            $sSelect .= " LIMIT $iLimit";
        }

        unset($this->_aSqlLimit[0]);
        unset($this->_aSqlLimit[1]);
        $this->selectString($sSelect);

        // this select is FAST so no need to hazzle here with getNrOfArticles()
        return $this->_sxTotalResults;
    }

}
