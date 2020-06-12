<?php

namespace Semknox\Productsearch\Application\Model;

use Semknox\Productsearch\Application\Model\SxHelper;

class ArticleList extends \OxidEsales\Eshop\Application\Model\ArticleList
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
    public function loadAllArticles($pageSize = 50, $page = 1)
    {
        $offset = ($page < 1) ? 0 : (($page - 1) * $pageSize);

        $sSelect = "SELECT * FROM oxarticles ORDER BY oxartnum LIMIT $pageSize";

        if($offset) $sSelect .= " OFFSET $offset";

        $this->selectString( $sSelect );
    }


    /**
     * get quantity of all articles
     *
     */
    public function getAllArticlesCount()
    {
        $sSelect = "SELECT * FROM oxarticles";

        $this->selectString($sSelect);

        return $this->count();
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

        if($sxHelper->isEncodedOption(ltrim($sqlSorting, '`'))){
            $sqlSorting = false;
        }

        return parent::setCustomSorting($sqlSorting);

    }

}
