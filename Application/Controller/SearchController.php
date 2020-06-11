<?php

namespace Semknox\Productsearch\Application\Controller;

use OxidEsales\Eshop\Core\Registry;

class SearchController extends SearchController_parent
{
    /**
     * get search page headline
     * 
     * @return string 
     */
    public function getSearchHeader()
    {
        // fallback, if not active for current shop
        if (!$this->_aArticleList->_isSxArticleList) return '';

        return $this->_aArticleList->getArticleListInterpretation();       
    }

    /**
     * Returns default category sorting for selected category
     *
     * @return array
     */
    public function getUserSelectedSorting()
    {
        $request = Registry::get(\OxidEsales\Eshop\Core\Request::class);
        $sortBy = $request->getRequestParameter($this->getSortOrderByParameterName());
        $sortOrder = $request->getRequestParameter($this->getSortOrderParameterName());

        return ['sortby' => $sortBy, 'sortdir' => $sortOrder];
    }


    /**
     * Template variable getter. Returns sorting columns
     *
     * @return array
     */
    public function getSortColumns()
    {
        // fallback, if not active for current shop
        if (!$this->_aArticleList->_isSxArticleList) return parent::getSortColumns();

        return $this->_aArticleList->getAvailableSortingOptions();
    }

    /**
     * disable oxid sorting dropdown
     * @return mixed 
     */
    /*
    public function showSorting()
    {
        if (!$this->_aArticleList->_isSxArticleList) return parent::showSorting();

        return true;//false;
    }
    */

    /**
     * enable semknox sorting dropdown (if oxid sorting is active)
     * @return bool 
     */
    /*
    public function showSxSorting()
    {
        return parent::showSorting();
    }
    */
}
