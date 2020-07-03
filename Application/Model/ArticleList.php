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
