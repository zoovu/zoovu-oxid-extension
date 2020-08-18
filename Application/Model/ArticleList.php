<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxHelper;

class ArticleList extends ArticleList_parent
{

    protected $_sxArticleListInterpretation;
    protected $_sxAvailableSortingOptions;

    public $isSxArticleList = false;

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
        $sxHelper = new SxHelper();

        $trimedsqlSorting = ltrim($sqlSorting, '`');

        if(!$sqlSorting || !$trimedsqlSorting || $sxHelper->isEncodedOption($trimedsqlSorting)){
            $sqlSorting = false;
        }

        return parent::setCustomSorting($sqlSorting);

    }

}
