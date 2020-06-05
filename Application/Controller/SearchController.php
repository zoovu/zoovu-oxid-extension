<?php

namespace Semknox\Productsearch\Application\Controller;

class SearchController extends SearchController_parent
{
    /**
     * get search page headline
     * 
     * @return string 
     */
    public function getSearchHeader()
    {
        if(method_exists($this->_aArticleList, 'getArticleListInterpretation')){
            return $this->_aArticleList->getArticleListInterpretation();
        }

        return '';        
    }
}
