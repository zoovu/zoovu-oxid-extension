<?php

namespace Semknox\Productsearch\Application\Model;

class ArticleList extends \OxidEsales\Eshop\Application\Model\ArticleList
{

    protected $_sxArticleListInterpretation;

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
     * set article list interpretation text
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

}
